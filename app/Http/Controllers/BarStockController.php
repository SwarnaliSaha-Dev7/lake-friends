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
                ->where('module', 'bar_stock_transfer')
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
            ]);

            $warehouse      = $this->getOrCreateWarehouse($club_id);
            $godownLocation = $this->getGodownLocation();
            $barLocation    = $this->getBarLocation();
            $isAdmin        = Auth::user()->hasRole('admin');

            $foodItem = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'liquor')
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
            ];

            if ($isAdmin) {
                DB::beginTransaction();

                $this->executeTransfer(
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

    public function executeTransfer(int $warehouseId, int $godownLocationId, int $barLocationId, int $foodItemId, int $bottles, int $barQty, ?FoodItemCurrentStock $godownStock): void
    {
        StockLedger::create([
            'warehouse_id'   => $warehouseId,
            'location_id'    => $godownLocationId,
            'food_items_id'  => $foodItemId,
            'movement_type'  => 'transfer',
            'direction'      => 'out',
            'quantity'       => $bottles,
            'reference_type' => 'manual',
        ]);

        if ($godownStock) {
            $godownStock->decrement('quantity', $bottles);
        }

        StockLedger::create([
            'warehouse_id'   => $warehouseId,
            'location_id'    => $barLocationId,
            'food_items_id'  => $foodItemId,
            'movement_type'  => 'transfer',
            'direction'      => 'in',
            'quantity'       => $barQty,
            'reference_type' => 'manual',
        ]);

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

        // All bar movements before period → opening
        $beforeFrom = StockLedger::where('warehouse_id', $warehouse->id)
            ->where('location_id', $barLocation->id)
            ->where('created_at', '<', $from)
            ->select('food_items_id', 'direction', DB::raw('SUM(quantity) as total'))
            ->groupBy('food_items_id', 'direction')
            ->get()
            ->groupBy('food_items_id');

        // IN during period = transfers received from godown
        $inDuring = StockLedger::where('warehouse_id', $warehouse->id)
            ->where('location_id', $barLocation->id)
            ->whereBetween('created_at', [$from, $to])
            ->where('movement_type', 'transfer')
            ->where('direction', 'in')
            ->select('food_items_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('food_items_id')
            ->pluck('total', 'food_items_id');

        // OUT during period = sales from bar
        $outDuring = StockLedger::where('warehouse_id', $warehouse->id)
            ->where('location_id', $barLocation->id)
            ->whereBetween('created_at', [$from, $to])
            ->where('movement_type', 'sale')
            ->where('direction', 'out')
            ->select('food_items_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('food_items_id')
            ->pluck('total', 'food_items_id');

        $reportData = $liquorItems->map(function ($item) use ($beforeFrom, $inDuring, $outDuring) {
            $before     = $beforeFrom->get($item->id, collect());
            $beforeIn   = (int) $before->where('direction', 'in')->sum('total');
            $beforeOut  = (int) $before->where('direction', 'out')->sum('total');
            $openingQty = max(0, $beforeIn - $beforeOut);
            $inQty      = (int) ($inDuring[$item->id] ?? 0);
            $outQty     = (int) ($outDuring[$item->id] ?? 0);
            $closingQty = max(0, $openingQty + $inQty - $outQty);

            // Convert to bottle equivalents for totals (ml / size_ml for spirits)
            $sizeMl     = (int) ($item->size_ml ?? 1);
            $isBeer     = (bool) $item->is_beer;
            $unit       = $isBeer ? 'BTL' : 'ml';
            $toBottles  = fn($qty) => $isBeer ? $qty : ($sizeMl > 0 ? round($qty / $sizeMl, 2) : 0);

            return [
                'item'           => $item,
                'unit'           => $unit,
                'is_beer'        => $isBeer,
                'size_ml'        => $sizeMl,
                'opening_qty'    => $openingQty,
                'in_qty'         => $inQty,
                'out_qty'        => $outQty,
                'closing_qty'    => $closingQty,
                // Bottle equivalents for summary totals
                'opening_btl'    => $toBottles($openingQty),
                'in_btl'         => $toBottles($inQty),
                'out_btl'        => $toBottles($outQty),
                'closing_btl'    => $toBottles($closingQty),
            ];
        });

        return [
            'reportData'   => $reportData,
            'totalOpening' => $reportData->sum('opening_btl'),
            'totalIn'      => $reportData->sum('in_btl'),
            'totalOut'     => $reportData->sum('out_btl'),
            'totalClosing' => $reportData->sum('closing_btl'),
            'from'         => $from,
            'to'           => $to,
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
