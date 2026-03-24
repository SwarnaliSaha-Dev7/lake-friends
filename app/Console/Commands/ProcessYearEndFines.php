<?php

namespace App\Console\Commands;

use App\Models\FinancialYear;
use App\Models\Member;
use App\Models\MemberFine;
use App\Models\MemberFinancialSummary;
use App\Models\MembershipPurchaseHistory;
use App\Models\MinimumSpendRule;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessYearEndFines extends Command
{
    protected $signature = 'fines:process-year-end {--club_id= : Specific club ID (optional)} {--fy_label= : FY label e.g. 2025-2026 (optional, defaults to closing FY)}';

    protected $description = 'Process year-end minimum spend shortfall fines and wallet carry-forward/forfeit for Annual and Annual Silver members';

    public function handle()
    {
        $today = Carbon::today();

        // Default: process the FY that just closed (run on April 1)
        if ($this->option('fy_label')) {
            $fyLabel = $this->option('fy_label');
            [$fyStartYear, $fyEndYear] = explode('-', $fyLabel);
            $fyStart = Carbon::create((int)$fyStartYear, 4, 1);
            $fyEnd   = Carbon::create((int)$fyEndYear, 3, 31);
        } else {
            if ($today->month >= 4) {
                $fyStart = Carbon::create($today->year - 1, 4, 1);
                $fyEnd   = Carbon::create($today->year, 3, 31);
                $fyLabel = ($today->year - 1) . '-' . $today->year;
            } else {
                $fyStart = Carbon::create($today->year - 2, 4, 1);
                $fyEnd   = Carbon::create($today->year - 1, 3, 31);
                $fyLabel = ($today->year - 2) . '-' . ($today->year - 1);
            }
        }

        $this->info("Processing FY: {$fyLabel} ({$fyStart->toDateString()} to {$fyEnd->toDateString()})");

        $clubIds = $this->option('club_id')
            ? [(int)$this->option('club_id')]
            : DB::table('clubs')->pluck('id')->toArray();

        foreach ($clubIds as $clubId) {
            $this->processClub($clubId, $fyLabel, $fyStart, $fyEnd);
        }

        $this->info('Year-end fine processing complete.');
    }

    private function processClub(int $clubId, string $fyLabel, Carbon $fyStart, Carbon $fyEnd): void
    {
        $spendRule = MinimumSpendRule::where('club_id', $clubId)->first();
        if (!$spendRule) {
            $this->warn("Club {$clubId}: No minimum spend rule found, skipping.");
            return;
        }

        $annualMin  = (float) $spendRule->minimum_amount;
        $monthlyMin = $annualMin / 12;

        $fy = FinancialYear::firstOrCreate(
            ['club_id' => $clubId, 'fy_label' => $fyLabel],
            ['start_date' => $fyStart->toDateString(), 'end_date' => $fyEnd->toDateString(), 'is_closed' => false]
        );

        if ($fy->is_closed) {
            $this->warn("Club {$clubId}: FY {$fyLabel} already closed, skipping.");
            return;
        }

        // Only process active members whose current plan has is_minimum_spend_applicable = true
        $memberIds = MembershipPurchaseHistory::where('club_id', $clubId)
            ->where('status', 'active')
            ->whereHas('membershipPlanType', fn($q) => $q->where('is_minimum_spend_applicable', true))
            ->pluck('member_id')
            ->unique()
            ->toArray();

        $members = Member::where('club_id', $clubId)
            ->where('status', 'active')
            ->whereIn('id', $memberIds)
            ->get();

        $this->info("Club {$clubId}: Processing {$members->count()} Annual/Annual Silver members for FY {$fyLabel}");

        foreach ($members as $member) {
            $this->processMember($member, $fy, $fyStart, $fyEnd, $monthlyMin);
        }

        $fy->update(['is_closed' => true]);
        $this->info("Club {$clubId}: FY {$fyLabel} marked as closed.");
    }

    private function processMember(Member $member, FinancialYear $fy, Carbon $fyStart, Carbon $fyEnd, float $monthlyMin): void
    {
        DB::beginTransaction();
        try {
            // Pro-rate minimum spend based on join date
            $firstPurchase  = $member->purchaseHistory()->orderBy('start_date')->first();
            $joinDate       = $firstPurchase
                ? Carbon::parse($firstPurchase->start_date)->startOfMonth()
                : Carbon::parse($member->created_at)->startOfMonth();
            $effectiveStart = $joinDate->gt($fyStart) ? $joinDate : $fyStart;
            $months         = (int) $effectiveStart->diffInMonths($fyEnd->copy()->addDay());
            $minimumRequired = round($monthlyMin * $months, 2);

            $summary = MemberFinancialSummary::firstOrCreate(
                ['club_id' => $member->club_id, 'member_id' => $member->id, 'financial_year_id' => $fy->id],
                ['minimum_spend_required' => $minimumRequired, 'total_recharge' => 0, 'total_spend' => 0,
                 'shortfall_amount' => 0, 'forfeited_amount' => 0, 'carry_forward_amount' => 0]
            );

            $totalSpend = (float) $summary->total_spend;
            $shortfall  = max(0, $minimumRequired - $totalSpend);

            $wallet  = Wallet::where('member_id', $member->id)->first();
            $balance = $wallet ? (float) $wallet->current_balance : 0;

            if ($shortfall > 0) {
                // Forfeit only up to shortfall amount (not entire wallet)
                $forfeited = min($balance, $shortfall);

                if ($forfeited > 0 && $wallet) {
                    $wallet->decrement('current_balance', $forfeited);
                    WalletTransaction::create([
                        'wallet_id'  => $wallet->id,
                        'member_id'  => $member->id,
                        'amount'     => $forfeited,
                        'direction'  => 'debit',
                        'txn_type'   => 'forfeit',
                        'created_by' => 1,
                    ]);
                    $summary->update(['forfeited_amount' => $forfeited]);
                }

                // Fine = remaining shortfall after wallet forfeiture
                $fineAmount = $shortfall - $forfeited;

                if ($fineAmount > 0) {
                    $alreadyRecorded = MemberFine::where('club_id', $member->club_id)
                        ->where('member_id', $member->id)
                        ->where('financial_year_id', $fy->id)
                        ->where('fine_type', 'minimum_spend_shortfall')
                        ->exists();

                    if (!$alreadyRecorded) {
                        MemberFine::create([
                            'club_id'            => $member->club_id,
                            'member_id'          => $member->id,
                            'financial_year_id'  => $fy->id,
                            'fine_type'          => 'minimum_spend_shortfall',
                            'fine_amount'        => $fineAmount,
                            'reference_amount'   => $shortfall,
                            'fine_date'          => $fyEnd->toDateString(),
                            'status'             => 'pending',
                            'notes'              => "FY {$fy->fy_label}: shortfall ₹{$shortfall}, forfeited ₹{$forfeited}, fine ₹{$fineAmount}",
                        ]);
                    }
                }

                $summary->update(['shortfall_amount' => $shortfall]);
                $this->line("  Member {$member->id} ({$member->name}): Shortfall ₹{$shortfall}, Forfeited ₹{$forfeited}, Fine ₹{$fineAmount}");
            } else {
                // No shortfall — remaining wallet carries forward (scenario 4)
                $summary->update(['carry_forward_amount' => $balance, 'shortfall_amount' => 0]);
                $this->line("  Member {$member->id} ({$member->name}): No shortfall. Carry forward ₹{$balance}");
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("  Member {$member->id}: Failed — " . $e->getMessage());
        }
    }
}
