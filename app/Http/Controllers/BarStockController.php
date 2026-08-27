<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\FoodItem;
use App\Models\FoodItemCurrentStock;
use App\Models\Location;
use App\Models\StockLedger;
use App\Models\StockWarehouse;
use App\Models\User;
use App\Notifications\ApprovalNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class BarStockController extends Controller
{
    private function getOrCreateWarehouse(int $club_id): StockWarehouse
    {
        return StockWarehouse::firstOrCreate(
            ['club_id' => $club_id],
            ['stock_name' => 'Main Godown']
        );
    }

    private function getGodownLocation(): Location
    {
        return Location::where('name', Location::GODOWN)->firstOrFail();
    }

    private function getBarLocation(): Location
    {
        return Location::where('name', Location::BAR)->firstOrFail();
    }

    // Reconstructs what stock was at the end of a given date, from ledger history.
    // Needed for backdated adjustments: comparing a staff-observed historical
    // physical count against LIVE current stock (instead of the stock as it stood
    // on that date) would compute a wildly wrong delta whenever other movements
    // happened between that date and now, corrupting both the historical record
    // and — since the delta is always applied to live stock — today's real stock too.
    private function reconstructStockAsOf(int $warehouseId, int $locationId, int $foodItemId, Carbon $asOfDate): int
    {
        $totalIn = (int) StockLedger::where('warehouse_id', $warehouseId)
            ->where('location_id', $locationId)
            ->where('food_items_id', $foodItemId)
            ->where('created_at', '<=', $asOfDate)
            ->where('direction', 'in')
            ->sum('quantity');

        $totalOut = (int) StockLedger::where('warehouse_id', $warehouseId)
            ->where('location_id', $locationId)
            ->where('food_items_id', $foodItemId)
            ->where('created_at', '<=', $asOfDate)
            ->where('direction', 'out')
            ->sum('quantity');

        return max(0, $totalIn - $totalOut);
    }

    public function index()
    {
        try {
            $page_title = 'Bar Stock Management';
            $title      = 'Bar Stock List';
            $club_id    = auth()->user()->club_id;

            $warehouse      = $this->getOrCreateWarehouse($club_id);
            $barLocation    = $this->getBarLocation();
            $godownLocation = $this->getGodownLocation();

            $liquorItems = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->with(['foodItemCat'])
                ->latest()
                ->get();

            $barStockMap = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                ->where('location_id', $barLocation->id)
                ->get()
                ->keyBy('food_items_id');

            $godownStockMap = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                ->where('location_id', $godownLocation->id)
                ->get()
                ->keyBy('food_items_id');

            $pendingItemIds = ActionApproval::where('club_id', $club_id)
                ->whereIn('module', ['bar_stock_transfer', 'bar_stock_adjustment'])
                ->where('status', 'pending')
                ->pluck('entity_id')
                ->toArray();

            return view('liquor_stock.bar.list', compact(
                'liquorItems', 'barStockMap', 'godownStockMap', 'pendingItemIds',
                'page_title', 'title', 'warehouse'
            ));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function transfer(Request $request)
    {
        try {
            $club_id = auth()->user()->club_id;

            $request->validate([
                'food_items_id' => 'required|exists:food_items,id',
                'bottles'       => 'required|integer|min:1',
                'notes'         => 'nullable|string|max:500',
                'date'          => 'nullable|date|before_or_equal:today',
            ]);

            $warehouse      = $this->getOrCreateWarehouse($club_id);
            $godownLocation = $this->getGodownLocation();
            $barLocation    = $this->getBarLocation();
            $isAdmin        = Auth::user()->hasRole('admin');

            // Optional backdated entry date (e.g. catching up historical transfers
            // from a parallel system) — keeps today's time-of-day so relative
            // ordering against same-day entries stays sane. Defaults to now().
            $entryDate = $request->filled('date')
                ? Carbon::parse($request->date)->setTimeFrom(now())
                : null;

            $foodItem = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->where('id', $request->food_items_id)
                ->firstOrFail();

            $godownStock = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                ->where('location_id', $godownLocation->id)
                ->where('food_items_id', $foodItem->id)
                ->first();

            // Sufficiency must be checked against what godown stock actually was ON
            // THE BACKDATED DATE, not today's live stock — otherwise a transfer that
            // was genuinely impossible on that date (stock arrived only afterward)
            // would be wrongly allowed just because more has arrived since.
            $godownQty = $entryDate
                ? $this->reconstructStockAsOf($warehouse->id, $godownLocation->id, $foodItem->id, $entryDate)
                : ($godownStock ? (int) $godownStock->quantity : 0);

            if ($godownQty < (int) $request->bottles) {
                $asOfLabel = $entryDate ? " as of {$request->date}" : '';
                return response()->json([
                    'statusCode' => 422,
                    'message'    => "Insufficient godown stock{$asOfLabel}. Available: {$godownQty} BTL.",
                ]);
            }

            $isBeer  = (bool) $foodItem->is_beer;
            $barQty  = $isBeer ? (int) $request->bottles : ((int) $request->bottles * ($foodItem->size_ml ?? 1));
            $barUnit = $isBeer ? 'bottle' : 'ml';

            $payload = [
                'warehouse_id'       => $warehouse->id,
                'godown_location_id' => $godownLocation->id,
                'bar_location_id'    => $barLocation->id,
                'food_items_id'      => $foodItem->id,
                'item_name'          => $foodItem->name,
                'bottles'            => (int) $request->bottles,
                'bar_qty'            => $barQty,
                'bar_unit'           => $barUnit,
                'size_ml'            => $foodItem->size_ml,
                'is_beer'            => $isBeer,
                'notes'              => $request->notes,
                'date'               => $entryDate?->toDateTimeString(),
            ];

            if ($isAdmin) {
                DB::beginTransaction();

                $this->executeTransfer(
                    $warehouse->id, $godownLocation->id, $barLocation->id,
                    $foodItem->id, $request->bottles, $barQty, $godownStock, $entryDate
                );

                $approval = ActionApproval::create([
                    'club_id'                 => $club_id,
                    'module'                  => 'bar_stock_transfer',
                    'action_type'             => 'create',
                    'entity_model'            => 'FoodItem',
                    'entity_id'               => $foodItem->id,
                    'maker_user_id'           => Auth::id(),
                    'checker_user_id'         => Auth::id(),
                    'request_payload'         => $payload,
                    'status'                  => 'approved',
                    'approved_or_rejected_at' => now(),
                ]);

                DB::commit();

                $recipients = User::role(['operator', 'admin'])->where('id', '!=', Auth::id())->get();
                Notification::send($recipients, new ApprovalNotification($approval));

                return response()->json(['statusCode' => 200, 'message' => 'Stock transferred to bar successfully.']);
            }

            $approval = ActionApproval::create([
                'club_id'         => $club_id,
                'module'          => 'bar_stock_transfer',
                'action_type'     => 'create',
                'entity_model'    => 'FoodItem',
                'entity_id'       => $foodItem->id,
                'maker_user_id'   => Auth::id(),
                'request_payload' => $payload,
                'status'          => 'pending',
            ]);

            $recipients = User::role(['operator', 'admin'])->where('id', '!=', Auth::id())->get();
            Notification::send($recipients, new ApprovalNotification($approval));

            return response()->json(['statusCode' => 200, 'message' => 'Transfer request submitted for approval.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function executeTransfer(int $warehouseId, int $godownLocationId, int $barLocationId, int $foodItemId, int $bottles, int $barQty, ?FoodItemCurrentStock $godownStock, ?\Carbon\Carbon $entryDate = null): void
    {
        $outLedger = StockLedger::create([
            'warehouse_id'   => $warehouseId,
            'location_id'    => $godownLocationId,
            'food_items_id'  => $foodItemId,
            'movement_type'  => 'transfer',
            'direction'      => 'out',
            'quantity'       => $bottles,
            'reference_type' => 'manual',
        ]);

        if ($entryDate) {
            $outLedger->created_at = $entryDate;
            $outLedger->updated_at = $entryDate;
            $outLedger->save();
        }

        if ($godownStock) {
            $godownStock->decrement('quantity', $bottles);
        }

        $inLedger = StockLedger::create([
            'warehouse_id'   => $warehouseId,
            'location_id'    => $barLocationId,
            'food_items_id'  => $foodItemId,
            'movement_type'  => 'transfer',
            'direction'      => 'in',
            'quantity'       => $barQty,
            'reference_type' => 'manual',
        ]);

        if ($entryDate) {
            $inLedger->created_at = $entryDate;
            $inLedger->updated_at = $entryDate;
            $inLedger->save();
        }

        $barStock = FoodItemCurrentStock::where('warehouse_id', $warehouseId)
            ->where('location_id', $barLocationId)
            ->where('food_items_id', $foodItemId)
            ->first();

        if ($barStock) {
            $barStock->increment('quantity', $barQty);
        } else {
            FoodItemCurrentStock::create([
                'warehouse_id'  => $warehouseId,
                'location_id'   => $barLocationId,
                'food_items_id' => $foodItemId,
                'quantity'      => $barQty,
            ]);
        }
    }

    // Direct physical-count correction for bar stock — needed because "Transfer to
    // Bar" can only ever add stock (pulled from godown); it cannot fix a bar count
    // that's too high, or set an exact known value (e.g. matching a parallel
    // system's stock). Mirrors GodownStockController::adjust(), except the physical
    // count is unit-aware: whole bottles for beer/wine, bottles+ml for spirits
    // (bar stores spirits in ml, since pegs are poured by the ml).
    public function adjust(Request $request)
    {
        try {
            $club_id = auth()->user()->club_id;

            $request->validate([
                'food_items_id'     => 'required|exists:food_items,id',
                'physical_bottles'  => 'required|integer|min:0',
                'physical_ml'       => 'nullable|integer|min:0',
                'reason'            => 'required|string|max:500',
                'date'              => 'nullable|date|before_or_equal:today',
            ]);

            $warehouse   = $this->getOrCreateWarehouse($club_id);
            $barLocation = $this->getBarLocation();
            $isAdmin     = Auth::user()->hasRole('admin');

            $foodItem = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->where('id', $request->food_items_id)
                ->firstOrFail();

            // Check for any pending bar-side request for this item (transfer or
            // adjustment) — avoids two in-flight operations racing on the same item.
            $hasPending = ActionApproval::where('club_id', $club_id)
                ->whereIn('module', ['bar_stock_transfer', 'bar_stock_adjustment'])
                ->where('entity_id', $foodItem->id)
                ->where('status', 'pending')
                ->exists();

            if ($hasPending) {
                return response()->json([
                    'statusCode' => 422,
                    'message'    => 'This item already has a pending bar stock request. Please wait for it to be approved or rejected first.',
                ]);
            }

            $isBeer   = (bool) $foodItem->is_beer;
            $sizeMl   = (int) ($foodItem->size_ml ?? 0);
            $physicalBottles = (int) $request->physical_bottles;
            $physicalMl      = $isBeer ? 0 : (int) ($request->physical_ml ?? 0);

            if (!$isBeer && $sizeMl > 0 && $physicalMl >= $sizeMl) {
                return response()->json([
                    'statusCode' => 422,
                    'message'    => "Partial ml must be less than the bottle size ({$sizeMl} ml).",
                ]);
            }

            $physicalQty = $isBeer ? $physicalBottles : ($physicalBottles * $sizeMl) + $physicalMl;

            // Optional backdated entry date, same as Add Stock / Transfer.
            $entryDate = $request->filled('date')
                ? Carbon::parse($request->date)->setTimeFrom(now())
                : null;

            $currentStock = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                ->where('location_id', $barLocation->id)
                ->where('food_items_id', $foodItem->id)
                ->first();

            // When backdating, the physical count staff observed is being compared
            // against what stock was AS OF that date — not today's live stock (which
            // may already include unrelated movements from after that date).
            $systemQty = $entryDate
                ? $this->reconstructStockAsOf($warehouse->id, $barLocation->id, $foodItem->id, $entryDate)
                : ($currentStock ? (int) $currentStock->quantity : 0);
            $diff      = $physicalQty - $systemQty;

            if ($diff === 0) {
                return response()->json([
                    'statusCode' => 200,
                    'message'    => 'No adjustment needed. Physical count matches system stock.',
                ]);
            }

            $direction = $diff > 0 ? 'in' : 'out';
            $adjQty    = abs($diff);

            $payload = [
                'warehouse_id'   => $warehouse->id,
                'location_id'    => $barLocation->id,
                'food_items_id'  => $foodItem->id,
                'item_name'      => $foodItem->name,
                'system_qty'     => $systemQty,
                'physical_qty'   => $physicalQty,
                'quantity'       => $adjQty,
                'direction'      => $direction,
                'is_beer'        => $isBeer,
                'unit'           => $isBeer ? 'bottle' : 'ml',
                'size_ml'        => $foodItem->size_ml,
                'reason'         => $request->reason,
                'movement_type'  => 'adjustment',
                'reference_type' => 'manual',
                'date'           => $entryDate?->toDateTimeString(),
            ];

            if ($isAdmin) {
                DB::beginTransaction();

                $ledger = StockLedger::create([
                    'warehouse_id'   => $warehouse->id,
                    'location_id'    => $barLocation->id,
                    'food_items_id'  => $foodItem->id,
                    'movement_type'  => 'adjustment',
                    'direction'      => $direction,
                    'quantity'       => $adjQty,
                    'reference_type' => 'manual',
                ]);

                if ($entryDate) {
                    $ledger->created_at = $entryDate;
                    $ledger->updated_at = $entryDate;
                    $ledger->save();
                }

                if ($currentStock) {
                    if ($direction === 'in') {
                        $currentStock->increment('quantity', $adjQty);
                    } else {
                        $currentStock->decrement('quantity', $adjQty);
                    }
                } else {
                    FoodItemCurrentStock::create([
                        'warehouse_id'  => $warehouse->id,
                        'location_id'   => $barLocation->id,
                        'food_items_id' => $foodItem->id,
                        'quantity'      => $adjQty,
                    ]);
                }

                $approval = ActionApproval::create([
                    'club_id'                 => $club_id,
                    'module'                  => 'bar_stock_adjustment',
                    'action_type'             => 'update',
                    'entity_model'            => 'FoodItem',
                    'entity_id'               => $foodItem->id,
                    'maker_user_id'           => Auth::id(),
                    'checker_user_id'         => Auth::id(),
                    'request_payload'         => $payload,
                    'status'                  => 'approved',
                    'approved_or_rejected_at' => now(),
                ]);

                DB::commit();

                $recipients = User::role(['operator', 'admin'])->where('id', '!=', Auth::id())->get();
                Notification::send($recipients, new ApprovalNotification($approval));

                return response()->json(['statusCode' => 200, 'message' => 'Bar stock adjusted successfully.']);
            }

            $approval = ActionApproval::create([
                'club_id'         => $club_id,
                'module'          => 'bar_stock_adjustment',
                'action_type'     => 'update',
                'entity_model'    => 'FoodItem',
                'entity_id'       => $foodItem->id,
                'maker_user_id'   => Auth::id(),
                'request_payload' => $payload,
                'status'          => 'pending',
            ]);

            $recipients = User::role(['operator', 'admin'])->where('id', '!=', Auth::id())->get();
            Notification::send($recipients, new ApprovalNotification($approval));

            return response()->json(['statusCode' => 200, 'message' => 'Bar stock adjustment submitted for approval.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    // Used by the Physical Stock Count modal: returns the correct baseline stock
    // for a given item — live current stock if no date is picked, or the
    // reconstructed stock as of that date if one is. Keeps the "System Stock"
    // shown in the UI in sync with what adjust() will actually compare against.
    public function stockAsOf(Request $request)
    {
        try {
            $club_id = auth()->user()->club_id;

            $request->validate([
                'food_items_id' => 'required|exists:food_items,id',
                'date'          => 'nullable|date|before_or_equal:today',
            ]);

            $warehouse   = $this->getOrCreateWarehouse($club_id);
            $barLocation = $this->getBarLocation();

            $foodItem = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->where('id', $request->food_items_id)
                ->firstOrFail();

            if ($request->filled('date')) {
                $asOfDate = Carbon::parse($request->date)->setTimeFrom(now());
                $qty = $this->reconstructStockAsOf($warehouse->id, $barLocation->id, $foodItem->id, $asOfDate);
            } else {
                $stock = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                    ->where('location_id', $barLocation->id)
                    ->where('food_items_id', $foodItem->id)
                    ->first();
                $qty = $stock ? (int) $stock->quantity : 0;
            }

            return response()->json(['statusCode' => 200, 'quantity' => $qty]);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    // ─── Report ───────────────────────────────────────────────────────────────

    private function getBarReportData(Request $request): array
    {
        $club_id     = auth()->user()->club_id;
        $warehouse   = $this->getOrCreateWarehouse($club_id);
        $barLocation = $this->getBarLocation();

        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : Carbon::today()->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : Carbon::today()->endOfDay();

        $liquorItems = FoodItem::where('club_id', $club_id)
            ->where('item_type', 'liquor')
            ->with(['foodItemCat'])
            ->get();

        $godownLocation = $this->getGodownLocation();

        // ── Godown WAC (Weighted Average Cost) per item ───────────────────────
        // Bar stock comes from godown, so its cost = godown purchase WAC
        $wacRows = StockLedger::where('warehouse_id', $warehouse->id)
            ->where('location_id', $godownLocation->id)
            ->where('direction', 'in')
            ->where('movement_type', 'purchase')
            ->whereNotNull('unit_price')
            ->where('created_at', '<=', $to)
            ->select(
                'food_items_id',
                DB::raw('SUM(quantity * unit_price) as total_cost'),
                DB::raw('SUM(quantity) as total_qty')
            )
            ->groupBy('food_items_id')
            ->get()
            ->keyBy('food_items_id');

        // WAC per bottle (0 if no godown purchase data yet)
        $wacPerBottle = $wacRows->mapWithKeys(fn($r) => [
            $r->food_items_id => $r->total_qty > 0
                ? round($r->total_cost / $r->total_qty, 2)
                : 0,
        ]);

        // ── Current bar stock map — always the ground truth for closing balance ──
        $currentBarStockMap = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
            ->where('location_id', $barLocation->id)
            ->pluck('quantity', 'food_items_id');

        // ── Qty movements during period ─────────────────────────────────────────
        // IN during period — all direction=in movements (transfers from godown,
        // plus any stock adjustments that added stock). Not filtered to
        // movement_type='transfer' — an adjustment-in would otherwise be invisible
        // in this column and would also break opening/closing day-to-day
        // continuity (opening of the next day must equal closing of this one).
        $inDuring = StockLedger::where('warehouse_id', $warehouse->id)
            ->where('location_id', $barLocation->id)
            ->whereBetween('created_at', [$from, $to])
            ->where('direction', 'in')
            ->select('food_items_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('food_items_id')
            ->pluck('total', 'food_items_id');

        // OUT during period — all direction=out movements (sales, plus any stock
        // adjustments that reduced stock). Same reasoning as above.
        $outDuring = StockLedger::where('warehouse_id', $warehouse->id)
            ->where('location_id', $barLocation->id)
            ->whereBetween('created_at', [$from, $to])
            ->where('direction', 'out')
            ->select('food_items_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('food_items_id')
            ->pluck('total', 'food_items_id');

        // A report ending "now" or later (i.e. covering today) can always trust the
        // live current-stock table for closing — it's the ground truth regardless of
        // ledger completeness. A report for a fully past date range has no "now" to
        // read from, so closing there must be reconstructed from ledger history up
        // to that date instead (needed once backdated entries are used to catch up
        // historical data from a parallel system).
        $useLiveClosing = $to->isToday() || $to->isFuture();

        $closingInUpToTo  = collect();
        $closingOutUpToTo = collect();
        if (!$useLiveClosing) {
            $closingInUpToTo = StockLedger::where('warehouse_id', $warehouse->id)
                ->where('location_id', $barLocation->id)
                ->where('created_at', '<=', $to)
                ->where('direction', 'in')
                ->select('food_items_id', DB::raw('SUM(quantity) as total'))
                ->groupBy('food_items_id')
                ->pluck('total', 'food_items_id');

            $closingOutUpToTo = StockLedger::where('warehouse_id', $warehouse->id)
                ->where('location_id', $barLocation->id)
                ->where('created_at', '<=', $to)
                ->where('direction', 'out')
                ->select('food_items_id', DB::raw('SUM(quantity) as total'))
                ->groupBy('food_items_id')
                ->pluck('total', 'food_items_id');
        }

        $reportData = $liquorItems->map(function ($item) use ($inDuring, $outDuring, $wacPerBottle, $currentBarStockMap, $useLiveClosing, $closingInUpToTo, $closingOutUpToTo) {
            $inQty  = (int) ($inDuring[$item->id]  ?? 0);
            $outQty = (int) ($outDuring[$item->id] ?? 0);

            // Closing reflects the true stock as of the report's end date: live
            // current stock for a report covering today, otherwise reconstructed
            // from all ledger movements up to that date. Opening is derived
            // backward from closing using this period's movements.
            $closingQty = $useLiveClosing
                ? (int) ($currentBarStockMap[$item->id] ?? 0)
                : max(0, (int) ($closingInUpToTo[$item->id] ?? 0) - (int) ($closingOutUpToTo[$item->id] ?? 0));
            $openingQty = max(0, $closingQty - $inQty + $outQty);

            $sizeMl    = (int) ($item->size_ml ?? 1);
            $isBeer    = (bool) $item->is_beer;
            $unit      = $isBeer ? 'BTL' : 'ml';
            $toBottles = fn($qty) => $isBeer ? $qty : ($sizeMl > 0 ? round($qty / $sizeMl, 2) : 0);

            // Cost rate per unit:
            // Beer   → WAC per bottle
            // Spirit → WAC per bottle ÷ size_ml = cost per ml
            $wacBtl     = (float) ($wacPerBottle[$item->id] ?? 0);
            $costPerUnit = $isBeer
                ? $wacBtl
                : ($sizeMl > 0 ? round($wacBtl / $sizeMl, 6) : 0);

            return [
                'item'           => $item,
                'unit'           => $unit,
                'is_beer'        => $isBeer,
                'size_ml'        => $sizeMl,
                'opening_qty'    => $openingQty,
                'in_qty'         => $inQty,
                'out_qty'        => $outQty,
                'closing_qty'    => $closingQty,
                'opening_btl'    => $toBottles($openingQty),
                'in_btl'         => $toBottles($inQty),
                'out_btl'        => $toBottles($outQty),
                'closing_btl'    => $toBottles($closingQty),
                'opening_amount' => round($openingQty * $costPerUnit, 2),
                'in_amount'      => round($inQty      * $costPerUnit, 2),
                'out_amount'     => round($outQty     * $costPerUnit, 2),
                'closing_amount' => round($closingQty * $costPerUnit, 2),
            ];
        });

        return [
            'reportData'          => $reportData,
            'totalOpening'        => $reportData->sum('opening_btl'),
            'totalIn'             => $reportData->sum('in_btl'),
            'totalOut'            => $reportData->sum('out_btl'),
            'totalClosing'        => $reportData->sum('closing_btl'),
            'totalOpeningAmount'  => $reportData->sum('opening_amount'),
            'totalInAmount'       => $reportData->sum('in_amount'),
            'totalOutAmount'      => $reportData->sum('out_amount'),
            'totalClosingAmount'  => $reportData->sum('closing_amount'),
            'from'                => $from,
            'to'                  => $to,
        ];
    }

    public function report(Request $request)
    {
        try {
            $data       = $this->getBarReportData($request);
            $page_title = 'Bar Stock Report';
            $title      = 'Bar Report';

            return view('liquor_stock.bar.report', array_merge($data, compact('page_title', 'title')));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function downloadReport(Request $request)
    {
        try {
            $data     = $this->getBarReportData($request);
            $pdf      = Pdf::loadView('liquor_stock.bar.report_pdf', $data)
                ->setPaper('a4', 'landscape');
            $filename = 'bar-report-' . $data['from']->format('d-m-Y') . '-to-' . $data['to']->format('d-m-Y') . '.pdf';

            return $pdf->download($filename);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
