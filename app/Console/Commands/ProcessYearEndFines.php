<?php

namespace App\Console\Commands;

use App\Models\FinancialYear;
use App\Models\Member;
use App\Models\MemberFine;
use App\Models\MemberFinancialSummary;
use App\Models\MinimumSpendRule;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessYearEndFines extends Command
{
    protected $signature = 'fines:process-year-end {--club_id= : Specific club ID (optional)} {--fy_label= : FY label e.g. 2025-2026 (optional, defaults to closing FY)}';

    protected $description = 'Process year-end minimum spend shortfall fines and wallet carry-forward/forfeit for all members';

    public function handle()
    {
        $today = Carbon::today();

        // Default: process the FY that just closed (run on April 1)
        // FY closes on March 31, so "closing FY" = previous FY if today is April 1
        if ($this->option('fy_label')) {
            $fyLabel = $this->option('fy_label');
            [$fyStartYear, $fyEndYear] = explode('-', $fyLabel);
            $fyStart = Carbon::create((int)$fyStartYear, 4, 1);
            $fyEnd   = Carbon::create((int)$fyEndYear, 3, 31);
        } else {
            // If today is April 1+, process previous FY (Apr 1 last year – Mar 31 this year)
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

        // Get clubs to process
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

        $annualMin  = (float) $spendRule->minimum_amount; // ₹3600
        $monthlyMin = $annualMin / 12;                    // ₹300

        // Get or create the FinancialYear record
        $fy = FinancialYear::firstOrCreate(
            ['club_id' => $clubId, 'fy_label' => $fyLabel],
            ['start_date' => $fyStart->toDateString(), 'end_date' => $fyEnd->toDateString(), 'is_closed' => false]
        );

        if ($fy->is_closed) {
            $this->warn("Club {$clubId}: FY {$fyLabel} already closed, skipping.");
            return;
        }

        // Get all active members in this club
        $members = Member::where('club_id', $clubId)->where('status', 'active')->get();

        $this->info("Club {$clubId}: Processing {$members->count()} members for FY {$fyLabel}");

        foreach ($members as $member) {
            $this->processMember($member, $fy, $fyStart, $fyEnd, $monthlyMin);
        }

        // Mark FY as closed
        $fy->update(['is_closed' => true]);
        $this->info("Club {$clubId}: FY {$fyLabel} marked as closed.");
    }

    private function processMember(Member $member, FinancialYear $fy, Carbon $fyStart, Carbon $fyEnd, float $monthlyMin): void
    {
        DB::beginTransaction();
        try {
            // Pro-rate minimum spend: from first purchase start_date or FY start, whichever is later
            $firstPurchase  = $member->purchaseHistory()->orderBy('start_date')->first();
            $joinDate       = $firstPurchase
                ? Carbon::parse($firstPurchase->start_date)->startOfMonth()
                : Carbon::parse($member->created_at)->startOfMonth();
            $effectiveStart = $joinDate->gt($fyStart) ? $joinDate : $fyStart;
            $months         = (int) $effectiveStart->diffInMonths($fyEnd->copy()->addDay());
            $minimumRequired = round($monthlyMin * $months, 2);

            // Get or create financial summary
            $summary = MemberFinancialSummary::firstOrCreate(
                ['club_id' => $member->club_id, 'member_id' => $member->id, 'financial_year_id' => $fy->id],
                ['minimum_spend_required' => $minimumRequired, 'total_recharge' => 0, 'total_spend' => 0,
                 'shortfall_amount' => 0, 'forfeited_amount' => 0, 'carry_forward_amount' => 0]
            );

            $totalSpend = (float) $summary->total_spend;
            $shortfall  = max(0, $minimumRequired - $totalSpend);

            // Get current wallet balance
            $wallet  = Wallet::where('member_id', $member->id)->first();
            $balance = $wallet ? (float) $wallet->current_balance : 0;

            if ($shortfall > 0) {
                // Forfeit remaining wallet balance (scenario 2 & 3)
                if ($balance > 0 && $wallet) {
                    $wallet->decrement('current_balance', $balance);
                    WalletTransaction::create([
                        'wallet_id'  => $wallet->id,
                        'member_id'  => $member->id,
                        'amount'     => $balance,
                        'direction'  => 'debit',
                        'txn_type'   => 'forfeit',
                        'created_by' => 1,
                    ]);
                    $summary->update(['forfeited_amount' => $balance]);
                }

                // Create shortfall fine only if not already recorded (any status — paid at renewal counts)
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
                        'fine_amount'        => $shortfall,
                        'reference_amount'   => $shortfall,
                        'fine_date'          => $fyEnd->toDateString(),
                        'status'             => 'pending',
                        'notes'              => "FY {$fy->fy_label} minimum spend shortfall",
                    ]);
                }

                $summary->update(['shortfall_amount' => $shortfall]);
                $this->line("  Member {$member->id} ({$member->name}): Shortfall ₹{$shortfall}, Forfeited ₹{$balance}");
            } else {
                // No shortfall — carry forward remaining balance (scenario 4)
                $surplus = $totalSpend - $minimumRequired;
                $summary->update(['carry_forward_amount' => $balance, 'shortfall_amount' => 0]);
                $this->line("  Member {$member->id} ({$member->name}): No shortfall. Surplus ₹{$surplus}, Carry forward ₹{$balance}");
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("  Member {$member->id}: Failed — " . $e->getMessage());
        }
    }
}
