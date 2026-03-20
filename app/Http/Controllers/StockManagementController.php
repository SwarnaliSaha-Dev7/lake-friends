<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\FoodCategory;
use App\Models\FoodItem;
use App\Models\FoodItemCurrentStock;
use App\Models\Location;
use App\Models\StockLedger;
use App\Models\User;
use App\Notifications\ApprovalNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class StockManagementController extends Controller
{
    public function godownStockList()
    {
        try {
            // $page_title     = 'Current Godown Inventory';
            $page_title     = 'Liquor Stock Manage';
            // $sub_title      = 'Overview of your current stock inventory system';
            $title          = 'Liquor Stock Manage';

            $user           = auth()->user();
            $club_id        = $user->club_id;

            $godownLocationId = Location::where('name', 'godown')->first()->id;

            $godownStockList = StockLedger::with('foodItem.foodItemCat')
                ->where('club_id', $club_id)
                ->where('status', 'approved')
                ->where('location_id', $godownLocationId)
                ->latest()
                ->get();

            // dd($godownStockList);
            $liquors = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->where('is_active', 1)
                ->latest()
                ->get();

            $lowStockItemsCount = FoodItemCurrentStock::with('foodItem')
                ->where('club_id', $club_id)
                ->whereHas('foodItem', function ($query) {
                    $query->whereColumn('food_item_current_stocks.quantity', '<=', 'food_items.low_stock_alert_qty');
                })
                ->count();

            return view('stock_management.godown.godown_stock_manage', compact('page_title', 'title', 'godownStockList', 'liquors', 'lowStockItemsCount'));
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    public function godownStockStore(Request $request)
    {
        // return (gettype($request->quantity));
        try {
            $user           = auth()->user();
            $club_id        = $user->club_id;

            $currentGodownStock = FoodItemCurrentStock::where('club_id', $club_id)
                ->where('food_items_id', $request->liquor_id)
                ->first();

            $currentGodownQuantity = $currentGodownStock?->quantity ?? 0;

            if (($request->direction == 'out' || $request->movement == 'transfer_to_bar') && $currentGodownQuantity < $request->quantity) {
                return response()->json([
                    'statusCode' => 400,
                    'message' => 'Insufficient stock',
                ]);
            }

            // $liquor = FoodItem::where('id', $request->liquor_id)->first();
            // if ($liquor->is_beer == 1) {
            //     $unit = 'bottle';
            // } else {
            //     $unit = 'ml';
            // }

            $unit = 'bottle';

            $locationId = Location::where('name', 'godown')->first()->id;

            $godownStockledger = StockLedger::create([
                'club_id' => $club_id,
                'food_items_id' => $request->liquor_id,
                'location_id' => $locationId,
                'movement_type' => $request->movement,
                'direction' => $request->direction,
                'quantity' => $request->quantity,
                'unit' => $unit,
                'status' => 'pending',
            ]);

            if ($request->movement == 'transfer_to_bar') {
                $barLocationId = Location::where('name', 'bar')->first()->id;

                $foodItem = FoodItem::find($request->liquor_id);
                if ($foodItem->is_beer == 1) {
                    $quantity = $request->quantity;
                    $barItemUnit = 'bottle';
                } else {
                    $quantity = $request->quantity * $foodItem->size_ml;
                    $barItemUnit = 'ml';
                }

                $barStockledger = StockLedger::create([
                    'club_id' => $club_id,
                    'food_items_id' => $request->liquor_id,
                    'location_id' => $barLocationId,
                    'movement_type' => $request->movement,
                    'direction' => 'in',
                    'quantity' => $quantity,
                    'unit' => $barItemUnit,
                    'status' => 'pending',
                ]);
            }

            if ($user->hasRole('admin')) {
                $godownStockledger->update([
                    'status' => 'approved',
                ]);

                if ($request->movement == 'transfer_to_bar') {
                    $barStockledger->update([
                        'status' => 'approved',
                    ]);
                }

                if ($currentGodownStock) {
                    if ($request->movement == 'transfer_to_bar') {
                        $currentGodownStock->quantity -= $request->quantity;
                        $currentBarStock = FoodItemCurrentStock::where('club_id', $club_id)
                            ->where('food_items_id', $request->liquor_id)
                            ->where('location_id', $barLocationId)
                            ->first();
                        if ($currentBarStock) {
                            $currentBarStock->quantity += $request->quantity;
                            $currentBarStock->save();
                        } else {
                            $currentBarStock = FoodItemCurrentStock::create([
                                'club_id' => $club_id,
                                'location_id' => $barLocationId,
                                'food_items_id' => (int) $request->liquor_id,
                                'quantity' => $request->quantity,
                                'unit' => $barItemUnit,
                            ]);
                        }
                    } else {
                        if ($request->direction == 'out') {
                            $currentGodownStock->quantity -= $request->quantity;
                        } else {
                            $currentGodownStock->quantity += $request->quantity;
                        }
                    }
                    $currentGodownStock->save();
                } else {
                    $currentGodownStock = FoodItemCurrentStock::create([
                        'club_id' => $club_id,
                        'location_id' => $locationId,
                        'food_items_id' => (int) $request->liquor_id,
                        'quantity' => $request->quantity,
                        'unit' => $unit,
                    ]);
                }

                $payload = [
                    'food_items_id' => $request->liquor_id,
                    'location_id' => $locationId,
                    'movement_type' => $request->movement,
                    'direction' => $request->direction,
                    'quantity' => $request->quantity,
                    'unit' => $unit,
                    'bar_stock_ledger_id' => $barStockledger?->id ?? null,
                    'bar_location_id' => $barLocationId ?? null,
                ];

                $approval = ActionApproval::create([
                    'club_id' => $club_id,
                    'module' => 'godown_stock_management',
                    'action_type' => 'create',
                    'entity_model' => 'StockLedger',
                    'maker_user_id' => $user->id,
                    'entity_id' => $godownStockledger->id,
                    'checker_user_id' => $user->id,
                    'request_payload' => json_encode($payload),
                    'status' => 'approved',
                    'approved_or_rejected_at' => now(),
                ]);
            } else {
                $payload = [
                    'food_items_id' => $request->liquor_id,
                    'location_id' => $locationId,
                    'movement_type' => $request->movement,
                    'direction' => $request->direction,
                    'quantity' => $request->quantity,
                    'unit' => $unit,
                    'bar_stock_ledger_id' => $barStockledger?->id ?? null,
                    'bar_location_id' => $barLocationId ?? null,
                ];

                $approval = ActionApproval::create([
                    'club_id' => $club_id,
                    'module' => 'godown_stock_management',
                    'action_type' => 'create',
                    'entity_id' => $godownStockledger->id,
                    'entity_model' => 'StockLedger',
                    'maker_user_id' => $user->id,
                    'request_payload' => json_encode($payload)
                ]);



                $approvers = User::role(['operator', 'admin'])
                    ->where('id', '!=', $user->id)
                    ->get();

                Notification::send($approvers, new ApprovalNotification($approval));
            }


            return response()->json([
                'statusCode' => 200,
                'message' => 'Stock added successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'message' => 'Something went wrong',
            ]);
        }
    }

    public function godownCurrentStockList()
    {
        try {
            $page_title     = 'Current Godown Inventory';
            $sub_title      = 'Overview of your current godown stock inventory system';
            $title          = 'Current Godown Inventory';

            $user           = auth()->user();
            $club_id        = $user->club_id;

            $locationId = Location::where('name', 'godown')->first()->id;

            $godownStockList = FoodItemCurrentStock::with('foodItem.foodItemCat')
                ->where('club_id', $club_id)
                ->where('location_id', $locationId)
                ->latest()
                ->get();

            $itemNames = FoodItem::where('club_id', $club_id)
                ->latest()
                ->get();

            $itemCategories = FoodCategory::where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->latest()
                ->get();


            return view('stock_management.godown.godown_current_stock_inventory', compact('page_title', 'title', 'sub_title', 'godownStockList', 'itemNames', 'itemCategories'));
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    public function barStockList()
    {
        try {
            // $page_title     = 'Current Godown Inventory';
            $page_title     = 'Bar Stock Manage';
            // $sub_title      = 'Overview of your current stock inventory system';
            $title          = 'Bar Stock Manage';

            $user           = auth()->user();
            $club_id        = $user->club_id;

            $barLocationId = Location::where('name', 'bar')->first()->id;

            $barStockList = StockLedger::with('foodItem.foodItemCat')
                ->where('club_id', $club_id)
                ->where('status', 'approved')
                ->where('location_id', $barLocationId)
                ->latest()
                ->get();

            // dd($godownStockList);
            $liquors = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->where('is_active', 1)
                ->latest()
                ->get();

            $lowStockItemsCount = FoodItemCurrentStock::with('foodItem')
                ->where('club_id', $club_id)
                ->whereHas('foodItem', function ($query) {
                    $query->whereColumn('food_item_current_stocks.quantity', '<=', 'food_items.low_stock_alert_qty');
                })
                ->count();

            return view('stock_management.bar.bar_stock_manage', compact('page_title', 'title', 'barStockList', 'liquors', 'lowStockItemsCount'));
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    public function barStockManage()
    {
        return view('stock_management.bar.bar_stock_manage');
    }

    public function barCurrentStockInventory()
    {
        return view('stock_management.current_stock_inventory');
    }

    public function liquorStockReport()
    {
        return view('stock_management.liquor_stock_report');
    }
}
