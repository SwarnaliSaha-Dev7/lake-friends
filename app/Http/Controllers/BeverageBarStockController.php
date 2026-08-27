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

class BeverageBarStockController extends Controller
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
            $page_title = 'Beverage Bar Stock Management';
            $title      = 'Beverage Bar Stock List';
            $club_id    = auth()->user()->club_id;

            $warehouse      = $this->getOrCreateWarehouse($club_id);
            $barLocation    = $this->getBarLocation();
            $godownLocation = $this->getGodownLocation();

            $beverageItems = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'beverage')
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

            return view('beverage_stock.bar.list', compact(
                'beverageItems', 'barStockMap', 'godownStockMap', 'pendingItemIds',
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
                ->where('item_type', 'beverage')
                ->where('id', $request->food_items_id)
                ->firstOrFail();

            $godownStock = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
                ->where('location_id', $godownLocation->id)
                ->where('food_items_id', $foodItem->id)
                ->first();

            $godownQty = $godownStock ? (int) $godownStock->quantity : 0;

            if ($godownQty < (int) $request->bottles) {
                return response()->json([
                    'statusCode' => 422,
                    'message'    => "Insufficient godown stock. Available: {$godownQty} BTL.",
                ]);
            }

            // Beverages are always whole-unit — 1 bottle transferred = 1 bottle at the bar.
            $barQty  = (int) $request->bottles;
            $barUnit = 'bottle';

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
                'is_beer'            => true,
                'notes'              => $request->notes,
                'date'               => $entryDate?->toDateTimeString(),
            ];

            if ($isAdmin) {
                DB::beginTransaction();

                (new BarStockController)->executeTransfer(
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

    // Direct physical-count correction for bar stock — needed because "Transfer to
    // Bar" can only ever add stock (pulled from godown); it cannot fix a bar count
    // that's too high, or set an exact known value (e.g. matching a parallel
    // system's stock). Mirrors BarStockController::adjust(), simplified since
    // beverages are always whole-bottle (no ml split needed).
    public function adjust(Request $request)
    {
        try {
            $club_id = auth()->user()->club_id;

            $request->validate([
                'food_items_id'    => 'required|exists:food_items,id',
                'physical_bottles' => 'required|integer|min:0',
                'reason'           => 'required|string|max:500',
                'date'             => 'nullable|date|before_or_equal:today',
            ]);

            $warehouse   = $this->getOrCreateWarehouse($club_id);
            $barLocation = $this->getBarLocation();
            $isAdmin     = Auth::user()->hasRole('admin');

            $foodItem = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'beverage')
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

            $physicalQty = (int) $request->physical_bottles;

            // Optional backdated entry date, same as Transfer.
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
            $diff = $physicalQty - $systemQty;

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
                'is_beer'        => true,
                'unit'           => 'bottle',
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
                ->where('item_type', 'beverage')
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

        $beverageItems = FoodItem::where('club_id', $club_id)
            ->where('item_type', 'beverage')
            ->with(['foodItemCat'])
            ->get();

        $godownLocation = $this->getGodownLocation();

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

        $wacPerBottle = $wacRows->mapWithKeys(fn($r) => [
            $r->food_items_id => $r->total_qty > 0
                ? round($r->total_cost / $r->total_qty, 2)
                : 0,
        ]);

        $currentBarStockMap = FoodItemCurrentStock::where('warehouse_id', $warehouse->id)
            ->where('location_id', $barLocation->id)
            ->pluck('quantity', 'food_items_id');

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

        $reportData = $beverageItems->map(function ($item) use ($inDuring, $outDuring, $wacPerBottle, $currentBarStockMap, $useLiveClosing, $closingInUpToTo, $closingOutUpToTo) {
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

            $wacBtl = (float) ($wacPerBottle[$item->id] ?? 0);

            return [
                'item'           => $item,
                'unit'           => 'BTL',
                'is_beer'        => true, // beverages are always whole-unit sale, like beer
                'size_ml'        => (int) ($item->size_ml ?? 0),
                'opening_qty'    => $openingQty,
                'in_qty'         => $inQty,
                'out_qty'        => $outQty,
                'closing_qty'    => $closingQty,
                'opening_amount' => round($openingQty * $wacBtl, 2),
                'in_amount'      => round($inQty      * $wacBtl, 2),
                'out_amount'     => round($outQty     * $wacBtl, 2),
                'closing_amount' => round($closingQty * $wacBtl, 2),
            ];
        });

        return [
            'reportData'          => $reportData,
            'totalOpening'        => $reportData->sum('opening_qty'),
            'totalIn'             => $reportData->sum('in_qty'),
            'totalOut'            => $reportData->sum('out_qty'),
            'totalClosing'        => $reportData->sum('closing_qty'),
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
            $page_title = 'Beverage Bar Stock Report';
            $title      = 'Beverage Bar Report';

            return view('beverage_stock.bar.report', array_merge($data, compact('page_title', 'title')));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function downloadReport(Request $request)
    {
        try {
            $data     = $this->getBarReportData($request);
            $pdf      = Pdf::loadView('beverage_stock.bar.report_pdf', $data)
                ->setPaper('a4', 'landscape');
            $filename = 'beverage-bar-report-' . $data['from']->format('d-m-Y') . '-to-' . $data['to']->format('d-m-Y') . '.pdf';

            return $pdf->download($filename);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
