<?php

namespace App\Http\Controllers;

use App\Models\FoodCategory;
use App\Models\FoodItem;
use App\Models\FoodItemPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LiquorItemManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $page_title     = 'Manage Liquor Items';
            $title          = 'Liquor Items List';

            $user           = auth()->user();
            $club_id        = $user->club_id;

            $liquorItemsList  = FoodItem::where('club_id', $club_id)
                                    ->with([
                                        'foodItemPrice',
                                        'foodItemCat'])
                                    ->where('item_type', 'liquor')
                                    ->latest()
                                    ->get();

            $liquorCatList    = FoodCategory::where('club_id', $club_id)
                                          ->where('item_type', 'liquor')
                                          ->get();

            return view('liquor_items.list', compact('liquorItemsList','liquorCatList','page_title','title'));

        }

        catch (\Throwable $th) {
            return $th->getMessage();
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //dd($request->all());

        try {

            $user           = auth()->user();
            $club_id        = $user->club_id;

            DB::beginTransaction();

            $data = $request->validate([
                'itemName'  => ['required', 'string', 'max:255',
                                Rule::unique('food_items','name')
                                ->where(function ($query) use ($club_id) {
                                    return $query->where('club_id', $club_id)
                                                    ->where('item_type','liquor')
                                                    ->whereNull('deleted_at');
                                            }),
                            ],
                'itemCat' => 'required',

                'itemPrice' => 'required|numeric|min:0|max:9999999999|decimal:0,2',

                'itemImage' => 'required|image|mimes:jpeg,png,jpg|max:5120',

                'itemCode' => ['required','string','max:255',
                                Rule::unique('food_items','code')
                                    ->where(function ($query) use ($club_id) {
                                        return $query->where('club_id', $club_id)
                                                     ->where('item_type','liquor')
                                                     ->whereNull('deleted_at');
                                                }),
                            ],

                'itemstatus' => 'required|boolean',

                'size_ml' => 'nullable|numeric|min:0',

                'low_stock_alert_qty' => 'nullable|numeric|min:0',

                'is_beer' => 'nullable|boolean',

            ]);

            // checkbox handling
            $isBeer = $request->boolean('is_beer');

            // unit logic
            $unit = $isBeer ? 'bottle' : 'ml';

            $dest_path  = 'uploads/images';
            $image_path = null;

            if($request->hasFile('itemImage')){

            $file       = $request->file('itemImage');
            $filename   = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
            $path       = $file->storeAs($dest_path, $filename, 'public');
            $image_path = 'storage/' . $path;
            }

            $foodItem   = FoodItem::create([
                'club_id'             => $club_id,
                'name'                => $request->itemName,
                'category_id'         => $request->itemCat,
                'item_type'           => 'liquor',
                'image'               => $image_path,
                'code'                => $request->itemCode,
                'is_active'           => $request->itemstatus,
                'unit'                => $unit,
                'size_ml'             => $request->size_ml,
                'is_beer'             => $isBeer,
                'low_stock_alert_qty' => $request->itemLow,

            ]);

            $foodPrice  = FoodItemPrice::create([
                'item_id'         => $foodItem->id,
                'price'           => $request->itemPrice,
                'effective_from'  => now(),
                'is_active'       => '1',
            ]);

            DB::commit();

            return response()->json([

            'statusCode'=>200,

            'message'   =>'Liquor item added successfully.'

            ]);

        }

        catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'statusCode' =>500,
                'error'      => $th->getMessage(),
            ]);
        }


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $user = auth()->user();
            $club_id = $user->club_id;

            $liquorItem = FoodItem::where('club_id', $club_id)
                                  ->where('item_type','liquor')
                                  ->where('id', $id)
                                  ->firstOrFail();

            $liquorItem->delete();

            return response()->json([
                'statusCode' => 200,
                'message' => 'Liquor item deleted successfully'
            ]);

        } catch (\Throwable $th) {

            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage()
            ]);

        }
    }
}
