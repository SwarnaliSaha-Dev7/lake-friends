<?php

namespace App\Http\Controllers;

use App\Models\GstRate;
use App\Models\MembershipPlanType;
use App\Models\MembershipType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SwimmingMemberController extends Controller
{
    public function list()
    {
        $title = 'Swimming Member list';
        $page_title = 'Manage Swimming Member';
        // dd('hi');

        $membershipTypeId = MembershipType::where('name', 'Swimming Membership')->value('id');

        // dd($membershipTypeId);
        $membershipPlanList = MembershipPlanType::where('membership_type_id', $membershipTypeId)->get();

        $gstPercentage = GstRate::where('club_id', Auth::user()->club_id)
            ->value('gst_percentage') ?? 0;

        return view('swimming_member.list', compact(
            'title',
            'page_title',
            'membershipPlanList',
            'gstPercentage'
        ));
    }

    public function store(Request $request)
    {
        return "coming from controller";
    }
}
