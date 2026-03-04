<?php

namespace App\Http\Controllers;

use App\Models\GstRate;
use App\Models\Member;
use App\Models\MembershipFormDetail;
use App\Models\MembershipPlanType;
use App\Models\MembershipPurchaseHistory;
use App\Models\MembershipType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function store(Request $request)
    {
        try {

            $clubId = club_id();

            // Check if email already exists in same club
            $exists = Member::where('email', $request->email)
                            ->where('club_id', $clubId)
                            ->exists();

            if ($exists) {
                return response()->json([
                    'statusCode' => 409,
                    'message' => 'Email already exists'
                ]);
            }

            DB::beginTransaction();

            // Generate Member Code (example)
            $memberCode = 'LF-' . time();

            $member = Member::create([
                'club_id'     => $clubId,
                'member_code' => $memberCode,
                'name'        => $request->name,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'address'     => $request->address,
                'status'      => 1
            ]);

            //fetch the membership type id
            $membershipType = MembershipType::where('name', 'Club Membership')
                                        ->where('club_id', $clubId)
                                        ->first();
            $membershipTypeId = $membershipType->id;

            MembershipFormDetail::create([
                'member_id' => $member->id,
                'membership_type_id' => $membershipTypeId,
                'details' => [
                    'blood_grp' => $request->blood_grp,
                    'spouse_name' => $request->spouse_name,
                    'spouse_email' => $request->spouse_email,
                    'spouse_phone' => $request->spouse_phone,
                    'spouse_blood_grp' => $request->spouse_blood_grp,
                    'spouse_address' => $request->spouse_address,
                ]
            ]);

            // Get Plan Details
            $plan = MembershipPlanType::where('id', $request->membership_plan_type_id)
                                        ->where('is_active', 1)
                                        ->first();
            if (!$plan) {
                DB::rollBack();
                return response()->json([
                    'statusCode' => 404,
                    'message' => 'Membership plan not found'
                ]);
            }

            $startDate = Carbon::today();

            if ($plan->is_lifetime) {
                $expiryDate = null; // Lifetime membership
            } else {
                $expiryDate = $startDate->copy()->addMonths($plan->duration_months);
            }

            $fee = $plan->price;
            $fineAmount = 0;
            $netAmount = $fee + $fineAmount;

            // Store Purchase History
            MembershipPurchaseHistory::create([
                'club_id'                 => $clubId,
                'member_id'               => $member->id,
                'membership_type_id'      => $membershipTypeId,
                'membership_plan_type_id' => $plan->id,
                'fee'                     => $fee,
                'fine_amount'             => $fineAmount,
                'net_amount'              => $netAmount,
                'start_date'              => $startDate,
                'expiry_date'             => $expiryDate,
                'status'                  => 1
            ]);



            DB::commit();

            return response()->json([
                // 'data' => $data,
                'statusCode' => 200,
                'message' => 'Member created successfully'
            ]);

        } catch (\Throwable $th) {

            DB::rollBack();

            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function getPlanPrice(Request $request)
    {
        try {
            $data['plan'] = MembershipPlanType::find($request->planTypeId);

            if (!$data['plan']) {
                return response()->json([
                    'statusCode' => 404,
                    'message' => 'Plan not found'
                ]);
            }

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
