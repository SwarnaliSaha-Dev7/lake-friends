<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Models\FoodItemCurrentStock;
use App\Models\Location;
use App\Models\Member;
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

class OrderSessionController extends Controller
{
    public function index()
    {
        try {
            $clubId     = club_id();
            $page_title = 'Order Sessions';
            $title      = 'Order Sessions';

            $sessions = OrderSession::where('club_id', $clubId)
                ->whereDate('created_at', now())
                ->with(['member', 'orders'])
                ->latest()
                ->get();

            $members = Member::where('club_id', $clubId)
                ->where('status', 'active')
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

            $member = Member::where('club_id', $clubId)->findOrFail($memberId);

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
                        'member_code'    => $member->member_code,
                        'wallet_balance' => $wallet ? number_format($wallet->current_balance, 2) : '0.00',
                        'order_count'    => $existing->orders()->whereNotIn('status', ['cancelled'])->count(),
                        'pending_total'  => number_format($existing->orders()->where('status', 'pending')->sum('net_amount'), 2),
                    ],
                ]);
            }

            $date        = now()->format('Ymd');
            $lastSession = OrderSession::where('club_id', $clubId)
                ->whereDate('created_at', now())
                ->latest()
                ->value('session_no');
            $lastNum   = $lastSession ? (int) substr($lastSession, -6) : 0;
            $sessionNo = 'TAB/LP/' . $date . '/' . str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);

            $session = OrderSession::create([
                'club_id'    => $clubId,
                'member_id'  => $memberId,
                'session_no' => $sessionNo,
                'status'     => 'open',
            ]);

            $wallet = Wallet::where('member_id', $memberId)->first();

            return response()->json([
                'statusCode' => 200,
                'message'    => 'Session opened for ' . $member->name,
                'session'    => [
                    'id'             => $session->id,
                    'session_no'     => $session->session_no,
                    'status'         => 'open',
                    'member_id'      => $member->id,
                    'member_name'    => $member->name,
                    'member_code'    => $member->member_code,
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
            $session = OrderSession::with(['member', 'orders.items.foodItem'])
                ->where('club_id', club_id())
                ->findOrFail($id);

            $pendingTotal = $session->orders
                ->where('status', 'pending')
                ->sum('net_amount');

            $wallet = Wallet::where('member_id', $session->member_id)->first();

            return response()->json([
                'statusCode'     => 200,
                'data'           => $session,
                'pending_total'  => number_format($pendingTotal, 2),
                'wallet_balance' => $wallet ? number_format($wallet->current_balance, 2) : '0.00',
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

            $memberId  = $session->member_id;
            $netAmount = (float) $request->input('net_amount');
            $items     = $request->input('items', []);

            if (empty($items)) {
                return response()->json(['statusCode' => 422, 'message' => 'No items in order.']);
            }

            // Cumulative wallet check
            $wallet = Wallet::where('member_id', $memberId)->first();
            if (!$wallet) {
                return response()->json(['statusCode' => 422, 'message' => 'Wallet not found for this member.']);
            }

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

            // Bar stock check for liquor items
            $liquorItems = array_filter($items, fn($i) => in_array($i['unit'] ?? '', ['ml', 'btl']));
            $warehouse   = null;
            $barLocation = null;

            if (!empty($liquorItems)) {
                $warehouse   = $this->getWarehouse($clubId);
                $barLocation = $this->getBarLocation();

                foreach ($liquorItems as $item) {
                    $foodItemId = (int) $item['food_item_id'];
                    $deductQty  = (int) $item['deduct_qty'];

                    $stock     = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                        ->where('location_id', $barLocation->id)
                        ->where('food_items_id', $foodItemId)
                        ->first();
                    $available = $stock ? (int) $stock->quantity : 0;

                    if ($available < $deductQty) {
                        $foodItem = FoodItem::find($foodItemId);
                        $unit     = ($item['unit'] === 'btl') ? 'BTL' : 'ml';
                        DB::rollBack();
                        return response()->json([
                            'statusCode' => 422,
                            'message'    => "Insufficient bar stock for \"{$foodItem->name}\". Available: {$available} {$unit}.",
                        ]);
                    }
                }
            }

            // Generate order number
            $date      = now()->format('Ymd');
            $lastOrder = RestaurantOrder::where('club_id', $clubId)
                ->whereDate('created_at', now())
                ->latest()
                ->value('order_no');
            $lastNum = $lastOrder ? (int) substr($lastOrder, -6) : 0;
            $orderNo = 'ORD/LP/' . $date . '/' . str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);

            // Create order (no wallet deduction, no bill_no/mr_no yet)
            $order = RestaurantOrder::create([
                'club_id'         => $clubId,
                'session_id'      => $session->id,
                'member_id'       => $memberId,
                'order_no'        => $orderNo,
                'ac_head'         => 'Restaurant Order',
                'taxable_amount'  => $request->input('taxable_amount'),
                'discount_amount' => $request->input('discount_amount'),
                'gst_percentage'  => 10.00,
                'gst_amount'      => $request->input('gst_amount'),
                'net_amount'      => $netAmount,
                'status'          => 'pending',
            ]);

            // Create order items + deduct bar stock for liquor
            foreach ($items as $item) {
                $unit     = $item['unit'];
                $isLiquor = in_array($unit, ['ml', 'btl']);
                $volumeMl = ($unit === 'ml' && !empty($item['volume_ml'])) ? (int) $item['volume_ml'] : null;

                RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'food_item_id'        => $item['food_item_id'],
                    'quantity'            => $item['quantity'],
                    'unit'                => $unit,
                    'unit_price'          => $item['unit_price'],
                    'offer_applied'       => !empty($item['offer_applied']) ? $item['offer_applied'] : null,
                    'total_amount'        => $item['total_amount'],
                    'metadata'            => $volumeMl ? ['volume_ml' => $volumeMl] : null,
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

            // Final wallet check
            $wallet = Wallet::where('member_id', $session->member_id)->first();
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
                'txn_type'   => 'spend',
                'created_by' => Auth::id(),
            ]);

            // Mark all pending orders as paid
            foreach ($pendingOrders as $order) {
                $order->update([
                    'wallet_transactions_id' => $walletTxn->id,
                    'mr_no'                  => generateMrNo(),
                    'bill_no'                => generateBillNo(),
                    'status'                 => 'paid',
                ]);
            }

            // Update session totals
            $session->update([
                'status'                 => 'billed',
                'taxable_amount'         => $totalTaxable,
                'discount_amount'        => $totalDiscount,
                'gst_percentage'         => 10.00,
                'gst_amount'             => $totalGst,
                'net_amount'             => $totalNet,
                'bill_no'                => generateBillNo(),
                'mr_no'                  => generateMrNo(),
                'wallet_transactions_id' => $walletTxn->id,
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

            $session->update(['status' => 'cancelled']);

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
            $session = OrderSession::with(['member', 'orders.items.foodItem'])
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

            // Aggregate liquor items (group by food_item_id + unit + volume + offer)
            $liquorItems = $allItems->whereIn('unit', ['ml', 'btl'])
                ->groupBy(fn($i) => $i->food_item_id . '_' . $i->unit . '_' . ($i->metadata['volume_ml'] ?? '') . '_' . ($i->offer_applied ? json_encode($i->offer_applied) : ''))
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

            $pdf = Pdf::loadView('order_sessions.invoice', compact('session', 'foodItems', 'liquorItems'))
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
}
