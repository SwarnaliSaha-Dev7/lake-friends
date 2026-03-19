<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Models\FoodItemCurrentStock;
use App\Models\Location;
use App\Models\Member;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\StockLedger;
use App\Models\StockWarehouse;
use App\Models\Offer;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BarOrderController extends Controller
{
    const AC_HEAD = 'Bar Order';

    private function getWarehouse(int $club_id): StockWarehouse
    {
        return StockWarehouse::firstOrCreate(
            ['club_id' => $club_id],
            ['stock_name' => 'Main Godown']
        );
    }

    private function getBarLocation(): Location
    {
        return Location::where('name', Location::BAR)->firstOrFail();
    }

    // ── List (today) ────────────────────────────────────────────────────────

    public function index()
    {
        try {
            $clubId     = club_id();
            $page_title = 'Bar Orders';
            $title      = 'Bar Orders';

            $orders = RestaurantOrder::where('club_id', $clubId)
                ->whereDate('created_at', now())
                ->where('status', '!=', 'cancelled')
                ->whereHas('items', fn($q) => $q->whereIn('unit', ['ml', 'btl']))
                ->with(['member', 'items.foodItem'])
                ->latest()
                ->get();

            // Bar items with current stock for new order form
            $warehouse   = $this->getWarehouse($clubId);
            $barLocation = $this->getBarLocation();

            $barItems = FoodItem::where('club_id', $clubId)
                ->where('item_type', 'liquor')
                ->where('is_active', 1)
                ->with(['foodItemCat', 'foodItemPrice'])
                ->get();

            $barStockMap = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                ->where('location_id', $barLocation->id)
                ->get()
                ->keyBy('food_items_id');

            // ── Offer map keyed by food_item_id ─────────────────────────────
            $today    = now()->toDateString();
            $offerMap = [];
            Offer::where('club_id', $clubId)
                ->where('status', 'active')
                ->where('start_at', '<=', $today)
                ->where('end_at', '>=', $today)
                ->with(['offerType', 'offerItems'])
                ->get()
                ->each(function ($offer) use (&$offerMap) {
                    foreach ($offer->offerItems as $oi) {
                        if (!isset($offerMap[$oi->food_items_id])) {
                            $offerMap[$oi->food_items_id] = [
                                'offer_name'     => $offer->name,
                                'type_slug'      => $offer->offerType ? $offer->offerType->slug : '',
                                'discount_value' => (float) $offer->discount_value,
                                'buy_qty'        => (int) ($offer->buy_qty ?? 1),
                                'get_qty'        => (int) ($offer->get_qty ?? 1),
                            ];
                        }
                    }
                });

            // ── Stats for summary cards ──────────────────────────────────────
            $allLiquorItems = $orders->flatMap(fn($o) => $o->items->whereIn('unit', ['ml', 'btl']));

            $totalSelling = $allLiquorItems->sum('total_amount');

            $todaySale = $orders->where('status', '!=', 'cancelled')
                ->flatMap(fn($o) => $o->items->whereIn('unit', ['ml', 'btl']))
                ->sum('total_amount');

            $topSellingLiquor = $allLiquorItems
                ->groupBy('food_item_id')
                ->map(fn($rows) => ['name' => $rows->first()->foodItem->name ?? '—', 'total' => $rows->sum('total_amount')])
                ->sortByDesc('total')
                ->first()['name'] ?? '—';

            return view('bar_orders.list', compact(
                'orders', 'barItems', 'barStockMap', 'offerMap',
                'todaySale', 'totalSelling', 'topSellingLiquor',
                'page_title', 'title'
            ));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    // ── History (past days) ─────────────────────────────────────────────────

    public function history(Request $request)
    {
        try {
            $clubId     = club_id();
            $page_title = 'Bar Order History';
            $title      = 'Bar Order History';

            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate   = $request->input('end_date',   now()->toDateString());

            $orders = RestaurantOrder::where('club_id', $clubId)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->where('status', '!=', 'cancelled')
                ->whereHas('items', fn($q) => $q->whereIn('unit', ['ml', 'btl']))
                ->with(['member', 'items.foodItem'])
                ->latest()
                ->get();

            $allLiquorItems   = $orders->flatMap(fn($o) => $o->items->whereIn('unit', ['ml', 'btl']));
            $totalSelling     = $allLiquorItems->sum('total_amount');
            $topSellingLiquor = $allLiquorItems
                ->groupBy('food_item_id')
                ->map(fn($rows) => ['name' => $rows->first()->foodItem->name ?? '—', 'total' => $rows->sum('total_amount')])
                ->sortByDesc('total')
                ->first()['name'] ?? '—';

            return view('bar_orders.history', compact(
                'orders', 'topSellingLiquor', 'totalSelling',
                'startDate', 'endDate', 'page_title', 'title'
            ));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    // ── Show (AJAX) ──────────────────────────────────────────────────────────

    public function show($id)
    {
        try {
            $order = RestaurantOrder::with(['member', 'items.foodItem'])
                ->where('club_id', club_id())
                ->findOrFail($id);

            return response()->json(['statusCode' => 200, 'data' => $order]);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    // ── Get bar items for order (AJAX) ──────────────────────────────────────

    public function getBarItems()
    {
        try {
            $clubId      = club_id();
            $warehouse   = $this->getWarehouse($clubId);
            $barLocation = $this->getBarLocation();

            $barStockMap = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                ->where('location_id', $barLocation->id)
                ->pluck('quantity', 'food_items_id');

            // Build offer map keyed by food_item_id
            $today = now()->toDateString();
            $offerMap = [];
            Offer::where('club_id', $clubId)
                ->where('status', 'active')
                ->where('start_at', '<=', $today)
                ->where('end_at', '>=', $today)
                ->with(['offerType', 'offerItems'])
                ->get()
                ->each(function ($offer) use (&$offerMap) {
                    foreach ($offer->offerItems as $oi) {
                        if (!isset($offerMap[$oi->food_items_id])) {
                            $offerMap[$oi->food_items_id] = [
                                'offer_name'     => $offer->name,
                                'type_slug'      => $offer->offerType ? $offer->offerType->slug : '',
                                'discount_value' => (float) $offer->discount_value,
                                'buy_qty'        => (int) ($offer->buy_qty ?? 1),
                                'get_qty'        => (int) ($offer->get_qty ?? 1),
                            ];
                        }
                    }
                });

            $items = FoodItem::where('club_id', $clubId)
                ->where('item_type', 'liquor')
                ->where('is_active', 1)
                ->with(['foodItemCat', 'foodItemPrice'])
                ->get()
                ->map(function ($item) use ($barStockMap, $offerMap) {
                    $stock = (int) ($barStockMap[$item->id] ?? 0);
                    $sizeMl   = (int) ($item->size_ml ?? 1);
                    $alertQty = (int) ($item->low_stock_alert_qty ?? 0);
                    $btlEq    = $item->is_beer ? $stock : ($sizeMl > 0 ? floor($stock / $sizeMl) : 0);
                    $isOut    = $stock === 0;
                    $isLow    = !$isOut && $alertQty > 0 && $btlEq <= $alertQty;

                    return [
                        'id'          => $item->id,
                        'name'        => $item->name,
                        'category'    => $item->foodItemCat->name ?? '—',
                        'is_beer'     => (bool) $item->is_beer,
                        'size_ml'     => (int) ($item->size_ml ?? 0),
                        'price'       => (float) ($item->foodItemPrice->price ?? 0),
                        'bar_stock'   => $stock,
                        'in_stock'    => $stock > 0,
                        'is_low'      => $isLow,
                        'btl_eq'      => $btlEq,
                        'alert_qty'   => $alertQty,
                        'offer'       => $offerMap[$item->id] ?? null,
                    ];
                });

            return response()->json(['statusCode' => 200, 'items' => $items]);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    // ── Place order ──────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $clubId   = club_id();
            $memberId = $request->input('member_id');
            $items    = $request->input('items', []);

            if (empty($items)) {
                return response()->json(['statusCode' => 422, 'message' => 'No items in order.']);
            }

            // Wallet check
            $wallet = Wallet::where('member_id', $memberId)->first();
            if (!$wallet) {
                return response()->json(['statusCode' => 422, 'message' => 'Wallet not found.']);
            }

            $taxable  = (float) $request->input('taxable_amount', 0);
            $gstPct   = (float) $request->input('gst_percentage', 0);
            $gstAmt   = (float) $request->input('gst_amount', 0);
            $netAmt   = (float) $request->input('net_amount', 0);

            if ((float) $wallet->current_balance < $netAmt) {
                return response()->json([
                    'statusCode'      => 422,
                    'message'         => 'Insufficient wallet balance.',
                    'wallet_balance'  => number_format($wallet->current_balance, 2),
                    'required_amount' => number_format($netAmt, 2),
                ]);
            }

            // Stock check & deduction
            $warehouse   = $this->getWarehouse($clubId);
            $barLocation = $this->getBarLocation();

            foreach ($items as $item) {
                $deductQty  = (int) $item['deduct_qty'];
                $foodItemId = (int) $item['food_item_id'];

                $stock = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                    ->where('location_id', $barLocation->id)
                    ->where('food_items_id', $foodItemId)
                    ->first();

                $available = $stock ? (int) $stock->quantity : 0;
                if ($available < $deductQty) {
                    $foodItem = FoodItem::find($foodItemId);
                    $unit     = $item['is_beer'] ? 'BTL' : 'ml';
                    DB::rollBack();
                    return response()->json([
                        'statusCode' => 422,
                        'message'    => "Insufficient bar stock for \"{$foodItem->name}\". Available: {$available} {$unit}.",
                    ]);
                }
            }

            // Generate order number
            $date      = now()->format('Ymd');
            $lastOrder = RestaurantOrder::where('club_id', $clubId)
                ->where('ac_head', self::AC_HEAD)
                ->whereDate('created_at', now())
                ->latest()
                ->value('order_no');
            $lastNum = $lastOrder ? (int) substr($lastOrder, -6) : 0;
            $orderNo = 'BAR/LP/' . $date . '/' . str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);

            // Create order
            $order = RestaurantOrder::create([
                'club_id'         => $clubId,
                'member_id'       => $memberId,
                'order_no'        => $orderNo,
                'ac_head'         => self::AC_HEAD,
                'taxable_amount'  => $taxable,
                'gst_percentage'  => $gstPct,
                'gst_amount'      => $gstAmt,
                'discount_amount' => 0,
                'net_amount'      => $netAmt,
                'status'          => 'paid',
            ]);

            // Create order items + deduct bar stock
            foreach ($items as $item) {
                $foodItemId = (int) $item['food_item_id'];
                $isBeer     = (bool) $item['is_beer'];
                $deductQty  = (int) $item['deduct_qty'];
                $volumeMl   = $isBeer ? null : (int) $item['volume_ml'];
                $quantity   = (int) $item['quantity'];
                $unit       = $isBeer ? 'btl' : 'ml';

                RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'food_item_id'        => $foodItemId,
                    'quantity'            => $quantity,
                    'unit'                => $unit,
                    'unit_price'          => (float) $item['unit_price'],
                    'total_amount'        => (float) $item['total_amount'],
                    'metadata'            => $volumeMl ? ['volume_ml' => $volumeMl] : null,
                ]);

                // Deduct from bar stock
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

            // Deduct wallet
            $newBalance = (float) $wallet->current_balance - $netAmt;
            $wallet->update(['current_balance' => $newBalance]);

            $walletTxn = WalletTransaction::create([
                'wallet_id'  => $wallet->id,
                'member_id'  => $memberId,
                'amount'     => $netAmt,
                'direction'  => 'debit',
                'txn_type'   => 'spend',
                'created_by' => Auth::id(),
            ]);

            $order->update(['wallet_transactions_id' => $walletTxn->id]);

            DB::commit();

            return response()->json([
                'statusCode'     => 200,
                'message'        => 'Bar order placed successfully!',
                'order_no'       => $orderNo,
                'wallet_balance' => number_format($newBalance, 2),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    // ── Mark served ──────────────────────────────────────────────────────────

    public function markServed($id)
    {
        try {
            $order = RestaurantOrder::where('club_id', club_id())
                ->findOrFail($id);

            if ($order->status !== 'paid' && $order->status !== 'pending') {
                return response()->json(['statusCode' => 422, 'message' => 'Only active orders can be marked as served.']);
            }

            $order->update(['status' => 'delivered']);

            return response()->json(['statusCode' => 200, 'message' => 'Order marked as served.']);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    // ── Cancel ───────────────────────────────────────────────────────────────

    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            $order = RestaurantOrder::where('club_id', club_id())
                ->findOrFail($id);

            if ($order->status === 'delivered') {
                return response()->json(['statusCode' => 422, 'message' => 'Served orders cannot be cancelled.']);
            }
            if ($order->status === 'cancelled') {
                return response()->json(['statusCode' => 422, 'message' => 'Order is already cancelled.']);
            }

            $warehouse   = $this->getWarehouse(club_id());
            $barLocation = $this->getBarLocation();

            // Restore bar stock
            foreach ($order->items as $item) {
                $foodItemId = $item->food_item_id;
                $isBeer     = $item->unit === 'btl';
                $volumeMl   = $item->metadata['volume_ml'] ?? null;
                $deductQty  = $isBeer
                    ? (int) $item->quantity
                    : (int) $item->quantity * (int) $volumeMl;

                $stock = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                    ->where('location_id', $barLocation->id)
                    ->where('food_items_id', $foodItemId)
                    ->first();

                if ($stock) {
                    $stock->increment('quantity', $deductQty);
                } else {
                    FoodItemCurrentStock::create([
                        'warehouse_id'  => $warehouse->id,
                        'location_id'   => $barLocation->id,
                        'food_items_id' => $foodItemId,
                        'quantity'      => $deductQty,
                    ]);
                }

                StockLedger::create([
                    'warehouse_id'   => $warehouse->id,
                    'location_id'    => $barLocation->id,
                    'food_items_id'  => $foodItemId,
                    'movement_type'  => 'adjustment',
                    'direction'      => 'in',
                    'quantity'       => $deductQty,
                    'reference_type' => 'order',
                ]);
            }

            // Refund wallet
            $wallet = Wallet::where('member_id', $order->member_id)->first();
            if ($wallet) {
                $newBalance = (float) $wallet->current_balance + (float) $order->net_amount;
                $wallet->update(['current_balance' => $newBalance]);

                WalletTransaction::create([
                    'wallet_id'  => $wallet->id,
                    'member_id'  => $order->member_id,
                    'amount'     => $order->net_amount,
                    'direction'  => 'credit',
                    'txn_type'   => 'refund',
                    'created_by' => Auth::id(),
                ]);
            }

            $order->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'statusCode'     => 200,
                'message'        => 'Order cancelled. Rs ' . number_format($order->net_amount, 2) . ' refunded to wallet.',
                'refund_amount'  => number_format($order->net_amount, 2),
                'wallet_balance' => $wallet ? number_format($wallet->fresh()->current_balance, 2) : null,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }
}
