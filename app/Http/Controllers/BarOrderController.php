<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Models\FoodItemCurrentStock;
use App\Models\Location;
use App\Models\LiquorServing;
use App\Models\Member;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\StockLedger;
use App\Models\StockWarehouse;
use App\Models\Offer;
use App\Models\GstRate;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
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
                ->with(['member', 'session', 'items.foodItem'])
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
                ->where('status', 'paid')
                ->whereHas('items', fn($q) => $q->whereIn('unit', ['ml', 'btl']))
                ->with(['member', 'session', 'items.foodItem'])
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

    // ── Download history PDF ─────────────────────────────────────────────────

    public function downloadReport(Request $request)
    {
        try {
            $clubId    = club_id();
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate   = $request->input('end_date',   now()->toDateString());

            $orders = RestaurantOrder::where('club_id', $clubId)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->where('status', 'paid')
                ->whereHas('items', fn($q) => $q->whereIn('unit', ['ml', 'btl']))
                ->with(['member', 'session', 'items.foodItem'])
                ->latest()
                ->get();

            $allLiquorItems   = $orders->flatMap(fn($o) => $o->items->whereIn('unit', ['ml', 'btl']));
            $totalSelling     = $allLiquorItems->sum('total_amount');
            $topSellingLiquor = $allLiquorItems
                ->groupBy('food_item_id')
                ->map(fn($rows) => ['name' => $rows->first()->foodItem->name ?? '—', 'total' => $rows->sum('total_amount')])
                ->sortByDesc('total')
                ->first()['name'] ?? '—';

            $pdf = Pdf::loadView('bar_orders.report_pdf', compact(
                'orders', 'startDate', 'endDate', 'totalSelling', 'topSellingLiquor'
            ))->setPaper('a4', 'landscape');

            return $pdf->download('bar_order_report_' . $startDate . '_to_' . $endDate . '.pdf');
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

            // ── Beer: sold whole-bottle, priced straight off the food item ─────
            $regularItems = FoodItem::where('club_id', $clubId)
                ->where('item_type', 'liquor')
                ->where('is_beer', 1)
                ->where('is_active', 1)
                ->with(['foodItemCat', 'foodItemPrice'])
                ->get()
                ->map(function ($item) use ($barStockMap, $offerMap) {
                    $stock    = (int) ($barStockMap[$item->id] ?? 0);
                    $alertQty = (int) ($item->low_stock_alert_qty ?? 0);
                    $btlEq    = $stock;
                    $isOut    = $stock === 0;
                    $isLow    = !$isOut && $alertQty > 0 && $btlEq <= $alertQty;

                    return [
                        'id'          => $item->id,
                        'serving_id'  => null,
                        'name'        => $item->name,
                        'category'    => $item->foodItemCat->name ?? '—',
                        'is_beer'     => true,
                        'is_cocktail' => false,
                        'size_ml'     => (int) ($item->size_ml ?? 0),
                        'price'       => (float) ($item->foodItemPrice->price ?? 0),
                        'bar_stock'   => $stock,
                        'in_stock'    => $stock > 0,
                        'is_low'      => $isLow,
                        'btl_eq'      => $btlEq,
                        'alert_qty'   => $alertQty,
                        'offer'       => $offerMap[$item->id] ?? null,
                        'gst_rate'    => 0, // liquor is GST-free in Bar Order — see BUSINESS_LOGIC.md GST section
                    ];
                });

            // ── Spirit pegs + cocktails: both are Liquor Serving entries with a
            //    fixed volume and a price set on the serving itself — the raw
            //    spirit food item has no sellable price of its own (see
            //    LiquorItemManageController::store(), which always stores 0 for
            //    non-beer items), so pricing must come from here, not the item. ──
            $beverageGstRate = (float) (GstRate::where('club_id', $clubId)->where('gst_type', 'beverage')->value('gst_percentage') ?? 0);

            $servings = LiquorServing::where('club_id', $clubId)
                ->where('is_active', true)
                ->with(['foodItem.foodItemCat', 'secondaryFoodItem.foodItemPrice'])
                ->get()
                ->map(function ($serving) use ($barStockMap, $beverageGstRate) {
                    $baseItemId  = $serving->food_item_id;
                    // Base item can be a spirit (deduct ml) or, for a non-alcoholic
                    // drink like "Masala Coke"/"Fresh Lime Soda", a beverage item
                    // (deduct whole bottles) — is_beer on the base item decides which.
                    $baseIsBeer  = (bool) ($serving->foodItem->is_beer ?? false);
                    $stockMl     = (int) ($barStockMap[$baseItemId] ?? 0);
                    $deductMl    = (int) ($serving->volume_ml ?? 1);
                    $canMake     = $deductMl > 0 ? (int) floor($stockMl / $deductMl) : 0;

                    // Optional mixer/soda — its stock also caps how many servings can
                    // be poured, even if the base item itself has plenty left.
                    $secondaryId    = $serving->secondary_food_item_id;
                    $secondaryQty   = (int) ($serving->secondary_quantity ?? 0);
                    $secondaryStock = $secondaryId ? (int) ($barStockMap[$secondaryId] ?? 0) : null;
                    if ($secondaryId && $secondaryQty > 0) {
                        $canMake = min($canMake, (int) floor($secondaryStock / $secondaryQty));
                    }

                    // A "cocktail" is only really a cocktail with an alcoholic (liquor)
                    // base — one built on a beverage base (Masala Coke, Virgin Mojito
                    // etc.) is non-alcoholic and should read as a Mocktail instead.
                    $isMocktail = $serving->is_cocktail && (($serving->foodItem->item_type ?? null) === 'beverage');

                    // The mixer's own catalog price is charged on top at order time
                    // (see store()) — shown here too so the cart/preview total the
                    // staff sees before confirming already matches what gets charged,
                    // without ever naming the mixer itself.
                    $secondaryUnitPrice = $secondaryId ? (float) ($serving->secondaryFoodItem->foodItemPrice->price ?? 0) : 0;
                    $displayPrice       = (float) $serving->price + ($secondaryUnitPrice * $secondaryQty);

                    return [
                        'id'          => $baseItemId,   // base item food_item_id, for stock deduction
                        'serving_id'  => $serving->id,  // unique key per serving (peg size or cocktail)
                        'name'        => $serving->name,
                        'category'    => $isMocktail ? 'Mocktail' : ($serving->is_cocktail ? 'Cocktail' : ($serving->foodItem->foodItemCat->name ?? '—')),
                        'is_beer'     => $baseIsBeer,
                        'is_cocktail' => (bool) $serving->is_cocktail,
                        'is_mocktail' => $isMocktail,
                        'size_ml'     => $deductMl,      // ml deducted per serving (or bottle count if base is a beverage)
                        'price'       => round($displayPrice, 2),
                        'bar_stock'   => $stockMl,
                        'in_stock'    => $canMake > 0,
                        'is_low'      => false,
                        'btl_eq'      => $canMake,       // how many servings can be poured
                        'alert_qty'   => 0,
                        'offer'       => null,
                        // Liquor-based servings are GST-free (see BUSINESS_LOGIC.md);
                        // a beverage-based mocktail/soft-drink still carries beverage GST.
                        'gst_rate'    => $baseIsBeer ? $beverageGstRate : 0,
                        'secondary_item_id'   => $secondaryId,
                        'secondary_item_name' => $serving->secondaryFoodItem->name ?? null,
                        'secondary_quantity'  => $secondaryQty ?: null,
                        'secondary_stock'     => $secondaryStock,
                    ];
                });

            $beverageItems = FoodItem::where('club_id', $clubId)
                ->where('item_type', 'beverage')
                ->where('is_active', 1)
                ->with(['foodItemCat', 'foodItemPrice'])
                ->get()
                ->map(function ($item) use ($barStockMap, $offerMap, $beverageGstRate) {
                    $stock    = (int) ($barStockMap[$item->id] ?? 0);
                    $alertQty = (int) ($item->low_stock_alert_qty ?? 0);
                    $isOut    = $stock === 0;
                    $isLow    = !$isOut && $alertQty > 0 && $stock <= $alertQty;

                    return [
                        'id'          => $item->id,
                        'serving_id'  => null,
                        'name'        => $item->name,
                        'category'    => $item->foodItemCat->name ?? '—',
                        'is_beer'     => true,
                        'is_beverage' => true,
                        'is_cocktail' => false,
                        'size_ml'     => (int) ($item->size_ml ?? 0),
                        'price'       => (float) ($item->foodItemPrice->price ?? 0),
                        'bar_stock'   => $stock,
                        'in_stock'    => $stock > 0,
                        'is_low'      => $isLow,
                        'btl_eq'      => $stock,
                        'alert_qty'   => $alertQty,
                        'offer'       => $offerMap[$item->id] ?? null,
                        'gst_rate'    => $beverageGstRate,
                    ];
                });

            $items = $regularItems->concat($servings)->concat($beverageItems)->values();

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

            // Wallet check (locked to prevent concurrent double-spend)
            $wallet = Wallet::where('member_id', $memberId)->lockForUpdate()->first();
            if (!$wallet) {
                return response()->json(['statusCode' => 422, 'message' => 'Wallet not found.']);
            }

            $taxable     = (float) $request->input('taxable_amount', 0);
            $discountAmt = (float) $request->input('discount_amount', 0);
            $gstAmt      = (float) $request->input('gst_amount', 0);
            $netAmt      = (float) $request->input('net_amount', 0);

            // Stock check & deduction (aggregate per food_item_id first)
            $warehouse   = $this->getWarehouse($clubId);
            $barLocation = $this->getBarLocation();

            // Re-derive every serving-based line (peg or cocktail) from LiquorServing
            // server-side — not trusted from the client. This matters beyond
            // security: a serving's base item can now be a beverage (e.g. "Masala
            // Coke", deducted by the bottle) rather than a spirit (deducted by ml),
            // and getting that unit wrong would silently deduct the wrong quantity.
            // Also resolves the optional mixer/soda, which the client never needs
            // to know about at all — so it can't be omitted to skip the deduction
            // while still charging the cocktail's full (mixer-inclusive) price.
            //
            // The mixer's own catalog price is already folded into the price the
            // client built this line from — see getBarItems(), which now returns
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
                    $item['is_beer']      = $baseIsBeer;
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
                            'per_unit_qty' => (int) $serving->secondary_quantity,
                            'quantity'     => $secondaryTotalQty,
                            'cost'         => $secondaryCost,
                            // A mocktail's base is already a beverage, so getBarItems()
                            // gave it gst_rate=beverageGstRate on its FULL price (mixer
                            // cost included) — already correctly taxed once by the
                            // client. Only a true (spirit-based) cocktail's line was
                            // priced GST-free, silently untaxing its mixer portion —
                            // that's the only case needing a correction here.
                            'needs_gst_correction' => !$baseIsBeer,
                        ];
                    }
                }
            }
            unset($item);

            // The mixer's cost is already part of $taxable (the client's price
            // already included it) — only its GST (at the beverage rate, since a
            // mixer is always a beverage) still needs adding on top, and only for
            // mixers attached to a true cocktail (see needs_gst_correction).
            $secondaryCostSum = array_sum(array_column(
                array_filter($secondaryByIndex, fn($s) => $s['needs_gst_correction']),
                'cost'
            ));
            if ($secondaryCostSum > 0) {
                $beverageGstRate = (float) (GstRate::where('club_id', $clubId)->where('gst_type', 'beverage')->value('gst_percentage') ?? 0);
                $secondaryGst    = round($secondaryCostSum * $beverageGstRate / 100, 2);

                $gstAmt += $secondaryGst;
                $netAmt += $secondaryGst;
            }
            $gstPct = $taxable > 0 ? round(($gstAmt / $taxable) * 100, 2) : 0;

            if ((float) $wallet->current_balance < $netAmt) {
                return response()->json([
                    'statusCode'      => 422,
                    'message'         => 'Insufficient wallet balance.',
                    'wallet_balance'  => number_format($wallet->current_balance, 2),
                    'required_amount' => number_format($netAmt, 2),
                ]);
            }

            $deductMap = [];
            $isBeerMap = [];
            foreach ($items as $item) {
                $foodItemId = (int) $item['food_item_id'];
                $deductMap[$foodItemId] = ($deductMap[$foodItemId] ?? 0) + (int) $item['deduct_qty'];
                $isBeerMap[$foodItemId] = (bool) $item['is_beer'];
            }
            foreach ($secondaryByIndex as $secondary) {
                $sid = $secondary['food_item_id'];
                $deductMap[$sid] = ($deductMap[$sid] ?? 0) + $secondary['quantity'];
                $isBeerMap[$sid] = true; // beverages/mixers are always whole-bottle
            }

            foreach ($deductMap as $foodItemId => $totalDeduct) {
                $stock = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                    ->where('location_id', $barLocation->id)
                    ->where('food_items_id', $foodItemId)
                    ->first();

                $available = $stock ? (int) $stock->quantity : 0;
                if ($available < $totalDeduct) {
                    $foodItem  = FoodItem::find($foodItemId);
                    $unitLabel = $isBeerMap[$foodItemId] ? 'BTL' : 'ml';
                    DB::rollBack();
                    return response()->json([
                        'statusCode' => 422,
                        'message'    => "Insufficient bar stock for \"{$foodItem->name}\". Available: {$available} {$unitLabel}, Required: {$totalDeduct} {$unitLabel}.",
                    ]);
                }
            }

            // Generate order number
            $orderNo = generateOrderNo();

            // Create order
            $order = RestaurantOrder::create([
                'club_id'         => $clubId,
                'member_id'       => $memberId,
                'order_no'        => $orderNo,
                'ac_head'         => self::AC_HEAD,
                'taxable_amount'  => $taxable,
                'gst_percentage'  => $gstPct,
                'gst_amount'      => $gstAmt,
                'discount_amount' => $discountAmt,
                'net_amount'      => $netAmt,
                'status'          => 'paid',
            ]);

            // Create order items + deduct bar stock
            foreach ($items as $idx => $item) {
                $foodItemId   = (int) $item['food_item_id'];
                $isBeer       = (bool) $item['is_beer'];
                $isCocktail   = (bool) ($item['is_cocktail'] ?? false);
                $deductQty    = (int) $item['deduct_qty'];
                $volumeMl     = $isBeer ? null : (int) $item['volume_ml'];
                $quantity     = (int) $item['quantity'];
                $unit         = $isBeer ? 'btl' : 'ml';
                $offerApplied = $item['offer_applied'] ?? null;
                $secondary    = $secondaryByIndex[$idx] ?? null;

                $metadata = null;
                if ($isCocktail) {
                    $metadata = [
                        'volume_ml'     => $volumeMl,
                        'is_cocktail'   => true,
                        'cocktail_name' => $item['name'] ?? '',
                        'serving_id'    => $item['serving_id'] ?? null,
                    ];
                    // Mixer/soda deducted alongside the base spirit — never its own
                    // bill line, so it's recorded here only for stock-reversal on
                    // cancel, not shown anywhere on the receipt.
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
                    'food_item_id'        => $foodItemId,
                    'quantity'            => $quantity,
                    'unit'                => $unit,
                    'unit_price'          => (float) $item['unit_price'],
                    'total_amount'        => (float) $item['total_amount'],
                    'offer_applied'       => $offerApplied,
                    'metadata'            => $metadata,
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

                // Deduct the mixer/soda separately — its own stock row and ledger
                // entry (for accurate reporting), but no separate RestaurantOrderItem
                // row, so it never appears as its own line on the bill.
                if ($secondary) {
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

            // Deduct wallet
            $newBalance = (float) $wallet->current_balance - $netAmt;
            $wallet->update(['current_balance' => $newBalance]);

            $walletTxn = WalletTransaction::create([
                'wallet_id'  => $wallet->id,
                'member_id'  => $memberId,
                'amount'     => $netAmt,
                'direction'  => 'debit',
                'txn_type'   => 'Bar Order',
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

                // Restore the mixer/soda that was deducted alongside this cocktail,
                // if any (never its own bill line, but its own stock/ledger entry).
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
