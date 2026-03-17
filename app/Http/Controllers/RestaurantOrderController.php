<?php

namespace App\Http\Controllers;

use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestaurantOrderController extends Controller
{
    public function history()
    {
        try {
            $clubId     = club_id();
            $page_title = 'Order History';
            $title      = 'Order History';

            $orders = RestaurantOrder::where('club_id', $clubId)
                ->whereDate('created_at', '!=', now()->toDateString())
                ->with(['member', 'items.foodItem'])
                ->latest()
                ->get();

            return view('restaurant_orders.history', compact('title', 'page_title', 'orders'));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function index()
    {
        try {
            $clubId     = club_id();
            $page_title = 'Current Order List';
            $title      = 'Current Order List';

            $orders = RestaurantOrder::where('club_id', $clubId)
                ->whereDate('created_at', now())
                ->with(['member', 'items.foodItem'])
                ->latest()
                ->get();

            return view('restaurant_orders.list', compact('title', 'page_title', 'orders'));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

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

    public function downloadInvoice($id)
    {
        try {
            $order = RestaurantOrder::with(['member', 'items.foodItem'])
                ->where('club_id', club_id())
                ->findOrFail($id);

            $foodItems   = $order->items->where('unit', 'plate');
            $liquorItems = $order->items->where('unit', 'ml');

            $pdf = Pdf::loadView('restaurant_orders.invoice', compact('order', 'foodItems', 'liquorItems'))
                ->setPaper('a4', 'portrait');

            $filename = 'invoice-' . str_replace('/', '-', $order->order_no) . '.pdf';

            return $pdf->download($filename);
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function cancelOrder($id)
    {
        DB::beginTransaction();
        try {
            $order = RestaurantOrder::where('club_id', club_id())->findOrFail($id);

            if ($order->status === 'delivered') {
                return response()->json(['statusCode' => 422, 'message' => 'Delivered orders cannot be cancelled.']);
            }

            if ($order->status === 'cancelled') {
                return response()->json(['statusCode' => 422, 'message' => 'Order is already cancelled.']);
            }

            // Refund to wallet
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
                    'created_by' => auth()->id(),
                ]);
            }

            $order->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'statusCode'     => 200,
                'message'        => 'Order cancelled and Rs ' . number_format($order->net_amount, 2) . ' refunded to wallet.',
                'refund_amount'  => number_format($order->net_amount, 2),
                'wallet_balance' => $wallet ? number_format($wallet->fresh()->current_balance, 2) : null,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function markDelivered($id)
    {
        try {
            $order = RestaurantOrder::where('club_id', club_id())->findOrFail($id);
            $order->update(['status' => 'delivered']);
            return response()->json(['statusCode' => 200, 'message' => 'Order marked as delivered.']);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $clubId    = club_id();
            $memberId  = $request->input('member_id');
            $netAmount = (float) $request->input('net_amount');
            $items     = $request->input('items', []);

            if (empty($items)) {
                return response()->json(['statusCode' => 422, 'message' => 'No items in order.']);
            }

            // Check wallet balance
            $wallet = Wallet::where('member_id', $memberId)->first();
            if (!$wallet) {
                return response()->json(['statusCode' => 422, 'message' => 'Wallet not found for this member.']);
            }

            if ((float) $wallet->current_balance < $netAmount) {
                return response()->json([
                    'statusCode'      => 422,
                    'message'         => 'Insufficient wallet balance.',
                    'wallet_balance'  => number_format($wallet->current_balance, 2),
                    'required_amount' => number_format($netAmount, 2),
                ]);
            }

            // Generate order number
            $date      = now()->format('Ymd');
            $lastOrder = RestaurantOrder::where('club_id', $clubId)
                ->whereDate('created_at', now())
                ->latest()
                ->value('order_no');
            $lastNum = $lastOrder ? (int) substr($lastOrder, -6) : 0;
            $orderNo = 'ORD/LP/' . $date . '/' . str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);

            // Create restaurant order
            $order = RestaurantOrder::create([
                'club_id'         => $clubId,
                'member_id'       => $memberId,
                'order_no'        => $orderNo,
                'mr_no'           => generateMrNo(),
                'bill_no'         => generateBillNo(),
                'ac_head'         => 'Restaurant Order',
                'taxable_amount'  => $request->input('taxable_amount'),
                'discount_amount' => $request->input('discount_amount'),
                'gst_percentage'  => 10.00,
                'gst_amount'      => $request->input('gst_amount'),
                'net_amount'      => $netAmount,
                'status'          => 'pending',
            ]);

            // Create order items
            foreach ($items as $item) {
                RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'food_item_id'        => $item['food_item_id'],
                    'quantity'            => $item['quantity'],
                    'unit'                => $item['unit'],
                    'unit_price'          => $item['unit_price'],
                    'offer_applied'       => !empty($item['offer_applied']) ? $item['offer_applied'] : null,
                    'total_amount'        => $item['total_amount'],
                ]);
            }

            // Deduct from wallet
            $newBalance = (float) $wallet->current_balance - $netAmount;
            $wallet->update(['current_balance' => $newBalance]);

            // Create wallet transaction
            $walletTxn = WalletTransaction::create([
                'wallet_id'  => $wallet->id,
                'member_id'  => $memberId,
                'amount'     => $netAmount,
                'direction'  => 'debit',
                'txn_type'   => 'spend',
                'created_by' => auth()->id(),
            ]);

            // Link wallet transaction to order and mark paid
            $order->update([
                'wallet_transactions_id' => $walletTxn->id,
                'status'                 => 'paid',
            ]);

            DB::commit();

            return response()->json([
                'statusCode'     => 200,
                'message'        => 'Order placed successfully!',
                'order_no'       => $orderNo,
                'wallet_balance' => number_format($newBalance, 2),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }
}
