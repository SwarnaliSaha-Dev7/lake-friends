<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\FinancialYear;
use App\Models\FoodItem;
use App\Models\FoodItemCurrentStock;
use App\Models\GstRate;
use App\Models\LiquorServing;
use App\Models\Location;
use App\Models\Member;
use App\Models\MemberFinancialSummary;
use App\Models\MembershipPurchaseHistory;
use App\Models\MembershipType;
use App\Models\MinimumSpendRule;
use App\Models\OrderSession;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\StockLedger;
use App\Models\StockWarehouse;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSessionController extends Controller
{
    public function index()
    {
        try {
            $clubId     = club_id();
            $page_title = 'Current Order';
            $title      = 'Current Order';

            $sessions = OrderSession::where('club_id', $clubId)
                ->whereDate('created_at', now())
                ->with(['member', 'orders'])
                ->latest()
                ->get();

            $clubMembershipTypeId = MembershipType::where('club_id', $clubId)
                ->where('name', 'Club Membership')
                ->value('id');

            $members = Member::where('club_id', $clubId)
                ->where('status', 'active')
                ->where('membership_type_id', $clubMembershipTypeId)
                ->with([
                    'cardDetails:cards.id,card_no',
                    'spouseCardDetails:cards.id,card_no',
                    'memberDetails:id,member_id,details',
                ])
                ->orderBy('name')
                ->get(['id', 'name', 'member_code']);

            return view('order_sessions.list', compact('title', 'page_title', 'sessions', 'members'));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function store(Request $request)
    {
        try {
            $clubId   = club_id();
            $memberId = $request->input('member_id');

            $member = Member::with(['membershipType', 'memberDetails'])->where('club_id', $clubId)->findOrFail($memberId);
            $holderType = $request->input('order_person_holder_type') === 'spouse' ? 'spouse' : 'member';
            $orderPersonName = $this->resolveOrderPersonName($member, $holderType);

            // If member already has an open session today, return it
            $existing = OrderSession::where('club_id', $clubId)
                ->where('member_id', $memberId)
                ->where('status', 'open')
                ->whereDate('created_at', now())
                ->first();

            if ($existing) {
                $wallet = Wallet::where('member_id', $memberId)->first();
                return response()->json([
                    'statusCode' => 200,
                    'message'    => 'Existing session found for ' . $member->name,
                    'is_existing' => true,
                    'session'    => [
                        'id'             => $existing->id,
                        'session_no'     => $existing->session_no,
                        'status'         => 'open',
                        'member_id'      => $member->id,
                        'member_name'    => $member->name,
                        'order_person_name' => $existing->order_person_name ?: $member->name,
                        'order_person_holder_type' => $existing->order_person_holder_type ?: 'member',
                        'member_code'    => $member->member_code,
                        'member_type'    => $member->membershipType?->name ?? '—',
                        'wallet_balance' => $wallet ? number_format($wallet->current_balance, 2) : '0.00',
                        'order_count'    => $existing->orders()->whereNotIn('status', ['cancelled'])->count(),
                        'pending_total'  => number_format($existing->orders()->where('status', 'pending')->sum('net_amount'), 2),
                    ],
                ]);
            }

            $sessionNo = generateSessionNo();
            //fetch current balance
            $wallet    = Wallet::where('member_id', $memberId)->first();

            $session = OrderSession::create([
                'club_id'                => $clubId,
                'member_id'              => $memberId,
                'order_person_name'      => $orderPersonName,
                'order_person_holder_type' => $holderType,
                'session_no'             => $sessionNo,
                'status'                 => 'open',
                'opening_wallet_balance' => $wallet ? (float) $wallet->current_balance : 0,
                'topup_from_at'          => now(),
                'created_by'             => Auth::id(),
            ]);

            return response()->json([
                'statusCode' => 200,
                'message'    => 'Session opened for ' . $member->name,
                'session'    => [
                    'id'             => $session->id,
                    'session_no'     => $session->session_no,
                    'status'         => 'open',
                    'member_id'      => $member->id,
                    'member_name'    => $member->name,
                    'order_person_name' => $session->order_person_name ?: $member->name,
                    'order_person_holder_type' => $session->order_person_holder_type ?: 'member',
                    'member_code'    => $member->member_code,
                    'member_type'    => $member->membershipType?->name ?? '—',
                    'wallet_balance' => $wallet ? number_format($wallet->current_balance, 2) : '0.00',
                    'order_count'    => 0,
                    'pending_total'  => '0.00',
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $session = OrderSession::with(['member.memberDetails', 'orders.items.foodItem', 'walletTransaction'])
                ->where('club_id', club_id())
                ->findOrFail($id);

            $pendingTotal = $session->orders
                ->where('status', 'pending')
                ->sum('net_amount');

            $wallet = Wallet::where('member_id', $session->member_id)->first();

            $billTxnAt = $session->walletTransaction?->created_at ?? $session->updated_at ?? now();

            $openingBalance = (float) ($session->opening_wallet_balance ?? 0);
            $topupFromAt    = $session->topup_from_at ?? $session->created_at;

            $topupDuringSession = (float) WalletTransaction::where('member_id', $session->member_id)
                ->where('direction', 'credit')
                ->where('txn_type', 'recharge')
                ->whereBetween('created_at', [$topupFromAt, $billTxnAt])
                ->sum('amount');

            $billedAmount   = (float) ($session->net_amount ?? 0);
            $openingPlusTop = $openingBalance + $topupDuringSession;
            $closingBalance = $openingPlusTop - $billedAmount;

            $cardBalanceInfo = [
                'opening_balance'    => $openingBalance,
                'last_topup'         => $topupDuringSession,
                'opening_plus_topup' => $openingPlusTop,
                'billed_amount'      => $billedAmount,
                'closing_balance'    => $closingBalance,
            ];

            $minimumRequired = (float) ($session->minimum_spend_required ?? 0);
            $usedSoFar       = (float) ($session->total_spend ?? 0);

            $minimumUsageInfo = [
                'applicable'      => $minimumRequired > 0,
                'minimum_charges' => $minimumRequired,
                'used_so_far'     => $usedSoFar,
                'balance'         => max(0, round($minimumRequired - $usedSoFar, 2)),
            ];

            $walletBalanceDisplay = $session->status === 'billed'
                ? $closingBalance
                : ($wallet ? (float) $wallet->current_balance : 0);

            return response()->json([
                'statusCode'     => 200,
                'data'           => $session,
                'pending_total'  => number_format($pendingTotal, 2),
                'wallet_balance' => round($walletBalanceDisplay, 2),
                'card_balance_info' => $cardBalanceInfo,
                'minimum_usage_info' => $minimumUsageInfo,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function addOrder(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $clubId  = club_id();
            $session = OrderSession::where('club_id', $clubId)->findOrFail($id);

            if ($session->status !== 'open') {
                return response()->json(['statusCode' => 422, 'message' => 'Session is no longer open.']);
            }

            $memberId      = $session->member_id;
            $netAmount     = (float) $request->input('net_amount');
            $items         = $request->input('items', []);
            $orderTaxable  = (float) $request->input('taxable_amount');
            $orderGstAmt   = (float) $request->input('gst_amount');

            if (empty($items)) {
                return response()->json(['statusCode' => 422, 'message' => 'No items in order.']);
            }

            // Cumulative wallet check (locked to prevent concurrent double-spend)
            $wallet = Wallet::where('member_id', $memberId)->lockForUpdate()->first();
            if (!$wallet) {
                return response()->json(['statusCode' => 422, 'message' => 'Wallet not found for this member.']);
            }

            // Re-derive every serving-based line (peg or cocktail) from LiquorServing
            // server-side, exactly as BarOrderController does — not trusted from the
            // client. Resolves the optional mixer/soda too, which the client never
            // needs to know about, so it can't be omitted to skip the deduction
            // while still charging the cocktail's full (mixer-inclusive) price.
            //
            // The mixer's own catalog price is already folded into the price the
            // client built this line from — see getOrderItems(), which now returns
            // a serving's price as base + mixer cost (never naming the mixer). So
            // $item['total_amount']/'unit_price' need no adjustment here; only the
            // GST does, since the client has no way to know part of a "liquor"
            // (GST-free) line's price is actually a beverage mixer that must carry
            // beverage GST.
            $secondaryByIndex = [];
            foreach ($items as $idx => &$item) {
                if (!empty($item['serving_id'])) {
                    $serving = LiquorServing::where('club_id', $clubId)
                        ->with(['foodItem', 'secondaryFoodItem.foodItemPrice'])
                        ->find((int) $item['serving_id']);

                    if (!$serving || !$serving->foodItem) {
                        DB::rollBack();
                        return response()->json(['statusCode' => 422, 'message' => 'One or more items are no longer available.']);
                    }

                    $baseIsBeer = (bool) $serving->foodItem->is_beer;
                    $qty        = (int) $item['quantity'];

                    $item['food_item_id'] = $serving->food_item_id;
                    $item['unit']         = $baseIsBeer ? 'btl' : 'ml';
                    $item['is_cocktail']  = (bool) $serving->is_cocktail;
                    $item['volume_ml']    = (int) $serving->volume_ml;
                    $item['deduct_qty']   = $baseIsBeer ? $qty : $qty * (int) $serving->volume_ml;

                    if ($serving->secondary_food_item_id && $serving->secondary_quantity) {
                        $secondaryUnitPrice = (float) ($serving->secondaryFoodItem->foodItemPrice->price ?? 0);
                        $secondaryTotalQty  = $qty * (int) $serving->secondary_quantity;
                        $secondaryCost      = round($secondaryUnitPrice * $secondaryTotalQty, 2);

                        $secondaryByIndex[$idx] = [
                            'food_item_id' => (int) $serving->secondary_food_item_id,
                            'name'         => $serving->secondaryFoodItem->name ?? null,
                            'quantity'     => $secondaryTotalQty,
                            'cost'         => $secondaryCost,
                        ];
                    }
                }
            }
            unset($item);

            // The mixer's cost is already part of $orderTaxable (the client's price
            // already included it, via getOrderItems()) — only its GST (at the
            // beverage rate, since a mixer is always a beverage) still needs adding.
            $secondaryCostSum = array_sum(array_column($secondaryByIndex, 'cost'));
            if ($secondaryCostSum > 0) {
                $beverageGstRate = (float) (GstRate::where('club_id', $clubId)->where('gst_type', 'beverage')->value('gst_percentage') ?? 0);
                $secondaryGst    = round($secondaryCostSum * $beverageGstRate / 100, 2);

                $orderGstAmt += $secondaryGst;
                $netAmount   += $secondaryGst;
            }
            // Effective GST rate for this order — a food+beverage mix blends two
            // different rates (and liquor contributes none), so this is derived
            // from what was actually charged rather than a single fixed rate.
            $effectiveGst = $orderTaxable > 0 ? round(($orderGstAmt / $orderTaxable) * 100, 2) : 0;

            $sessionPending = (float) $session->orders()
                ->where('status', 'pending')
                ->sum('net_amount');

            if ((float) $wallet->current_balance < ($sessionPending + $netAmount)) {
                return response()->json([
                    'statusCode'      => 422,
                    'message'         => 'Insufficient wallet balance.',
                    'wallet_balance'  => number_format($wallet->current_balance, 2),
                    'session_pending' => number_format($sessionPending, 2),
                    'required_amount' => number_format($netAmount, 2),
                ]);
            }

            // Bar stock check for liquor items (aggregate per food_item_id first)
            $liquorItems = array_filter($items, fn($i) => in_array($i['unit'] ?? '', ['ml', 'btl']));
            $warehouse   = null;
            $barLocation = null;

            if (!empty($liquorItems) || !empty($secondaryByIndex)) {
                $warehouse   = $this->getWarehouse($clubId);
                $barLocation = $this->getBarLocation();

                // Aggregate total deduction per food_item_id to catch multi-row same-item orders
                $deductMap = [];
                $unitMap   = [];
                foreach ($liquorItems as $item) {
                    $foodItemId = (int) $item['food_item_id'];
                    $deductMap[$foodItemId] = ($deductMap[$foodItemId] ?? 0) + (int) $item['deduct_qty'];
                    $unitMap[$foodItemId]   = $item['unit'];
                }
                foreach ($secondaryByIndex as $secondary) {
                    $sid = $secondary['food_item_id'];
                    $deductMap[$sid] = ($deductMap[$sid] ?? 0) + $secondary['quantity'];
                    $unitMap[$sid]   = 'btl'; // beverages/mixers are always whole-bottle
                }

                foreach ($deductMap as $foodItemId => $totalDeduct) {
                    $stock     = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                        ->where('location_id', $barLocation->id)
                        ->where('food_items_id', $foodItemId)
                        ->first();
                    $available = $stock ? (int) $stock->quantity : 0;

                    if ($available < $totalDeduct) {
                        $foodItem  = FoodItem::find($foodItemId);
                        $unitLabel = ($unitMap[$foodItemId] === 'btl') ? 'BTL' : 'ml';
                        DB::rollBack();
                        return response()->json([
                            'statusCode' => 422,
                            'message'    => "Insufficient bar stock for \"{$foodItem->name}\". Available: {$available} {$unitLabel}, Required: {$totalDeduct} {$unitLabel}.",
                        ]);
                    }
                }
            }

            // Generate order number
            $orderNo = generateOrderNo();

            // Create order (no wallet deduction, no bill_no/mr_no yet)
            $order = RestaurantOrder::create([
                'club_id'         => $clubId,
                'session_id'      => $session->id,
                'member_id'       => $memberId,
                'order_no'        => $orderNo,
                'ac_head'         => 'Restaurant Order',
                'taxable_amount'  => $orderTaxable,
                'discount_amount' => $request->input('discount_amount'),
                'gst_percentage'  => $effectiveGst,
                'gst_amount'      => $orderGstAmt,
                'net_amount'      => $netAmount,
                'status'          => 'pending',
            ]);

            // Create order items + deduct bar stock for liquor
            foreach ($items as $idx => $item) {
                $unit       = $item['unit'];
                $isLiquor   = in_array($unit, ['ml', 'btl']);
                $volumeMl   = ($unit === 'ml' && !empty($item['volume_ml'])) ? (int) $item['volume_ml'] : null;
                $isCocktail = !empty($item['is_cocktail']);
                $secondary  = $secondaryByIndex[$idx] ?? null;

                $metadata = null;
                if ($isCocktail) {
                    // volume_ml is null here for a bottle-based mocktail (its base is a
                    // beverage, deducted by whole bottle) — that's expected and consistent
                    // with how the rest of the app treats bottle-based items.
                    $metadata = [
                        'volume_ml'     => $volumeMl,
                        'is_cocktail'   => true,
                        'cocktail_name' => $item['cocktail_name'] ?? ($item['name'] ?? ''),
                        'serving_id'    => $item['serving_id'] ?? null,
                    ];
                    // Mixer/soda deducted alongside the base spirit — never its own
                    // bill line, recorded here only so cancel/void can reverse it.
                    if ($secondary) {
                        $metadata['secondary_food_item_id'] = $secondary['food_item_id'];
                        $metadata['secondary_item_name']    = $secondary['name'];
                        $metadata['secondary_quantity']     = $secondary['quantity'];
                    }
                } elseif ($volumeMl) {
                    $metadata = ['volume_ml' => $volumeMl];
                }

                RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'food_item_id'        => $item['food_item_id'],
                    'quantity'            => $item['quantity'],
                    'unit'                => $unit,
                    'unit_price'          => $item['unit_price'],
                    'offer_applied'       => !empty($item['offer_applied']) ? $item['offer_applied'] : null,
                    'total_amount'        => $item['total_amount'],
                    'metadata'            => $metadata,
                ]);

                if ($isLiquor) {
                    $deductQty   = (int) $item['deduct_qty'];
                    $foodItemId  = (int) $item['food_item_id'];
                    $warehouse   = $warehouse   ?? $this->getWarehouse($clubId);
                    $barLocation = $barLocation ?? $this->getBarLocation();

                    $stock = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                        ->where('location_id', $barLocation->id)
                        ->where('food_items_id', $foodItemId)
                        ->first();

                    if ($stock) {
                        $stock->decrement('quantity', $deductQty);
                    }

                    StockLedger::create([
                        'warehouse_id'   => $warehouse->id,
                        'location_id'    => $barLocation->id,
                        'food_items_id'  => $foodItemId,
                        'movement_type'  => 'sale',
                        'direction'      => 'out',
                        'quantity'       => $deductQty,
                        'reference_type' => 'order',
                    ]);
                }

                if ($secondary) {
                    $warehouse   = $warehouse   ?? $this->getWarehouse($clubId);
                    $barLocation = $barLocation ?? $this->getBarLocation();

                    $secondaryStock = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                        ->where('location_id', $barLocation->id)
                        ->where('food_items_id', $secondary['food_item_id'])
                        ->first();

                    if ($secondaryStock) {
                        $secondaryStock->decrement('quantity', $secondary['quantity']);
                    }

                    StockLedger::create([
                        'warehouse_id'   => $warehouse->id,
                        'location_id'    => $barLocation->id,
                        'food_items_id'  => $secondary['food_item_id'],
                        'movement_type'  => 'sale',
                        'direction'      => 'out',
                        'quantity'       => $secondary['quantity'],
                        'reference_type' => 'order',
                    ]);
                }
            }

            $newPendingTotal = (float) $session->orders()->where('status', 'pending')->sum('net_amount');

            DB::commit();

            return response()->json([
                'statusCode'    => 200,
                'message'       => 'Order added to session.',
                'order_no'      => $orderNo,
                'order_id'      => $order->id,
                'pending_total' => number_format($newPendingTotal, 2),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function cancelOrder($sessionId, $orderId)
    {
        DB::beginTransaction();
        try {
            $clubId  = club_id();
            $session = OrderSession::where('club_id', $clubId)->findOrFail($sessionId);

            if ($session->status !== 'open') {
                return response()->json(['statusCode' => 422, 'message' => 'Cannot cancel order — session is ' . $session->status . '.']);
            }

            $order = RestaurantOrder::with('items')
                ->where('session_id', $session->id)
                ->where('status', 'pending')
                ->findOrFail($orderId);

            // Restore bar stock
            $this->restoreStock($order, $clubId);

            $order->update(['status' => 'cancelled']);

            $newPendingTotal = (float) $session->orders()->where('status', 'pending')->sum('net_amount');

            DB::commit();

            return response()->json([
                'statusCode'    => 200,
                'message'       => 'Order ' . $order->order_no . ' cancelled.',
                'pending_total' => number_format($newPendingTotal, 2),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function generateBill($id)
    {
        DB::beginTransaction();
        try {
            $clubId  = club_id();
            $session = OrderSession::with('orders.items')
                ->where('club_id', $clubId)
                ->findOrFail($id);

            if ($session->status !== 'open') {
                return response()->json(['statusCode' => 422, 'message' => 'Session is already ' . $session->status . '.']);
            }

            $pendingOrders = $session->orders->where('status', 'pending');

            if ($pendingOrders->isEmpty()) {
                return response()->json(['statusCode' => 422, 'message' => 'No pending orders to bill.']);
            }

            $totalTaxable  = $pendingOrders->sum('taxable_amount');
            $totalDiscount = $pendingOrders->sum('discount_amount');
            $totalGst      = $pendingOrders->sum('gst_amount');
            $totalNet      = (float) $pendingOrders->sum('net_amount');

            // Final wallet check (locked to prevent concurrent double-spend)
            $wallet = Wallet::where('member_id', $session->member_id)->lockForUpdate()->first();
            if (!$wallet) {
                return response()->json(['statusCode' => 422, 'message' => 'Wallet not found.']);
            }

            if ((float) $wallet->current_balance < $totalNet) {
                return response()->json([
                    'statusCode'     => 422,
                    'message'        => 'Insufficient wallet balance for final bill.',
                    'wallet_balance' => number_format($wallet->current_balance, 2),
                    'total_net'      => number_format($totalNet, 2),
                ]);
            }

            // Single wallet deduction
            $newBalance = (float) $wallet->current_balance - $totalNet;
            $wallet->update(['current_balance' => $newBalance]);

            $walletTxn = WalletTransaction::create([
                'wallet_id'  => $wallet->id,
                'member_id'  => $session->member_id,
                'amount'     => $totalNet,
                'direction'  => 'debit',
                'txn_type'   => 'Food and Liquor Order',
                'created_by' => Auth::id(),
            ]);

            // Use session's original date for MR/Bill numbers (handles re-opened cancelled sessions)
            $sessionDate = $session->created_at->toDateString();

            // Mark all pending orders as paid
            foreach ($pendingOrders as $order) {
                $order->update([
                    'wallet_transactions_id' => $walletTxn->id,
                    'mr_no'                  => generateMrNo($sessionDate),
                    'bill_no'                => generateBillNo($sessionDate),
                    'status'                 => 'paid',
                ]);
            }

            // --------------------------
            // Minimum Usage Info
            // --------------------------
            $billTxnAt = $walletTxn->created_at;

            $billDate = Carbon::parse($billTxnAt);
            [$fyStartDate, $fyEndDate] = financialYearRange($billDate);
            $fyStart = Carbon::parse($fyStartDate)->startOfDay();
            $fyEnd   = Carbon::parse($fyEndDate)->endOfDay();
            $asOfDate = $billDate->toDateString();

            $activePurchase = MembershipPurchaseHistory::where('club_id', $session->club_id)
                ->where('member_id', $session->member_id)
                ->where('status', 'active')
                ->whereDate('start_date', '<=', $asOfDate)
                ->where(function ($q) use ($asOfDate) {
                    $q->whereNull('expiry_date')
                        ->orWhereDate('expiry_date', '>=', $asOfDate);
                })
                ->with('membershipPlanType:id,is_minimum_spend_applicable')
                ->latest('start_date')
                ->first();

            $isMinimumSpendApplicable = (bool) ($activePurchase?->membershipPlanType?->is_minimum_spend_applicable ?? false);

            $minimumCharges = 0.0;
            $usedSoFar      = 0.0;

            if ($isMinimumSpendApplicable) {
                $fy = FinancialYear::where('club_id', $session->club_id)
                    ->whereDate('start_date', '<=', $asOfDate)
                    ->whereDate('end_date', '>=', $asOfDate)
                    ->first();

                $summary = $fy
                    ? MemberFinancialSummary::where('club_id', $session->club_id)
                        ->where('member_id', $session->member_id)
                        ->where('financial_year_id', $fy->id)
                        ->first()
                    : null;

                if ($summary) {
                    $minimumCharges = (float) ($summary->minimum_spend_required ?? 0);
                    $usedSoFar      = (float) ($summary->total_spend ?? 0);
                } else {
                    $rule      = MinimumSpendRule::where('club_id', $session->club_id)->first();
                    $annualMin = (float) ($rule->minimum_amount ?? 0);
                    $monthlyMin = $annualMin / 12;

                    $firstPurchase = MembershipPurchaseHistory::where('member_id', $session->member_id)
                        ->orderBy('start_date')
                        ->first();
                    $joinDate = $firstPurchase
                        ? Carbon::parse($firstPurchase->start_date)->startOfMonth()
                        : Carbon::parse($session->member->created_at)->startOfMonth();
                    $effectiveStart = $joinDate->gt($fyStart) ? $joinDate : $fyStart->copy();
                    $months         = (int) $effectiveStart->diffInMonths($fyEnd->copy()->addDay());
                    $minimumCharges = round($monthlyMin * $months, 2);

                    $spendTypes = [
                        'spend', 'add_on_purchase', 'locker_purchase',
                        'Food and Liquor Order', 'Bar Order', 'Restaurant Food Order',
                    ];

                    $usedSoFar = (float) WalletTransaction::where('member_id', $session->member_id)
                        ->where('direction', 'debit')
                        ->whereIn('txn_type', $spendTypes)
                        ->whereBetween('created_at', [$fyStart, $fyEnd])
                        ->where('created_at', '<=', $billTxnAt)
                        ->sum('amount');
                }
            }

            // Effective GST rate for the whole bill — pending orders can mix food
            // (restaurant rate), beverage (beverage rate), and liquor (GST-free),
            // so this reflects what was actually charged rather than a fixed rate.
            $effectiveGst = $totalTaxable > 0 ? round(($totalGst / $totalTaxable) * 100, 2) : 0;

            // Update session totals
            $session->update([
                'status'                 => 'billed',
                'taxable_amount'         => $totalTaxable,
                'discount_amount'        => $totalDiscount,
                'gst_percentage'         => $effectiveGst,
                'gst_amount'             => $totalGst,
                'net_amount'             => $totalNet,
                'bill_no'                => generateBillNo($sessionDate),
                'mr_no'                  => generateMrNo($sessionDate),
                'wallet_transactions_id' => $walletTxn->id,
                'minimum_spend_required'  => $minimumCharges,
                'total_spend'             => $usedSoFar,
            ]);

            DB::commit();

            return response()->json([
                'statusCode'     => 200,
                'message'        => 'Bill generated! Total: Rs ' . number_format($totalNet, 2),
                'session_id'     => $session->id,
                'session_no'     => $session->session_no,
                'net_amount'     => number_format($totalNet, 2),
                'wallet_balance' => number_format($newBalance, 2),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function cancelSession($id)
    {
        DB::beginTransaction();
        try {
            $clubId  = club_id();
            $session = OrderSession::with('orders.items')
                ->where('club_id', $clubId)
                ->findOrFail($id);

            if ($session->status === 'cancelled') {
                return response()->json(['statusCode' => 422, 'message' => 'Session is already cancelled.']);
            }

            $isBilled = $session->status === 'billed';

            $ordersToReverse = $isBilled
                ? $session->orders->where('status', 'paid')
                : $session->orders->where('status', 'pending');

            foreach ($ordersToReverse as $order) {
                $this->restoreStock($order, $clubId);
                $order->update(['status' => 'cancelled']);
            }

            // Wallet refund only if billed
            $refundAmount = 0;
            if ($isBilled) {
                $refundAmount = (float) $session->net_amount;
                $wallet       = Wallet::where('member_id', $session->member_id)->first();
                if ($wallet && $refundAmount > 0) {
                    $wallet->update(['current_balance' => (float) $wallet->current_balance + $refundAmount]);
                    WalletTransaction::create([
                        'wallet_id'  => $wallet->id,
                        'member_id'  => $session->member_id,
                        'amount'     => $refundAmount,
                        'direction'  => 'credit',
                        'txn_type'   => 'refund',
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            $session->update(['status' => 'cancelled', 'cancelled_by' => Auth::id()]);

            DB::commit();

            $message = $isBilled
                ? 'Session cancelled and Rs ' . number_format($refundAmount, 2) . ' refunded to wallet.'
                : 'Session cancelled.';

            return response()->json([
                'statusCode'    => 200,
                'message'       => $message,
                'refund_amount' => $refundAmount > 0 ? number_format($refundAmount, 2) : null,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function downloadInvoice($id)
    {
        try {
            $session = OrderSession::with(['member.memberDetails', 'orders.items.foodItem'])
                ->where('club_id', club_id())
                ->findOrFail($id);

            // Aggregate items from non-cancelled orders
            $allItems = $session->orders
                ->whereNotIn('status', ['cancelled'])
                ->flatMap(fn($o) => $o->items);

            // Aggregate food items (group by food_item_id + offer)
            $foodItems = $allItems->where('unit', 'plate')
                ->groupBy(fn($i) => $i->food_item_id . '_' . ($i->offer_applied ? json_encode($i->offer_applied) : ''))
                ->map(function ($group) {
                    $first = $group->first();
                    return (object) [
                        'foodItem'      => $first->foodItem,
                        'quantity'      => $group->sum('quantity'),
                        'unit'          => $first->unit,
                        'unit_price'    => $first->unit_price,
                        'offer_applied' => $first->offer_applied,
                        'total_amount'  => $group->sum('total_amount'),
                        'metadata'      => $first->metadata,
                    ];
                })->values();

            // Aggregate liquor/beverage items (group by food_item_id + unit + volume +
            // offer + serving_id). Both share unit ml/btl, so they're told apart by the
            // underlying item's item_type — beverage is taxed, liquor never is. serving_id
            // is included so two different cocktail/mocktail recipes that happen to share
            // the same base item (e.g. Bloody Mary and Screwdriver both on ABSOLUTE, or
            // Fresh Lime Soda and Virgin Mojito both on SODA) never get merged into one row.
            $aggregateBottleItems = function ($items) {
                return $items
                    ->groupBy(fn($i) => $i->food_item_id . '_' . $i->unit . '_' . ($i->metadata['volume_ml'] ?? '') . '_' . ($i->offer_applied ? json_encode($i->offer_applied) : '') . '_' . ($i->metadata['serving_id'] ?? ''))
                    ->map(function ($group) {
                        $first = $group->first();
                        return (object) [
                            'foodItem'      => $first->foodItem,
                            'quantity'      => $group->sum('quantity'),
                            'unit'          => $first->unit,
                            'unit_price'    => $first->unit_price,
                            'offer_applied' => $first->offer_applied,
                            'total_amount'  => $group->sum('total_amount'),
                            'metadata'      => $first->metadata,
                        ];
                    })->values();
            };

            $bottleItems     = $allItems->whereIn('unit', ['ml', 'btl']);
            $liquorItems     = $aggregateBottleItems($bottleItems->filter(fn($i) => ($i->foodItem->item_type ?? null) !== 'beverage'));
            $beverageItems   = $aggregateBottleItems($bottleItems->filter(fn($i) => ($i->foodItem->item_type ?? null) === 'beverage'));

            // --------------------------
            // Card Balance (at bill time)
            // --------------------------
            $billTxnAt = $session->walletTransaction?->created_at ?? $session->updated_at ?? now();

            $openingBalance = (float) ($session->opening_wallet_balance ?? 0);

            // "Last Topup" as requested = total topups during this session window
            $topupFromAt = $session->topup_from_at ?? $session->created_at;

            $topupDuringSession = (float) WalletTransaction::where('member_id', $session->member_id)
                ->where('direction', 'credit')
                ->where('txn_type', 'recharge')
                ->whereBetween('created_at', [$topupFromAt, $billTxnAt])
                ->sum('amount');

            $billedAmount   = (float) ($session->net_amount ?? 0);
            $openingPlusTop = $openingBalance + $topupDuringSession;
            $closingBalance = $openingPlusTop - $billedAmount;

            $cardBalanceInfo = [
                'opening_balance'   => $openingBalance,
                'last_topup'        => $topupDuringSession,
                'opening_plus_topup'=> $openingPlusTop,
                'billed_amount'     => $billedAmount,
                'closing_balance'   => $closingBalance,
            ];

            // --------------------------
            // Minimum Usage Info
            // --------------------------
            // $billDate = Carbon::parse($billTxnAt);
            // [$fyStartDate, $fyEndDate] = financialYearRange($billDate);
            // $fyStart = Carbon::parse($fyStartDate)->startOfDay();
            // $fyEnd   = Carbon::parse($fyEndDate)->endOfDay();

            // $asOfDate = $billDate->toDateString();

            // $activePurchase = MembershipPurchaseHistory::where('club_id', $session->club_id)
            //     ->where('member_id', $session->member_id)
            //     ->where('status', 'active')
            //     ->whereDate('start_date', '<=', $asOfDate)
            //     ->where(function ($q) use ($asOfDate) {
            //         $q->whereNull('expiry_date')
            //             ->orWhereDate('expiry_date', '>=', $asOfDate);
            //     })
            //     ->with('membershipPlanType:id,is_minimum_spend_applicable')
            //     ->latest('start_date')
            //     ->first();

            // $isMinimumSpendApplicable = (bool) ($activePurchase?->membershipPlanType?->is_minimum_spend_applicable ?? false);

            $minimumUsageInfo = [
                // 'applicable'      => $isMinimumSpendApplicable,
                // 'minimum_charges' => 0.0,
                // 'used_so_far'     => 0.0,
                // 'balance'         => 0.0,
                'applicable'      => $session->minimum_spend_required > 0,
                'minimum_charges' => (float) $session->minimum_spend_required,
                'used_so_far'     => (float) $session->total_spend,
                'balance'         => max(0, round($session->minimum_spend_required - $session->total_spend, 2)),
            ];

            // if ($isMinimumSpendApplicable) {
            //     // Canonical source: member_financial_summaries (same module pipeline as observer/year-end).
            //     $fy = FinancialYear::where('club_id', $session->club_id)
            //         ->whereDate('start_date', '<=', $asOfDate)
            //         ->whereDate('end_date', '>=', $asOfDate)
            //         ->first();

            //     $summary = $fy
            //         ? MemberFinancialSummary::where('club_id', $session->club_id)
            //             ->where('member_id', $session->member_id)
            //             ->where('financial_year_id', $fy->id)
            //             ->first()
            //         : null;

            //     if ($summary) {
            //         $minimumCharges = (float) ($summary->minimum_spend_required ?? 0);
            //         $usedSoFar      = (float) ($summary->total_spend ?? 0);
            //     } else {
            //         // Fallback when summary is not initialized yet: mirror observer logic.
            //         $rule = MinimumSpendRule::where('club_id', $session->club_id)->first();
            //         $annualMin = (float) ($rule->minimum_amount ?? 0);
            //         $monthlyMin = $annualMin / 12;

            //         $firstPurchase = MembershipPurchaseHistory::where('member_id', $session->member_id)
            //             ->orderBy('start_date')
            //             ->first();
            //         $joinDate = $firstPurchase
            //             ? Carbon::parse($firstPurchase->start_date)->startOfMonth()
            //             : Carbon::parse($session->member->created_at)->startOfMonth();
            //         $effectiveStart = $joinDate->gt($fyStart) ? $joinDate : $fyStart->copy();
            //         $months = (int) $effectiveStart->diffInMonths($fyEnd->copy()->addDay());
            //         $minimumCharges = round($monthlyMin * $months, 2);

            //         // Keep spend types identical to WalletTransactionObserver.
            //         $spendTypes = [
            //             'spend',
            //             'add_on_purchase',
            //             'locker_purchase',
            //             'Food and Liquor Order',
            //             'Bar Order',
            //             'Restaurant Food Order',
            //         ];

            //         $usedSoFar = (float) WalletTransaction::where('member_id', $session->member_id)
            //             ->where('direction', 'debit')
            //             ->whereIn('txn_type', $spendTypes)
            //             ->whereBetween('created_at', [$fyStart, $fyEnd])
            //             ->where('created_at', '<=', $billTxnAt)
            //             ->sum('amount');
            //     }

            //     $minimumUsageInfo['minimum_charges'] = $minimumCharges;
            //     $minimumUsageInfo['used_so_far']     = $usedSoFar;
            //     $minimumUsageInfo['balance']         = max(0, round($minimumCharges - $usedSoFar, 2));
            // }

            $pdf = Pdf::loadView('order_sessions.invoice', compact(
                'session',
                'foodItems',
                'liquorItems',
                'beverageItems',
                'cardBalanceInfo',
                'minimumUsageInfo'
            ))
                ->setPaper('a4', 'portrait');

            $filename = 'invoice-' . str_replace('/', '-', $session->session_no) . '.pdf';

            return $pdf->download($filename);
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function restoreStock(RestaurantOrder $order, int $clubId): void
    {
        $liquorItems = $order->items->whereIn('unit', ['ml', 'btl']);
        if ($liquorItems->isEmpty()) return;

        $warehouse   = $this->getWarehouse($clubId);
        $barLocation = $this->getBarLocation();

        foreach ($liquorItems as $item) {
            $isBeer     = $item->unit === 'btl';
            $volumeMl   = $item->metadata['volume_ml'] ?? null;
            $restoreQty = $isBeer
                ? (int) $item->quantity
                : (int) $item->quantity * (int) $volumeMl;

            if ($restoreQty <= 0) continue;

            $stock = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                ->where('location_id', $barLocation->id)
                ->where('food_items_id', $item->food_item_id)
                ->first();

            if ($stock) {
                $stock->increment('quantity', $restoreQty);
            } else {
                FoodItemCurrentStock::create([
                    'warehouse_id'  => $warehouse->id,
                    'location_id'   => $barLocation->id,
                    'food_items_id' => $item->food_item_id,
                    'quantity'      => $restoreQty,
                ]);
            }

            StockLedger::create([
                'warehouse_id'   => $warehouse->id,
                'location_id'    => $barLocation->id,
                'food_items_id'  => $item->food_item_id,
                'movement_type'  => 'adjustment',
                'direction'      => 'in',
                'quantity'       => $restoreQty,
                'reference_type' => 'order',
            ]);

            // Restore the mixer/soda deducted alongside this cocktail, if any.
            $secondaryItemId = $item->metadata['secondary_food_item_id'] ?? null;
            $secondaryQty    = $item->metadata['secondary_quantity'] ?? null;
            if ($secondaryItemId && $secondaryQty) {
                $secondaryStock = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                    ->where('location_id', $barLocation->id)
                    ->where('food_items_id', $secondaryItemId)
                    ->first();

                if ($secondaryStock) {
                    $secondaryStock->increment('quantity', (int) $secondaryQty);
                } else {
                    FoodItemCurrentStock::create([
                        'warehouse_id'  => $warehouse->id,
                        'location_id'   => $barLocation->id,
                        'food_items_id' => $secondaryItemId,
                        'quantity'      => (int) $secondaryQty,
                    ]);
                }

                StockLedger::create([
                    'warehouse_id'   => $warehouse->id,
                    'location_id'    => $barLocation->id,
                    'food_items_id'  => $secondaryItemId,
                    'movement_type'  => 'adjustment',
                    'direction'      => 'in',
                    'quantity'       => (int) $secondaryQty,
                    'reference_type' => 'order',
                ]);
            }
        }
    }

    private function getWarehouse(int $clubId): StockWarehouse
    {
        return StockWarehouse::firstOrCreate(
            ['club_id' => $clubId],
            ['stock_name' => 'Main Godown']
        );
    }

    private function getBarLocation(): Location
    {
        return Location::where('name', Location::BAR)->firstOrFail();
    }

    private function resolveOrderPersonName(Member $member, string $holderType): string
    {
        if ($holderType === 'spouse') {
            $spouseName = $member->memberDetails?->details['spouse_name'] ?? null;
            if ($spouseName) {
                // return Str::title($spouseName);
                return $spouseName;
            }
        }

        return $member->name;
    }
}
