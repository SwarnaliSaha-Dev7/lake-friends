<?php

namespace App\View\Composers;

use App\Models\Bank;
use App\Models\GstRate;
use App\Models\MembershipPlanType;
use App\Models\MembershipType;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppLayoutComposer
{
    public function compose(View $view): void
    {
        if (!Auth::check()) return;

        $clubId = Auth::user()->club_id;

        $clubMembershipType = MembershipType::where('name', 'Club Membership')
            ->where('club_id', $clubId)->first();

        $renewalPlanTypes = $clubMembershipType
            ? MembershipPlanType::where('membership_type_id', $clubMembershipType->id)
                ->where('is_active', 1)->get()
            : collect();

        $globalGstPercentage = GstRate::where('club_id', $clubId)
            ->value('gst_percentage') ?? 0;

        $globalBankList = Bank::where('club_id', $clubId)->get();

        $view->with([
            'renewalPlanTypes'    => $renewalPlanTypes,
            'globalGstPercentage' => $globalGstPercentage,
            'globalBankList'      => $globalBankList,
        ]);
    }
}
