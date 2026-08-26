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

class GodownStockController extends Controller
{
    private function getOrCreateGodown(int $club_id): StockWarehouse
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

    public function index()
    {
        try {
            $page_title = 'Godown Stock Management';
            $title      = 'Godown Stock List';
            $club_id    = auth()->user()->club_id;

            $godown         = $this->getOrCreateGodown($club_id);
            $godownLocation = $this->getGodownLocation();

            $liquorItems = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->with(['foodItemCat'])
                ->latest()
                ->get();

            $stockMap = FoodItemCurrentStock::where('warehouse_id', $godown->id)
                ->where('location_id', $godownLocation->id)
                ->get()
                ->keyBy('food_items_id');

            $pendingItemIds = ActionApproval::where('club_id', $club_id)
                ->where('module', 'stock_adjustment')
                ->where('status', 'pending')
                ->pluck('entity_id')
                ->toArray();

            return view('liquor_stock.godown.list', compact(
                'liquorItems', 'stockMap', 'pendingItemIds',
                'page_title', 'title', 'godown'
            ));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function store(Request $request)
    {
        try {
            $club_id = auth()->user()->club_id;

            $request->validate([
                'food_items_id' => 'required|exists:food_items,id',
                'quantity'      => 'required|integer|min:1',
                'unit_price'    => 'required|numeric|min:0',
                'notes'         => 'nullable|string|max:500',
                'date'          => 'nullable|date|before_or_equal:today',
            ]);

            $godown         = $this->getOrCreateGodown($club_id);
            $godownLocation = $this->getGodownLocation();
            $isAdmin        = Auth::user()->hasRole('admin');

            $foodItem = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->where('id', $request->food_items_id)
                ->firstOrFail();

            // Optional backdated entry date (e.g. catching up historical purchases
            // from a parallel system) — keeps today's time-of-day so relative
            // ordering against same-day entries stays sane. Defaults to now().
            $entryDate = $request->filled('date')
                ? Carbon::parse($request->date)->setTimeFrom(now())
                : null;

            $payload = [
                'warehouse_id'   => $godown->id,
                'location_id'    => $godownLocation->id,
                'food_items_id'  => $foodItem->id,
                'item_name'      => $foodItem->name,
                'quantity'       => (int) $request->quantity,
                'unit_price'     => (float) $request->unit_price,
                'unit'           => 'bottle',
                'size_ml'        => $foodItem->size_ml,
                'notes'          => $request->notes,
                'movement_type'  => 'purchase',
                'direction'      => 'in',
                'reference_type' => 'manual',
                'date'           => $entryDate?->toDateTimeString(),
            ];

            if ($isAdmin) {
                DB::beginTransaction();

                $ledger = StockLedger::create([
                    'warehouse_id'   => $godown->id,
                    'location_id'    => $godownLocation->id,
                    'food_items_id'  => $foodItem->id,
                    'movement_type'  => 'purchase',
                    'direction'      => 'in',
                    'quantity'       => $request->quantity,
                    'unit_price'     => (float) $request->unit_price,
                    'reference_type' => 'manual',
                ]);

                if ($entryDate) {
                    $ledger->created_at = $entryDate;
                    $ledger->updated_at = $entryDate;
                    $ledger->save();
                }

                $currentStock = FoodItemCurrentStock::where('warehouse_id', $godown->id)
                    ->where('location_id', $godownLocation->id)
                    ->where('food_items_id', $foodItem->id)
                    ->first();

                if ($currentStock) {
                    $currentStock->increment('quantity', $request->quantity);
                } else {
                    FoodItemCurrentStock::create([
                        'warehouse_id'  => $godown->id,
                        'location_id'   => $godownLocation->id,
                        'food_items_id' => $foodItem->id,
                        'quantity'      => $request->quantity,
                    ]);
                }

                $approval = ActionApproval::create([
                    'club_id'                 => $club_id,
                    'module'                  => 'stock_adjustment',
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

                return response()->json(['statusCode' => 200, 'message' => 'Stock added successfully.']);
            }

            $approval = ActionApproval::create([
                'club_id'         => $club_id,
                'module'          => 'stock_adjustment',
                'action_type'     => 'create',
                'entity_model'    => 'FoodItem',
                'entity_id'       => $foodItem->id,
                'maker_user_id'   => Auth::id(),
                'request_payload' => $payload,
                'status'          => 'pending',
            ]);

            $recipients = User::role(['operator', 'admin'])->where('id', '!=', Auth::id())->get();
            Notification::send($recipients, new ApprovalNotification($approval));

            return response()->json(['statusCode' => 200, 'message' => 'Stock addition submitted for approval.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function adjust(Request $request)
    {
        try {
            $club_id = auth()->user()->club_id;

            $request->validate([
                'food_items_id'  => 'required|exists:food_items,id',
                'physical_count' => 'required|integer|min:0',
                'reason'         => 'required|string|max:500',
            ]);

            $godown         = $this->getOrCreateGodown($club_id);
            $godownLocation = $this->getGodownLocation();
            $isAdmin        = Auth::user()->hasRole('admin');

            $foodItem = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->where('id', $request->food_items_id)
                ->firstOrFail();

            // Check for pending approval for this item
            $hasPending = ActionApproval::where('club_id', $club_id)
                ->where('module', 'stock_adjustment')
                ->where('entity_id', $foodItem->id)
                ->where('status', 'pending')
                ->exists();

            if ($hasPending) {
                return response()->json([
                    'statusCode' => 422,
                    'message'    => 'This item already has a pending stock request. Please wait for it to be approved or rejected first.',
                ]);
            }

            $currentStock = FoodItemCurrentStock::where('warehouse_id', $godown->id)
                ->where('location_id', $godownLocation->id)
                ->where('food_items_id', $foodItem->id)
                ->first();

            $systemQty   = $currentStock ? (int) $currentStock->quantity : 0;
            $physicalQty = (int) $request->physical_count;
            $diff        = $physicalQty - $systemQty;

            if ($diff === 0) {
                return response()->json([
                    'statusCode' => 200,
                    'message'    => 'No adjustment needed. Physical count matches system stock.',
                ]);
            }

            $direction = $diff > 0 ? 'in' : 'out';
            $adjQty    = abs($diff);

            $payload = [
                'warehouse_id'   => $godown->id,
                'location_id'    => $godownLocation->id,
                'food_items_id'  => $foodItem->id,
                'item_name'      => $foodItem->name,
                'system_qty'     => $systemQty,
                'physical_qty'   => $physicalQty,
                'quantity'       => $adjQty,
                'direction'      => $direction,
                'unit'           => 'bottle',
                'size_ml'        => $foodItem->size_ml,
                'reason'         => $request->reason,
                'movement_type'  => 'adjustment',
                'reference_type' => 'manual',
            ];

            if ($isAdmin) {
                DB::beginTransaction();

                StockLedger::create([
                    'warehouse_id'   => $godown->id,
                    'location_id'    => $godownLocation->id,
                    'food_items_id'  => $foodItem->id,
                    'movement_type'  => 'adjustment',
                    'direction'      => $direction,
                    'quantity'       => $adjQty,
                    'reference_type' => 'manual',
                ]);

                if ($currentStock) {
                    if ($direction === 'in') {
                        $currentStock->increment('quantity', $adjQty);
                    } else {
                        $currentStock->decrement('quantity', $adjQty);
                    }
                } else {
                    // System had 0, physical count is more — create new record
                    FoodItemCurrentStock::create([
                        'warehouse_id'  => $godown->id,
                        'location_id'   => $godownLocation->id,
                        'food_items_id' => $foodItem->id,
                        'quantity'      => $adjQty,
                    ]);
                }

                $approval = ActionApproval::create([
                    'club_id'                 => $club_id,
                    'module'                  => 'stock_adjustment',
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

                return response()->json(['statusCode' => 200, 'message' => 'Stock adjusted successfully.']);
            }

            $approval = ActionApproval::create([
                'club_id'         => $club_id,
                'module'          => 'stock_adjustment',
                'action_type'     => 'update',
                'entity_model'    => 'FoodItem',
                'entity_id'       => $foodItem->id,
                'maker_user_id'   => Auth::id(),
                'request_payload' => $payload,
                'status'          => 'pending',
            ]);

            $recipients = User::role(['operator', 'admin'])->where('id', '!=', Auth::id())->get();
            Notification::send($recipients, new ApprovalNotification($approval));

            return response()->json(['statusCode' => 200, 'message' => 'Stock adjustment submitted for approval.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    private function getReportData(Request $request): array
    {
        $club_id        = auth()->user()->club_id;
        $godown         = $this->getOrCreateGodown($club_id);
        $godownLocation = $this->getGodownLocation();

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

        // ── WAC (Weighted Average Cost) per item ──────────────────────────────
        // All purchase IN entries up to $to with a unit_price — used for cost valuation
        $wacRows = StockLedger::where('warehouse_id', $godown->id)
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

        // WAC per bottle per item (0 if no purchase data yet)
        $wac = $wacRows->mapWithKeys(fn($r) => [
            $r->food_items_id => $r->total_qty > 0
                ? round($r->total_cost / $r->total_qty, 2)
                : 0,
        ]);

        // ── IN amount during period (actual purchase cost per batch) ──────────
        $inAmountDuring = StockLedger::where('warehouse_id', $godown->id)
            ->where('location_id', $godownLocation->id)
            ->where('direction', 'in')
            ->where('movement_type', 'purchase')
            ->whereNotNull('unit_price')
            ->whereBetween('created_at', [$from, $to])
            ->select('food_items_id', DB::raw('SUM(quantity * unit_price) as total'))
            ->groupBy('food_items_id')
            ->pluck('total', 'food_items_id');

        // ── Current stock map — always the ground truth for closing balance ────
        $currentStockMap = FoodItemCurrentStock::where('warehouse_id', $godown->id)
            ->where('location_id', $godownLocation->id)
            ->pluck('quantity', 'food_items_id');

        // ── Qty movements during period ─────────────────────────────────────────
        $inDuring = StockLedger::where('warehouse_id', $godown->id)
            ->where('location_id', $godownLocation->id)
            ->whereBetween('created_at', [$from, $to])
            ->where('direction', 'in')
            ->select('food_items_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('food_items_id')
            ->pluck('total', 'food_items_id');

        // Actual OUT from godown (adjustments out) — excludes transfers to bar
        $outDuring = StockLedger::where('warehouse_id', $godown->id)
            ->where('location_id', $godownLocation->id)
            ->whereBetween('created_at', [$from, $to])
            ->where('direction', 'out')
            ->where('movement_type', '!=', 'transfer')
            ->select('food_items_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('food_items_id')
            ->pluck('total', 'food_items_id');

        // Transfer OUT from godown to bar
        $transferDuring = StockLedger::where('warehouse_id', $godown->id)
            ->where('location_id', $godownLocation->id)
            ->whereBetween('created_at', [$from, $to])
            ->where('movement_type', 'transfer')
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
            $closingInUpToTo = StockLedger::where('warehouse_id', $godown->id)
                ->where('location_id', $godownLocation->id)
                ->where('created_at', '<=', $to)
                ->where('direction', 'in')
                ->select('food_items_id', DB::raw('SUM(quantity) as total'))
                ->groupBy('food_items_id')
                ->pluck('total', 'food_items_id');

            $closingOutUpToTo = StockLedger::where('warehouse_id', $godown->id)
                ->where('location_id', $godownLocation->id)
                ->where('created_at', '<=', $to)
                ->where('direction', 'out')
                ->select('food_items_id', DB::raw('SUM(quantity) as total'))
                ->groupBy('food_items_id')
                ->pluck('total', 'food_items_id');
        }

        $reportData = $liquorItems->map(function ($item) use ($inDuring, $outDuring, $transferDuring, $inAmountDuring, $wac, $currentStockMap, $useLiveClosing, $closingInUpToTo, $closingOutUpToTo) {
            $inQty       = (int) ($inDuring[$item->id]       ?? 0);
            $outQty      = (int) ($outDuring[$item->id]      ?? 0);
            $transferQty = (int) ($transferDuring[$item->id] ?? 0);

            // Closing reflects the true stock as of the report's end date: live
            // current stock for a report covering today, otherwise reconstructed
            // from all ledger movements up to that date. Opening is derived
            // backward from closing using this period's movements.
            $closingQty = $useLiveClosing
                ? (int) ($currentStockMap[$item->id] ?? 0)
                : max(0, (int) ($closingInUpToTo[$item->id] ?? 0) - (int) ($closingOutUpToTo[$item->id] ?? 0));
            $openingQty = max(0, $closingQty - $inQty + $outQty + $transferQty);

            // WAC per bottle — used for opening, out, transfer, closing valuation
            $wacPerBtl   = (float) ($wac[$item->id] ?? 0);
            $inAmount    = round((float) ($inAmountDuring[$item->id] ?? 0), 2);

            return [
                'item'            => $item,
                'opening_qty'     => $openingQty,
                'in_qty'          => $inQty,
                'out_qty'         => $outQty,
                'transfer_qty'    => $transferQty,
                'closing_qty'     => $closingQty,
                'price_per_btl'   => $wacPerBtl,
                'opening_amount'  => round($openingQty  * $wacPerBtl, 2),
                'in_amount'       => $inAmount,
                'out_amount'      => round($outQty      * $wacPerBtl, 2),
                'transfer_amount' => round($transferQty * $wacPerBtl, 2),
                'closing_amount'  => round($closingQty  * $wacPerBtl, 2),
            ];
        });

        return [
            'reportData'           => $reportData,
            'totalOpening'         => $reportData->sum('opening_qty'),
            'totalIn'              => $reportData->sum('in_qty'),
            'totalOut'             => $reportData->sum('out_qty'),
            'totalTransfer'        => $reportData->sum('transfer_qty'),
            'totalClosing'         => $reportData->sum('closing_qty'),
            'totalOpeningAmount'   => $reportData->sum('opening_amount'),
            'totalInAmount'        => $reportData->sum('in_amount'),
            'totalOutAmount'       => $reportData->sum('out_amount'),
            'totalTransferAmount'  => $reportData->sum('transfer_amount'),
            'totalClosingAmount'   => $reportData->sum('closing_amount'),
            'from'                 => $from,
            'to'                   => $to,
        ];
    }

    public function report(Request $request)
    {
        try {
            $data       = $this->getReportData($request);
            $page_title = 'Godown Stock Report';
            $title      = 'Godown Report';

            return view('liquor_stock.godown.report', array_merge($data, compact('page_title', 'title')));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function downloadReport(Request $request)
    {
        try {
            $data     = $this->getReportData($request);
            $pdf      = Pdf::loadView('liquor_stock.godown.report_pdf', $data)
                ->setPaper('a4', 'landscape');
            $filename = 'godown-report-' . $data['from']->format('d-m-Y') . '-to-' . $data['to']->format('d-m-Y') . '.pdf';

            return $pdf->download($filename);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
