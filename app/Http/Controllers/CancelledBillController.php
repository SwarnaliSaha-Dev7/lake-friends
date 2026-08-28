<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Models\FoodItemCurrentStock;
use App\Models\GstRate;
use App\Models\Location;
use App\Models\LiquorServing;
use App\Models\OrderSession;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\StockLedger;
use App\Models\StockWarehouse;
use App\Models\Wallet;
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

            $sessionCarbon = $session->created_at;
            $sessionDate   = $sessionCarbon->toDateString();

            // Re-derive every serving-based line (peg or cocktail) from LiquorServing
            // server-side, exactly as BarOrderController/OrderSessionController do —
            // not trusted from the client. Resolves the optional mixer/soda too,
            // which the client never needs to know about, so it can't be omitted to
            // skip the deduction while still charging the cocktail's full
            // (mixer-inclusive) price. The mixer's own catalog price is already
            // folded into the price the client built this line from — see
            // getOrderItems(), which returns a serving's price as base + mixer cost
            // (never naming the mixer). So item totals need no adjustment here; only
            // the GST does, since the client has no way to know part of a "liquor"
            // (GST-free) line's price is actually a beverage mixer that must carry
            // beverage GST.
            $secondaryByIndex = [];
            foreach ($items as $idx => &$item) {
                if (!empty($item['serving_id'])) {
                    $serving = LiquorServing::where('club_id', $clubId)
                        ->with(['foodItem', 'secondaryFoodItem'])
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
                        $secondaryByIndex[$idx] = [
                            'food_item_id' => (int) $serving->secondary_food_item_id,
                            'name'         => $serving->secondaryFoodItem->name ?? null,
                            'quantity'     => $qty * (int) $serving->secondary_quantity,
                            'cost'         => 0, // priced via the client's mixer-inclusive item price, not re-added here
                        ];
                    }
                }
            }
            unset($item);

            // Bar stock check
            $warehouse   = null;
            $barLocation = null;
            $deductMap   = [];
            foreach ($items as $item) {
                if (!in_array($item['unit'], ['ml', 'btl'])) continue;
                $deductMap[(int) $item['food_item_id']] = ($deductMap[(int) $item['food_item_id']] ?? 0) + (int) ($item['deduct_qty'] ?? 0);
            }
            foreach ($secondaryByIndex as $secondary) {
                $sid = $secondary['food_item_id'];
                $deductMap[$sid] = ($deductMap[$sid] ?? 0) + $secondary['quantity'];
            }
            foreach ($deductMap as $foodItemId => $totalDeduct) {
                $warehouse   = $warehouse   ?? $this->getWarehouse($clubId);
                $barLocation = $barLocation ?? $this->getBarLocation();

                $available = (int) FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                    ->where('location_id', $barLocation->id)
                    ->where('food_items_id', $foodItemId)
                    ->value('quantity');

                if ($available < $totalDeduct) {
                    $foodItem = FoodItem::find($foodItemId);
                    DB::rollBack();
                    return response()->json([
                        'statusCode' => 422,
                        'message'    => "Insufficient bar stock for \"{$foodItem->name}\". Available: {$available}, Required: {$totalDeduct}.",
                    ]);
                }
            }

            // The mixer's cost is already part of taxable_amount (the client's price
            // already included it, via getOrderItems()) — only its GST (at the
            // beverage rate, since a mixer is always a beverage) still needs adding.
            $orderTaxable = (float) $request->input('taxable_amount');
            $orderGstAmt  = (float) $request->input('gst_amount');
            $secondaryCostSum = 0;
            foreach ($secondaryByIndex as $idx => $secondary) {
                $servingId = $items[$idx]['serving_id'] ?? null;
                $serving   = $servingId ? LiquorServing::with('secondaryFoodItem.foodItemPrice')->find($servingId) : null;
                $unitPrice = $serving ? (float) ($serving->secondaryFoodItem->foodItemPrice->price ?? 0) : 0;
                $secondaryCostSum += round($unitPrice * $secondary['quantity'], 2);
            }
            if ($secondaryCostSum > 0) {
                $beverageGstRate = (float) (GstRate::where('club_id', $clubId)->where('gst_type', 'beverage')->value('gst_percentage') ?? 0);
                $secondaryGst    = round($secondaryCostSum * $beverageGstRate / 100, 2);
                $orderGstAmt    += $secondaryGst;
            }

            $netAmount = $orderTaxable - (float) $request->input('discount_amount') + $orderGstAmt;

            // Effective GST rate for this order — a food+beverage mix blends two
            // different rates (and liquor contributes none), so this is derived
            // from what was actually charged rather than a single fixed rate.
            $effectiveGst = $orderTaxable > 0 ? round(($orderGstAmt / $orderTaxable) * 100, 2) : 0;

            // Create order (backdated to session's original date)
            $orderNo = generateOrderNo($sessionDate);
            $order   = RestaurantOrder::create([
                'club_id'         => $clubId,
                'session_id'      => $session->id,
                'member_id'       => $session->member_id,
                'order_no'        => $orderNo,
                'ac_head'         => 'Restaurant Order',
                'taxable_amount'  => $orderTaxable,
                'discount_amount' => $request->input('discount_amount'),
                'gst_percentage'  => $effectiveGst,
                'gst_amount'      => $orderGstAmt,
                'net_amount'      => $netAmount,
                'status'          => 'pending',
            ]);

            // Backdate order
            DB::table('restaurant_orders')->where('id', $order->id)->update([
                'created_at' => $sessionCarbon,
                'updated_at' => $sessionCarbon,
            ]);

            // Create items + deduct bar stock (backdated)
            foreach ($items as $idx => $item) {
                $unit       = $item['unit'];
                $isLiquor   = in_array($unit, ['ml', 'btl']);
                $isCocktail = !empty($item['is_cocktail']);
                $volumeMl   = ($unit === 'ml' && !empty($item['volume_ml'])) ? (int) $item['volume_ml'] : null;
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
                    if ($secondary) {
                        $metadata['secondary_food_item_id'] = $secondary['food_item_id'];
                        $metadata['secondary_item_name']    = $secondary['name'];
                        $metadata['secondary_quantity']     = $secondary['quantity'];
                    }
                } elseif ($volumeMl) {
                    $metadata = ['volume_ml' => $volumeMl];
                }

                $orderItem = RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'food_item_id'        => $item['food_item_id'],
                    'quantity'            => $item['quantity'],
                    'unit'                => $unit,
                    'unit_price'          => $item['unit_price'],
                    'offer_applied'       => !empty($item['offer_applied']) ? $item['offer_applied'] : null,
                    'total_amount'        => $item['total_amount'],
                    'metadata'            => $metadata,
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

                    // Deduct the mixer/soda separately — its own stock row and ledger
                    // entry, but no separate RestaurantOrderItem row, so it never
                    // appears as its own line on the bill.
                    if ($secondary) {
                        $secondaryStock = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                            ->where('location_id', $barLocation->id)
                            ->where('food_items_id', $secondary['food_item_id'])
                            ->first();

                        if ($secondaryStock) {
                            $secondaryStock->decrement('quantity', $secondary['quantity']);
                        }

                        $secondaryLedger = StockLedger::create([
                            'warehouse_id'   => $warehouse->id,
                            'location_id'    => $barLocation->id,
                            'food_items_id'  => $secondary['food_item_id'],
                            'movement_type'  => 'sale',
                            'direction'      => 'out',
                            'quantity'       => $secondary['quantity'],
                            'reference_type' => 'order',
                        ]);

                        DB::table('stock_ledgers')->where('id', $secondaryLedger->id)->update([
                            'created_at' => $sessionCarbon,
                            'updated_at' => $sessionCarbon,
                        ]);
                    }
                }
            }

            // Re-open the cancelled session.
            // Financial window must restart from this reopen/edit moment:
            // - opening balance snapshot should be refreshed
            // - topup window should start now
            $walletNow = (float) Wallet::where('member_id', $session->member_id)->value('current_balance');
            $session->update([
                'status'                 => 'open',
                'opening_wallet_balance' => $walletNow,
                'topup_from_at'          => now(),
            ]);

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
