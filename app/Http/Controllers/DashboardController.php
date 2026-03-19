<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\Card;
use App\Models\FoodItem;
use App\Models\FoodItemCurrentStock;
use App\Models\Location;
use App\Models\LiquorServing;
use App\Models\Member;
use App\Models\MembershipPurchaseHistory;
use App\Models\MembershipType;
use Carbon\Carbon;
use App\Models\Offer;
use App\Models\StockWarehouse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        try {

            $page_title = 'Dashboard';
            $title = 'Dashboard';

            $clubId     = club_id();

            $today = Carbon::today();

            $next15Days = Carbon::today()->addDays(15);

            $totalMembers = Member::where('club_id', $clubId)
                                  ->where('status', 'active')
                                  ->count();

            $activeMembers = MembershipPurchaseHistory::where('club_id', $clubId)
                                                      ->where('status', 'active')
                                                      ->whereDate('start_date', '<=', $today)
                                                      ->whereDate('expiry_date', '>=', $today)
                                                      ->whereHas('member', function($q){
                                                            $q->where('status', 'active')
                                                              ->whereNull('deleted_at');
                                                        })
                                                      ->distinct('member_id')
                                                      ->count('member_id');

            $expiredMembers = Member::where('club_id', $clubId)
                                    ->where('status', 'active')
                                    ->whereNull('deleted_at')
                                    ->whereDoesntHave('memberships', function($q) use ($today){
                                        $q->where('status', 'active')
                                        ->whereDate('start_date', '<=', $today)
                                          ->whereDate('expiry_date', '>=', $today);
                                        })
                                    ->count();

            $thisMonthSignups = Member::where('club_id', $clubId)
                                      ->where('status', 'active')
                                      ->whereMonth('created_at', Carbon::now()->month)
                                      ->whereYear('created_at', Carbon::now()->year)
                                      ->count();

            $pendingApprovals = ActionApproval::where('club_id', $clubId)
                                              ->where('status', 'pending')
                                              ->count();

            $expiringSoon = Member::where('club_id', $clubId)
                                  ->where('status','active')
                                  ->whereNull('deleted_at')
                                  ->whereHas('memberships', function($q) use($today, $next15Days){
                                         $q->where('status', 'active')
                                         ->whereDate('expiry_date', '>=', $today)
                                           ->whereDate('expiry_date', '<=', $next15Days);
                                        })
                                  ->whereDoesntHave('memberships', function ($q) use ($next15Days) {
                                        $q->where('status', 'active')
                                        ->whereDate('expiry_date', '>', $next15Days);
                                        })
                                  ->count();

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
                'swimMembers',
                'activeMembers',
                'totalMembers',
                'expiredMembers',
                'thisMonthSignups',
                'pendingApprovals',
                'expiringSoon'
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
                    'memberDetails.membershipType',
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

            // Bar stock map for liquor items
            $warehouse = StockWarehouse::where('club_id', $clubId)->first();
            $barLocation = Location::where('name', Location::BAR)->first();
            $barStockMap = [];
            if ($warehouse && $barLocation) {
                $barStockMap = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                    ->where('location_id', $barLocation->id)
                    ->pluck('quantity', 'food_items_id')
                    ->toArray();
            }

            // Beer items: served by BTL, price from food_item_price
            $beerItems = FoodItem::where('club_id', $clubId)
                ->where('item_type', 'liquor')
                ->where('is_beer', 1)
                ->where('is_active', 1)
                ->with('foodItemPrice')
                ->get()
                ->map(function ($item) use ($offerMap, $barStockMap) {
                    return [
                        'id'           => 'beer_' . $item->id,
                        'food_item_id' => $item->id,
                        'name'         => $item->name,
                        'is_beer'      => 1,
                        'volume_ml'    => null,
                        'price'        => isset($item->foodItemPrice) ? (float) $item->foodItemPrice->price : 0,
                        'bar_stock'    => (int) ($barStockMap[$item->id] ?? 0),
                        'offer'        => $offerMap[$item->id] ?? null,
                    ];
                });

            // Spirit servings: ml-wise menu items from liquor_servings
            $spiritServings = LiquorServing::where('club_id', $clubId)
                ->where('is_active', 1)
                ->get()
                ->map(function ($serving) use ($offerMap, $barStockMap) {
                    return [
                        'id'           => 'srv_' . $serving->id,
                        'food_item_id' => $serving->food_item_id,
                        'name'         => $serving->name,
                        'is_beer'      => 0,
                        'volume_ml'    => $serving->volume_ml,
                        'price'        => (float) $serving->price,
                        'bar_stock'    => (int) ($barStockMap[$serving->food_item_id] ?? 0),
                        'offer'        => $offerMap[$serving->food_item_id] ?? null,
                    ];
                });

            $liquorItems = $beerItems->merge($spiritServings)->values();

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
