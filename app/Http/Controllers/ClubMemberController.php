<?php

namespace App\Http\Controllers;

use App\Models\GstRate;
use App\Models\MembershipPlanType;
use App\Models\MembershipType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubMemberController extends Controller
{
    public function list()
    {
        $title = 'Club Member list';
        $page_title = 'Manage Club Member';

        $clubId = club_id();
        $membershipPlanTypeList = [];

        $membershipType = MembershipType::where('name', 'Club Membership')
                                        ->where('club_id', $clubId)
                                        ->first();
        $membershipTypeId = $membershipType->id;

        $clubMembershipPlanTypeList = MembershipPlanType::where('membership_type_id', $membershipTypeId)
                                                    ->where('is_active', 1)
                                                    ->get();

        $gstPercentage = GstRate::where('club_id', Auth::user()->club_id)
                                        ->value('gst_percentage') ?? 0;

        return view('club_member.list', compact(
                                                'title',
                                                'page_title',
                                                'clubMembershipPlanTypeList',
                                                'gstPercentage'));
    }

    public function store()
    {
        return 748237;
    }

    public function getClubMemberPlanPrice(Request $request)
    {
        try {
            $data['package'] = MembershipPlanType::find($request->membership_package_id);

            return response()->json([
                'data' => $data,
                'statusCode' => 200,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                // 'error' => $th->getMessage(),
            ]);
        }

    }

    // public function mealPriceEdit(Request $request)
    // {
    //     try {
    //         $data['mealPrice'] = SchoolMealPrice::where('school_id', $request->schoolId)->first();

    //         return response()->json([
    //             'data' => $data,
    //             'statusCode' => 200,
    //         ]);

    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'statusCode' => 500,
    //             // 'error' => $th->getMessage(),
    //         ]);
    //     }
    // }
}
