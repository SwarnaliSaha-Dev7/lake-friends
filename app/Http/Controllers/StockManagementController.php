<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Models\FoodItemCurrentStock;
use App\Models\Location;
use App\Models\StockLedger;
use Illuminate\Http\Request;

class StockManagementController extends Controller
{
    public function index()
    {
        return view('stock_management.index');
    }

    public function godownStockList()
    {
        try {
            $page_title     = 'Current Godown Inventory';
            $sub_title      = 'Overview of your current stock inventory system';
            $title          = 'Current Godown Inventory';

            $user           = auth()->user();
            $club_id        = $user->club_id;

            $godownStockList = StockLedger::with('foodItem')
                ->where('club_id', $club_id)->get();

            // dd($godownStockList);
            $liquors = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->where('is_active', 1)
                ->get();

            return view('stock_management.godown_stock_manage', compact('page_title', 'sub_title', 'title', 'godownStockList', 'liquors'));
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

            $liquor = FoodItem::where('id', $request->liquor_id)->first();
            if ($liquor->is_beer == 1) {
                $unit = 'bottle';
            } else {
                $unit = 'ml';
            }

            $locationId = Location::where('name', 'godown')->first()->id;

            $stockledger = Stockledger::create([
                'club_id' => $club_id,
                'food_items_id' => $request->liquor_id,
                'location_id' => $locationId,
                'movement_type' => $request->movement,
                'direction' => $request->direction,
                'quantity' => $request->quantity,
                'unit' => $unit,
            ]);

            $currentStock = FoodItemCurrentStock::where('club_id', $club_id)
                ->where('food_items_id', $request->liquor_id)
                ->first();

            if ($currentStock) {
                $currentStock->quantity += $request->quantity;
                $currentStock->save();
            } else {
                $currentStock = FoodItemCurrentStock::create([
                    'club_id' => $club_id,
                    'location_id' => $locationId,
                    'food_items_id' => (int) $request->liquor_id,
                    'quantity' => $request->quantity,
                    'unit' => $unit,
                ]);
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

    public function barStockManage()
    {
        return view('stock_management.bar_stock_manage');
    }

    public function currentStockInventory()
    {
        return view('stock_management.current_stock_inventory');
    }

    public function liquorStockReport()
    {
        return view('stock_management.liquor_stock_report');
    }
}
