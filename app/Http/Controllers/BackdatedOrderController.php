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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BackdatedOrderController extends Controller
{
    public function index()
    {
        $clubId  = club_id();
        $members = Member::where('club_id', $clubId)
            ->where('status', 'active')
            ->with('cardDetails:cards.id,card_no')
            ->orderBy('name')
            ->get(['id', 'name', 'member_code']);

        return view('backdated_orders.index', compact('members'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $clubId    = club_id();
            $memberId  = $request->input('member_id');
            $orderDate = $request->input('order_date');
            $items     = $request->input('items', []);

            if (empty($memberId))  return response()->json(['statusCode' => 422, 'message' => 'Please select a member.']);
            if (empty($orderDate)) return response()->json(['statusCode' => 422, 'message' => 'Order date is required.']);
            if (empty($items))     return response()->json(['statusCode' => 422, 'message' => 'No items added to the order.']);

            $orderCarbon = Carbon::parse($orderDate)->startOfDay();
            if ($orderCarbon->isFuture()) {
                return response()->json(['statusCode' => 422, 'message' => 'Order date cannot be a future date.']);
            }

            $member = Member::where('club_id', $clubId)->findOrFail($memberId);

            $taxableAmount  = (float) $request->input('taxable_amount', 0);
            $discountAmount = (float) $request->input('discount_amount', 0);
            $gstAmount      = (float) $request->input('gst_amount', 0);
            $netAmount      = (float) $request->input('net_amount', 0);

            // Wallet check
            $wallet = Wallet::where('member_id', $memberId)->first();
            if (!$wallet) {
                return response()->json(['statusCode' => 422, 'message' => 'Wallet not found for this member.']);
            }
            if ((float) $wallet->current_balance < $netAmount) {
                return response()->json([
                    'statusCode'   => 422,
                    'insufficient' => true,
                    'message'      => 'Insufficient wallet balance. Available: Rs ' . number_format($wallet->current_balance, 2) . ', Required: Rs ' . number_format($netAmount, 2) . '.',
                ]);
            }

            // Bar stock check
            $liquorItems = array_filter($items, fn($i) => in_array($i['unit'] ?? '', ['ml', 'btl']));
            $warehouse   = null;
            $barLocation = null;
            if (!empty($liquorItems)) {
                $warehouse   = $this->getWarehouse($clubId);
                $barLocation = $this->getBarLocation();
                foreach ($liquorItems as $item) {
                    $foodItemId = (int) $item['food_item_id'];
                    $deductQty  = (int) $item['deduct_qty'];
                    $stock      = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                        ->where('location_id', $barLocation->id)
                        ->where('food_items_id', $foodItemId)->first();
                    $available = $stock ? (int) $stock->quantity : 0;
                    if ($available < $deductQty) {
                        $foodItem = FoodItem::find($foodItemId);
                        DB::rollBack();
                        return response()->json([
                            'statusCode' => 422,
                            'message'    => 'Insufficient bar stock for "' . $foodItem->name . '". Available: ' . $available . '.',
                        ]);
                    }
                }
            }

            $dateStr = $orderCarbon->format('Ymd');

            // Create session
            $lastSession = OrderSession::where('club_id', $clubId)
                ->whereDate('created_at', $orderCarbon->toDateString())
                ->latest('id')->value('session_no');
            $lastNum   = $lastSession ? (int) substr($lastSession, -6) : 0;
            $sessionNo = 'TAB/LP/' . $dateStr . '/' . str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);

            $session = OrderSession::create([
                'club_id'    => $clubId,
                'member_id'  => $memberId,
                'session_no' => $sessionNo,
                'status'     => 'open',
            ]);
            DB::table('order_sessions')->where('id', $session->id)->update([
                'created_at' => $orderCarbon,
                'updated_at' => $orderCarbon,
            ]);

            // Create order
            $lastOrder = RestaurantOrder::where('club_id', $clubId)
                ->whereDate('created_at', $orderCarbon->toDateString())
                ->latest('id')->value('order_no');
            $lastNum = $lastOrder ? (int) substr($lastOrder, -6) : 0;
            $orderNo = 'ORD/LP/' . $dateStr . '/' . str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);

            $order = RestaurantOrder::create([
                'club_id'         => $clubId,
                'session_id'      => $session->id,
                'member_id'       => $memberId,
                'order_no'        => $orderNo,
                'ac_head'         => 'Backdated Order',
                'taxable_amount'  => $taxableAmount,
                'discount_amount' => $discountAmount,
                'gst_percentage'  => 10.00,
                'gst_amount'      => $gstAmount,
                'net_amount'      => $netAmount,
                'status'          => 'pending',
            ]);
            DB::table('restaurant_orders')->where('id', $order->id)->update([
                'created_at' => $orderCarbon,
                'updated_at' => $orderCarbon,
            ]);

            // Create order items + deduct bar stock
            foreach ($items as $item) {
                $unit     = $item['unit'];
                $isLiquor = in_array($unit, ['ml', 'btl']);
                $volumeMl = ($unit === 'ml' && !empty($item['volume_ml'])) ? (int) $item['volume_ml'] : null;

                $orderItem = RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'food_item_id'        => $item['food_item_id'],
                    'quantity'            => $item['quantity'],
                    'unit'                => $unit,
                    'unit_price'          => $item['unit_price'],
                    'offer_applied'       => !empty($item['offer_applied']) ? $item['offer_applied'] : null,
                    'total_amount'        => $item['total_amount'],
                    'metadata'            => $volumeMl ? ['volume_ml' => $volumeMl] : null,
                ]);
                DB::table('restaurant_order_items')->where('id', $orderItem->id)->update([
                    'created_at' => $orderCarbon,
                    'updated_at' => $orderCarbon,
                ]);

                if ($isLiquor) {
                    $deductQty   = (int) $item['deduct_qty'];
                    $foodItemId  = (int) $item['food_item_id'];
                    $warehouse   = $warehouse   ?? $this->getWarehouse($clubId);
                    $barLocation = $barLocation ?? $this->getBarLocation();

                    $stock = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                        ->where('location_id', $barLocation->id)
                        ->where('food_items_id', $foodItemId)->first();
                    if ($stock) $stock->decrement('quantity', $deductQty);

                    $ledger = StockLedger::create([
                        'warehouse_id'   => $warehouse->id,
                        'location_id'    => $barLocation->id,
                        'food_items_id'  => $foodItemId,
                        'movement_type'  => 'sale',
                        'direction'      => 'out',
                        'quantity'       => $deductQty,
                        'reference_type' => 'order',
                    ]);
                    DB::table('stock_ledgers')->where('id', $ledger->id)->update([
                        'created_at' => $orderCarbon,
                        'updated_at' => $orderCarbon,
                    ]);
                }
            }

            // Wallet deduction
            $newBalance = (float) $wallet->current_balance - $netAmount;
            $wallet->update(['current_balance' => $newBalance]);

            $walletTxn = WalletTransaction::create([
                'wallet_id'  => $wallet->id,
                'member_id'  => $memberId,
                'amount'     => $netAmount,
                'direction'  => 'debit',
                'txn_type'   => 'Food and Liquor Order',
                'created_by' => Auth::id(),
            ]);
            DB::table('wallet_transactions')->where('id', $walletTxn->id)->update([
                'created_at' => $orderCarbon,
                'updated_at' => $orderCarbon,
            ]);

            // Finalize order + session
            $mrNo   = generateMrNo($orderCarbon->toDateString());
            $billNo = generateBillNo($orderCarbon->toDateString());

            $order->update([
                'wallet_transactions_id' => $walletTxn->id,
                'mr_no'   => $mrNo,
                'bill_no' => $billNo,
                'status'  => 'paid',
            ]);
            DB::table('restaurant_orders')->where('id', $order->id)->update(['updated_at' => $orderCarbon]);

            DB::table('order_sessions')->where('id', $session->id)->update([
                'status'                 => 'billed',
                'taxable_amount'         => $taxableAmount,
                'discount_amount'        => $discountAmount,
                'gst_percentage'         => 10.00,
                'gst_amount'             => $gstAmount,
                'net_amount'             => $netAmount,
                'bill_no'                => $billNo,
                'mr_no'                  => $mrNo,
                'wallet_transactions_id' => $walletTxn->id,
                'updated_at'             => $orderCarbon,
            ]);

            DB::commit();

            return response()->json([
                'statusCode'     => 200,
                'message'        => 'Backdated order placed successfully.',
                'session_no'     => $sessionNo,
                'order_no'       => $orderNo,
                'net_amount'     => number_format($netAmount, 2),
                'wallet_balance' => number_format($newBalance, 2),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
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
