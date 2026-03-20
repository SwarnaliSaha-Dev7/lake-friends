<?php

namespace App\Observers;

use App\Models\FinancialYear;
use App\Models\Member;
use App\Models\MemberFinancialSummary;
use App\Models\MinimumSpendRule;
use App\Models\WalletTransaction;
use Carbon\Carbon;

class WalletTransactionObserver
{
    public function created(WalletTransaction $txn): void
    {
        // Track debit transactions that count toward minimum spend
        $spendTypes = ['spend', 'add_on_purchase', 'locker_purchase'];
        if (!in_array($txn->txn_type, $spendTypes) || $txn->direction !== 'debit') {
            return;
        }

        $member = Member::find($txn->member_id);
        if (!$member) return;

        $clubId = $member->club_id;
        $today  = Carbon::now();

        // Determine current Financial Year (Apr 1 – Mar 31)
        $fy = $this->getCurrentFinancialYear($clubId, $today);

        // Calculate pro-rated minimum spend for this member in this FY
        $minimumRequired = $this->calcMinimumSpendRequired($clubId, $member, $fy);

        // Find or create summary row for this member + FY
        $summary = MemberFinancialSummary::firstOrCreate(
            [
                'club_id'            => $clubId,
                'member_id'          => $member->id,
                'financial_year_id'  => $fy->id,
            ],
            [
                'minimum_spend_required' => $minimumRequired,
                'total_recharge'         => 0,
                'total_spend'            => 0,
                'shortfall_amount'       => 0,
                'forfeited_amount'       => 0,
                'carry_forward_amount'   => 0,
            ]
        );

        $summary->increment('total_spend', $txn->amount);
    }

    // -------------------------------------------------------------------

    private function getCurrentFinancialYear(int $clubId, Carbon $date): FinancialYear
    {
        // FY: Apr 1 – Mar 31
        if ($date->month >= 4) {
            $fyStart = Carbon::create($date->year, 4, 1);
            $fyEnd   = Carbon::create($date->year + 1, 3, 31);
            $label   = $date->year . '-' . ($date->year + 1);
        } else {
            $fyStart = Carbon::create($date->year - 1, 4, 1);
            $fyEnd   = Carbon::create($date->year, 3, 31);
            $label   = ($date->year - 1) . '-' . $date->year;
        }

        return FinancialYear::firstOrCreate(
            ['club_id' => $clubId, 'fy_label' => $label],
            ['start_date' => $fyStart->toDateString(), 'end_date' => $fyEnd->toDateString(), 'is_closed' => false]
        );
    }

    private function calcMinimumSpendRequired(int $clubId, Member $member, FinancialYear $fy): float
    {
        $rule = MinimumSpendRule::where('club_id', $clubId)->first();
        if (!$rule) return 0;

        $annualMin  = (float) $rule->minimum_amount;
        $monthlyMin = $annualMin / 12;

        $fyStart = Carbon::parse($fy->start_date);
        $fyEnd   = Carbon::parse($fy->end_date);

        // Member's join date: use first purchase start_date, fallback to created_at
        $firstPurchase  = $member->purchaseHistory()->orderBy('start_date')->first();
        $joinDate       = $firstPurchase
            ? Carbon::parse($firstPurchase->start_date)->startOfMonth()
            : Carbon::parse($member->created_at)->startOfMonth();
        $effectiveStart = $joinDate->gt($fyStart) ? $joinDate : $fyStart;

        // Months remaining from effective start to FY end (inclusive)
        $months = (int) $effectiveStart->diffInMonths($fyEnd->copy()->addDay());

        return round($monthlyMin * $months, 2);
    }
}
