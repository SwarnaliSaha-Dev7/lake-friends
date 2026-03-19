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
                'notes'         => 'nullable|string|max:500',
            ]);

            $godown         = $this->getOrCreateGodown($club_id);
            $godownLocation = $this->getGodownLocation();
            $isAdmin        = Auth::user()->hasRole('admin');

            $foodItem = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->where('id', $request->food_items_id)
                ->firstOrFail();

            $payload = [
                'warehouse_id'   => $godown->id,
                'location_id'    => $godownLocation->id,
                'food_items_id'  => $foodItem->id,
                'item_name'      => $foodItem->name,
                'quantity'       => (int) $request->quantity,
                'unit'           => 'bottle',
                'size_ml'        => $foodItem->size_ml,
                'notes'          => $request->notes,
                'movement_type'  => 'purchase',
                'direction'      => 'in',
                'reference_type' => 'manual',
            ];

            if ($isAdmin) {
                DB::beginTransaction();

                StockLedger::create([
                    'warehouse_id'   => $godown->id,
                    'location_id'    => $godownLocation->id,
                    'food_items_id'  => $foodItem->id,
                    'movement_type'  => 'purchase',
                    'direction'      => 'in',
                    'quantity'       => $request->quantity,
                    'reference_type' => 'manual',
                ]);

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

        $beforeFrom = StockLedger::where('warehouse_id', $godown->id)
            ->where('location_id', $godownLocation->id)
            ->where('created_at', '<', $from)
            ->select('food_items_id', 'direction', DB::raw('SUM(quantity) as total'))
            ->groupBy('food_items_id', 'direction')
            ->get()
            ->groupBy('food_items_id');

        $inDuring = StockLedger::where('warehouse_id', $godown->id)
            ->where('location_id', $godownLocation->id)
            ->whereBetween('created_at', [$from, $to])
            ->where('direction', 'in')
            ->select('food_items_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('food_items_id')
            ->pluck('total', 'food_items_id');

        // Actual OUT from godown (sales, adjustments out) — excludes transfers to bar
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

        $reportData = $liquorItems->map(function ($item) use ($beforeFrom, $inDuring, $outDuring, $transferDuring) {
            $before      = $beforeFrom->get($item->id, collect());
            $beforeIn    = (int) $before->where('direction', 'in')->sum('total');
            $beforeOut   = (int) $before->where('direction', 'out')->sum('total');
            $openingQty  = max(0, $beforeIn - $beforeOut);
            $inQty       = (int) ($inDuring[$item->id]      ?? 0);
            $outQty      = (int) ($outDuring[$item->id]     ?? 0);
            $transferQty = (int) ($transferDuring[$item->id] ?? 0);
            $closingQty  = max(0, $openingQty + $inQty - $outQty - $transferQty);

            return [
                'item'         => $item,
                'opening_qty'  => $openingQty,
                'in_qty'       => $inQty,
                'out_qty'      => $outQty,
                'transfer_qty' => $transferQty,
                'closing_qty'  => $closingQty,
            ];
        });

        return [
            'reportData'      => $reportData,
            'totalOpening'    => $reportData->sum('opening_qty'),
            'totalIn'         => $reportData->sum('in_qty'),
            'totalOut'        => $reportData->sum('out_qty'),
            'totalTransfer'   => $reportData->sum('transfer_qty'),
            'totalClosing'    => $reportData->sum('closing_qty'),
            'from'            => $from,
            'to'              => $to,
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
