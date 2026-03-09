<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Member;
use App\Models\MembershipType;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        try {

            $page_title = 'Dashboard';
            $title = 'Dashboard';

            $clubId     = club_id();

            $clubMembershipType = MembershipType::where('name', 'Club Membership')
                ->where('club_id', $clubId)
                ->first();
            $clubMembershipTypeId = $clubMembershipType->id;

            $clubMembers = Member::where('club_id', $clubId)
                ->with([
                    'memberDetails',
                ])
                ->whereHas('memberDetails', function ($query) use ($clubMembershipTypeId) {
                    $query->where('membership_type_id', $clubMembershipTypeId);
                })
                ->orderBy('created_at', 'DESC')
                ->take(3)
                ->get();

            $swimMembershipType = MembershipType::where('name', 'Swimming Membership')
                ->where('club_id', $clubId)
                ->first();
            $swimMembershipTypeId = $swimMembershipType->id;

            $swimMembers = Member::where('club_id', $clubId)
                ->with([
                    'memberDetails',
                ])
                ->whereHas('memberDetails', function ($query) use ($swimMembershipTypeId) {
                    $query->where('membership_type_id', $swimMembershipTypeId);
                })
                ->orderBy('created_at', 'DESC')
                ->take(3)
                ->get();

            return view('dashboard', compact(
                'title',
                'page_title',
                'clubMembers',
                'swimMembers'
            ));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function fetchMemberDetailsByCard($cardNo)
    {
        try {
            $clubId = club_id();
            $card = Card::with('memberMapping')
                ->where('card_no', $cardNo)
                ->first();

            if (!$card) {
                return response()->json([
                    'statusCode' => 404,
                    'error' => 'Card Not Found.'
                ]);
            }

            if (!$card->memberMapping) {
                return response()->json([
                    'statusCode' => 404,
                    'error' => 'Card is not assigned.'
                ]);
            }

            $cardStatus = $card->status;
            // return $card;

            $member = Member::where('club_id', $clubId)
                ->with([
                    'memberDetails',
                    'cardDetails',
                    'purchaseHistory.membershipPlanType',
                    'clubDetails',
                    'walletDetails',
                    'paymentHistory'
                ])
                ->find($card->memberMapping->member_id);

            if (!$member) {
                return response()->json([
                    'statusCode' => 404,
                    'error' => 'Member Not Found..'
                ]);
            }

            return response()->json([
                'data' => $member,
                'statusCode' => 200,
                'cardStatus' => $cardStatus,
                'message' => 'Member Fetched successfully.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function readAllNotification()
    {
        try {
            auth()->user()->unreadNotifications->markAsRead();
            return response()->json([
                'statusCode' => 200,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 404,
                'message' => 'Something Went Wrong'
            ]);
        }


        //    return back();

    }
}
