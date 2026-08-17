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
                ->where('module', 'bar_stock_transfer')
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
            ]);

            $warehouse      = $this->getOrCreateWarehouse($club_id);
            $godownLocation = $this->getGodownLocation();
            $barLocation    = $this->getBarLocation();
            $isAdmin        = Auth::user()->hasRole('admin');

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
            ];

            if ($isAdmin) {
                DB::beginTransaction();

                (new BarStockController)->executeTransfer(
                    $warehouse->id, $godownLocation->id, $barLocation->id,
                    $foodItem->id, $request->bottles, $barQty, $godownStock
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

        $beforeFrom = StockLedger::where('warehouse_id', $warehouse->id)
            ->where('location_id', $barLocation->id)
            ->where('created_at', '<', $from)
            ->select('food_items_id', 'direction', DB::raw('SUM(quantity) as total'))
            ->groupBy('food_items_id', 'direction')
            ->get()
            ->groupBy('food_items_id');

        $inDuring = StockLedger::where('warehouse_id', $warehouse->id)
            ->where('location_id', $barLocation->id)
            ->whereBetween('created_at', [$from, $to])
            ->where('movement_type', 'transfer')
            ->where('direction', 'in')
            ->select('food_items_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('food_items_id')
            ->pluck('total', 'food_items_id');

        $outDuring = StockLedger::where('warehouse_id', $warehouse->id)
            ->where('location_id', $barLocation->id)
            ->whereBetween('created_at', [$from, $to])
            ->where('movement_type', 'sale')
            ->where('direction', 'out')
            ->select('food_items_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('food_items_id')
            ->pluck('total', 'food_items_id');

        $reportData = $beverageItems->map(function ($item) use ($beforeFrom, $inDuring, $outDuring, $wacPerBottle, $currentBarStockMap) {
            $before     = $beforeFrom->get($item->id, collect());
            $beforeIn   = (int) $before->where('direction', 'in')->sum('total');
            $beforeOut  = (int) $before->where('direction', 'out')->sum('total');
            $openingQty = max(0, $beforeIn - $beforeOut);
            $inQty      = (int) ($inDuring[$item->id]  ?? 0);
            $outQty     = (int) ($outDuring[$item->id] ?? 0);
            $ledgerClosing = max(0, $openingQty + $inQty - $outQty);

            $hasNoLedger = $openingQty === 0 && $inQty === 0;
            $closingQty  = ($hasNoLedger && $ledgerClosing === 0)
                ? (int) ($currentBarStockMap[$item->id] ?? 0)
                : $ledgerClosing;

            $wacBtl = (float) ($wacPerBottle[$item->id] ?? 0);

            return [
                'item'           => $item,
                'unit'           => 'BTL',
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
