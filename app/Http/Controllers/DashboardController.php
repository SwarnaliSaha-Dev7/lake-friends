<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\FoodItem;
use App\Models\Member;
use App\Models\MembershipType;
use App\Models\Offer;
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

    public function getOrderItems()
    {
        try {
            $clubId = club_id();
            $today  = now()->toDateString();

            // Build a map of food_item_id => first active offer
            $offerMap = [];
            $activeOffers = Offer::where('club_id', $clubId)
                ->where('status', 'active')
                ->where('start_at', '<=', $today)
                ->where('end_at', '>=', $today)
                ->with(['offerType', 'offerItems'])
                ->get();

            foreach ($activeOffers as $offer) {
                foreach ($offer->offerItems as $oi) {
                    if (!isset($offerMap[$oi->food_items_id])) {
                        $offerMap[$oi->food_items_id] = [
                            'offer_name'     => $offer->name,
                            'type_slug'      => $offer->offerType ? $offer->offerType->slug : '',
                            'discount_value' => (float) $offer->discount_value,
                            'buy_qty'        => (int) $offer->buy_qty,
                            'get_qty'        => (int) $offer->get_qty,
                        ];
                    }
                }
            }

            $foodItems = FoodItem::where('club_id', $clubId)
                ->where('item_type', 'food')
                ->where('is_active', 1)
                ->with('foodItemPrice')
                ->get(['id', 'name', 'item_type'])
                ->map(function ($item) use ($offerMap) {
                    $item->offer = $offerMap[$item->id] ?? null;
                    return $item;
                });

            $liquorItems = FoodItem::where('club_id', $clubId)
                ->where('item_type', 'liquor')
                ->where('is_active', 1)
                ->with('foodItemPrice')
                ->get(['id', 'name', 'item_type'])
                ->map(function ($item) use ($offerMap) {
                    $item->offer = $offerMap[$item->id] ?? null;
                    return $item;
                });

            return response()->json(['statusCode' => 200, 'foodItems' => $foodItems, 'liquorItems' => $liquorItems]);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
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
