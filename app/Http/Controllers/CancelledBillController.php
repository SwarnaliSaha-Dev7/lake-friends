<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Models\FoodItemCurrentStock;
use App\Models\Location;
use App\Models\OrderSession;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\StockLedger;
use App\Models\StockWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CancelledBillController extends Controller
{
    public function index()
    {
        $clubId = club_id();
        [$fyStart, $fyEnd] = financialYearRange(now());

        $sessions = OrderSession::with(['member.walletDetails', 'cancelledBy'])
            ->where('club_id', $clubId)
            ->where('status', 'cancelled')
            ->whereBetween('created_at', [$fyStart . ' 00:00:00', $fyEnd . ' 23:59:59'])
            ->latest('id')
            ->get();

        return view('cancelled_bills.index', compact('sessions'));
    }

    public function reorder(Request $request, $id)
    {
        $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.food_item_id' => 'required|integer',
            'items.*.quantity'     => 'required|numeric|min:0.5',
            'items.*.unit'         => 'required|string',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.total_amount' => 'required|numeric|min:0',
            'taxable_amount'       => 'required|numeric|min:0',
            'discount_amount'      => 'required|numeric|min:0',
            'gst_amount'           => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $clubId  = club_id();
            $session = OrderSession::where('club_id', $clubId)
                ->where('status', 'cancelled')
                ->findOrFail($id);

            $items      = $request->input('items');
            $netAmount  = (float) $request->input('taxable_amount')
                        - (float) $request->input('discount_amount')
                        + (float) $request->input('gst_amount');

            $sessionCarbon = $session->created_at;
            $sessionDate   = $sessionCarbon->toDateString();

            // Bar stock check
            $warehouse   = null;
            $barLocation = null;
            foreach ($items as $item) {
                if (!in_array($item['unit'], ['ml', 'btl'])) continue;

                $warehouse   = $warehouse   ?? $this->getWarehouse($clubId);
                $barLocation = $barLocation ?? $this->getBarLocation();

                $available = (int) FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                    ->where('location_id', $barLocation->id)
                    ->where('food_items_id', $item['food_item_id'])
                    ->value('quantity');

                $deductQty = (int) ($item['deduct_qty'] ?? 0);

                if ($available < $deductQty) {
                    $foodItem = FoodItem::find($item['food_item_id']);
                    $unit     = $item['unit'] === 'btl' ? 'BTL' : 'ml';
                    DB::rollBack();
                    return response()->json([
                        'statusCode' => 422,
                        'message'    => "Insufficient bar stock for \"{$foodItem->name}\". Available: {$available} {$unit}.",
                    ]);
                }
            }

            // Create order (backdated to session's original date)
            $orderNo = generateOrderNo($sessionDate);
            $order   = RestaurantOrder::create([
                'club_id'         => $clubId,
                'session_id'      => $session->id,
                'member_id'       => $session->member_id,
                'order_no'        => $orderNo,
                'ac_head'         => 'Restaurant Order',
                'taxable_amount'  => $request->input('taxable_amount'),
                'discount_amount' => $request->input('discount_amount'),
                'gst_percentage'  => 10.00,
                'gst_amount'      => $request->input('gst_amount'),
                'net_amount'      => $netAmount,
                'status'          => 'pending',
            ]);

            // Backdate order
            DB::table('restaurant_orders')->where('id', $order->id)->update([
                'created_at' => $sessionCarbon,
                'updated_at' => $sessionCarbon,
            ]);

            // Create items + deduct bar stock (backdated)
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
                    'created_at' => $sessionCarbon,
                    'updated_at' => $sessionCarbon,
                ]);

                if ($isLiquor) {
                    $deductQty   = (int) ($item['deduct_qty'] ?? 0);
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
                        'created_at' => $sessionCarbon,
                        'updated_at' => $sessionCarbon,
                    ]);
                }
            }

            // Re-open the cancelled session
            $session->update(['status' => 'open']);

            DB::commit();

            return response()->json([
                'statusCode' => 200,
                'message'    => 'Order added. Session is now open — go to Current Order to generate bill.',
                'order_no'   => $orderNo,
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
