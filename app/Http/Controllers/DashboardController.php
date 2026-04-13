<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\Card;
use App\Models\FoodItem;
use App\Models\FoodItemCurrentStock;
use App\Models\Location;
use App\Models\LiquorServing;
use App\Models\Member;
use App\Models\MemberFine;
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
                    'cardDetails',
                    'walletDetails',
                    'purchaseHistory',
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
                    'cardDetails',
                    'walletDetails',
                    'purchaseHistory',
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

            // Spirit servings + cocktails: ml-wise menu items from liquor_servings
            $spiritServings = LiquorServing::where('club_id', $clubId)
                ->where('is_active', 1)
                ->get()
                ->map(function ($serving) use ($offerMap, $barStockMap) {
                    return [
                        'id'           => 'srv_' . $serving->id,
                        'food_item_id' => $serving->food_item_id,
                        'name'         => $serving->name,
                        'is_beer'      => 0,
                        'is_cocktail'  => (bool) $serving->is_cocktail,
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

    public function membershipReport(Request $request)
    {
        try {
            $clubId      = club_id();
            $reportType  = $request->report_type;  // memberships | expiry_fines | renewals
            $memberType  = $request->member_type;  // club | swimming | all
            $period      = $request->period;       // daily|weekly|monthly|3months|6months|9months|yearly|custom
            $fromDate    = $request->from_date;
            $toDate      = $request->to_date;

            // Resolve date range
            $today = Carbon::today();
            switch ($period) {
                case 'daily':    $start = $today->copy(); $end = $today->copy(); break;
                case 'weekly':   $start = $today->copy()->subDays(6); $end = $today->copy(); break;
                case 'monthly':  $start = $today->copy()->subDays(29); $end = $today->copy(); break;
                case '3months':  $start = $today->copy()->subMonths(3)->addDay(); $end = $today->copy(); break;
                case '6months':  $start = $today->copy()->subMonths(6)->addDay(); $end = $today->copy(); break;
                case '9months':  $start = $today->copy()->subMonths(9)->addDay(); $end = $today->copy(); break;
                case 'yearly':   $start = $today->copy()->subYear()->addDay(); $end = $today->copy(); break;
                case 'custom':
                    if (!$fromDate || !$toDate) {
                        return response()->json(['statusCode' => 422, 'error' => 'Please provide both from and to dates.']);
                    }
                    $start = Carbon::parse($fromDate)->startOfDay();
                    $end   = Carbon::parse($toDate)->endOfDay();
                    if ($start->diffInDays($end) > 365) {
                        return response()->json(['statusCode' => 422, 'error' => 'Custom date range cannot exceed 1 year.']);
                    }
                    break;
                default: $start = $today->copy()->subDays(29); $end = $today->copy();
            }

            // Resolve membership type IDs
            $typeIds = [];
            if ($memberType === 'club' || $memberType === 'all') {
                $clubType = MembershipType::where('club_id', $clubId)->where('name', 'Club Membership')->first();
                if ($clubType) $typeIds[] = $clubType->id;
            }
            if ($memberType === 'swimming' || $memberType === 'all') {
                $swimType = MembershipType::where('club_id', $clubId)->where('name', 'Swimming Membership')->first();
                if ($swimType) $typeIds[] = $swimType->id;
            }

            // ── Report: New Memberships ──────────────────────────────────────────
            if ($reportType === 'memberships') {

                $rows = MembershipPurchaseHistory::where('club_id', $clubId)
                    ->whereHas('member')
                    ->whereIn('status', ['active', 'pending'])
                    ->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
                    ->when(!empty($typeIds), fn($q) => $q->whereIn('membership_type_id', $typeIds))
                    ->with([
                        'member',
                        'member.cardDetails',
                        'member.memberDetails.membershipType',
                        'membershipPlanType',
                    ])
                    ->orderByDesc('start_date')
                    ->get();

                // Mark first vs renewal
                $firstJoinMap = MembershipPurchaseHistory::where('club_id', $clubId)
                    ->whereHas('member')
                    ->whereIn('status', ['active', 'pending', 'expired', 'cancelled'])
                    ->selectRaw('member_id, MIN(id) as first_id')
                    ->groupBy('member_id')
                    ->pluck('first_id', 'member_id');

                $data = $rows->map(function ($r) use ($firstJoinMap) {
                    return [
                        'name'         => $r->member->name ?? '-',
                        'card_no'      => $r->member->cardDetails->card_no ?? '-',
                        'member_type'  => $r->member->memberDetails->membershipType->name ?? '-',
                        'plan'         => $r->membershipPlanType->name ?? '-',
                        'start_date'   => $r->start_date?->format('d/m/Y') ?? '-',
                        'expiry_date'  => $r->expiry_date?->format('d/m/Y') ?? '-',
                        'fee'          => number_format((float)$r->fee, 2),
                        'net_amount'   => number_format((float)$r->net_amount, 2),
                        'status'       => ucfirst($r->status),
                        'is_renewal'   => isset($firstJoinMap[$r->member_id]) && $firstJoinMap[$r->member_id] != $r->id,
                    ];
                });

                return response()->json(['statusCode' => 200, 'data' => $data, 'from' => $start->format('d/m/Y'), 'to' => $end->format('d/m/Y')]);
            }

            // ── Report: Expiry & Fines ───────────────────────────────────────────
            if ($reportType === 'expiry_fines') {

                $rows = MembershipPurchaseHistory::where('club_id', $clubId)
                    ->whereHas('member')
                    ->whereIn('status', ['active', 'expired'])
                    ->whereBetween('expiry_date', [$start->toDateString(), $end->toDateString()])
                    ->when(!empty($typeIds), fn($q) => $q->whereIn('membership_type_id', $typeIds))
                    ->with([
                        'member',
                        'member.cardDetails',
                        'member.memberDetails.membershipType',
                        'member.pendingFines',
                        'membershipPlanType',
                    ])
                    ->orderByDesc('expiry_date')
                    ->get();

                $data = $rows->map(function ($r) use ($today) {
                    $expiry      = $r->expiry_date;
                    $isExpired   = $expiry && $expiry->lt($today);
                    $daysOverdue = $isExpired ? $expiry->diffInDays($today) : 0;
                    $pendingFine = $r->member->pendingFines->sum('fine_amount');

                    return [
                        'name'          => $r->member->name ?? '-',
                        'card_no'       => $r->member->cardDetails->card_no ?? '-',
                        'member_type'   => $r->member->memberDetails->membershipType->name ?? '-',
                        'plan'          => $r->membershipPlanType->name ?? '-',
                        'expiry_date'   => $expiry?->format('d/m/Y') ?? '-',
                        'days_overdue'  => $daysOverdue,
                        'pending_fine'  => number_format($pendingFine, 2),
                        'expiry_status' => $isExpired ? 'Expired' : 'Expiring Soon',
                    ];
                });

                return response()->json(['statusCode' => 200, 'data' => $data, 'from' => $start->format('d/m/Y'), 'to' => $end->format('d/m/Y')]);
            }

            // ── Report: Renewal History ──────────────────────────────────────────
            if ($reportType === 'renewals') {

                // First join per member
                $firstJoinMap = MembershipPurchaseHistory::where('club_id', $clubId)
                    ->whereHas('member')
                    ->selectRaw('member_id, MIN(id) as first_id')
                    ->groupBy('member_id')
                    ->pluck('first_id', 'member_id');

                $rows = MembershipPurchaseHistory::where('club_id', $clubId)
                    ->whereHas('member')
                    ->whereIn('status', ['active', 'pending'])
                    ->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
                    ->when(!empty($typeIds), fn($q) => $q->whereIn('membership_type_id', $typeIds))
                    ->whereNotIn('id', $firstJoinMap->values()->toArray())  // only renewals
                    ->with([
                        'member',
                        'member.cardDetails',
                        'member.memberDetails.membershipType',
                        'membershipPlanType',
                    ])
                    ->orderByDesc('start_date')
                    ->get();

                $data = $rows->map(function ($r) {
                    return [
                        'name'         => $r->member->name ?? '-',
                        'card_no'      => $r->member->cardDetails->card_no ?? '-',
                        'member_type'  => $r->member->memberDetails->membershipType->name ?? '-',
                        'plan'         => $r->membershipPlanType->name ?? '-',
                        'renewal_date' => $r->start_date?->format('d/m/Y') ?? '-',
                        'expiry_date'  => $r->expiry_date?->format('d/m/Y') ?? '-',
                        'fee'          => number_format((float)$r->fee, 2),
                        'fine_at_renewal' => number_format((float)$r->fine_amount, 2),
                        'net_amount'   => number_format((float)$r->net_amount, 2),
                        'status'       => ucfirst($r->status),
                    ];
                });

                return response()->json(['statusCode' => 200, 'data' => $data, 'from' => $start->format('d/m/Y'), 'to' => $end->format('d/m/Y')]);
            }

            return response()->json(['statusCode' => 422, 'error' => 'Invalid report type.']);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function downloadMembershipReportPdf(Request $request)
    {
        // Reuse the same logic as membershipReport but return PDF
        $request->setMethod('POST');
        $jsonResponse = $this->membershipReport($request);
        $result       = json_decode($jsonResponse->getContent(), true);

        if (($result['statusCode'] ?? 500) !== 200) {
            abort(422, $result['error'] ?? 'Error generating report');
        }

        $tabLabels = [
            'memberships'  => 'Membership Report',
            'expiry_fines' => 'Expiry & Fines Report',
            'renewals'     => 'Renewal History Report',
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.membership_report_pdf', [
            'data'        => $result['data'],
            'from'        => $result['from'],
            'to'          => $result['to'],
            'report_type' => $request->report_type,
            'tab_label'   => $tabLabels[$request->report_type] ?? 'Report',
            'member_type' => $request->member_type,
        ])->setPaper('a4', 'landscape');

        $safeFrom = str_replace('/', '-', $result['from']);
        $safeTo   = str_replace('/', '-', $result['to']);

        return $pdf->download('membership_report_' . $safeFrom . '_to_' . $safeTo . '.pdf');
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
