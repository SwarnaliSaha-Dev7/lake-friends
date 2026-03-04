<?php

namespace App\Http\Controllers;

use App\Models\FoodCategory;
use App\Models\FoodItem;
use App\Models\FoodItemPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FoodItemManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
        
            $page_title     = 'Manage Food Items';
            $title          = 'Food Items List';

            $user           = auth()->user();
            $club_id        = $user->club_id;

            $foodItemsList  = FoodItem::where('club_id', $club_id)
                                    ->with([
                                        'foodItemPrice',
                                        'foodItemCat'])
                                    ->latest()
                                    ->get();

            $foodCatList    = FoodCategory::where('club_id', $club_id)
                                        ->get();

            return view('food_items.list', compact('foodItemsList','foodCatList','page_title','title'));

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

            $exists = FoodItem::where('name', $request->itemName)
                            ->where('club_id', $club_id)
                            ->whereNull('deleted_at')
                            ->exists();

            if($exists){
                return response()->json([
                    'statusCode' => 409,
                    'message' => 'Food item already exists'
                ]);
            }

            DB::beginTransaction();

            $data = $request->validate([
                'itemName'  => ['required', 'string', 'max:255',
                                Rule::unique('food_items','name')
                                ->where(function ($query) use ($club_id) {
                                    return $query->where('club_id', $club_id)
                                                    ->where('item_type','food')
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
                                                    ->where('item_type','food')
                                                    ->whereNull('deleted_at');
                                                }),
                            ],

                'itemLow' => 'required|numeric|min:0|',

                'itemstatus' => 'required|boolean',

            ]);

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
                'item_type'           => 'food',
                'image'               => $image_path,
                'code'                => $request->itemCode,
                'low_stock_alert_qty' => $request->itemLow,
                'is_active'           => $request->itemstatus,
                'unit'                => 'plate'
            ]);

            $foodPrice  = FoodItemPrice::create([
                'item_id'         => $foodItem->id,
                'price'           => $request->itemPrice,
                'approval_status' => 'approved'
            ]);

            DB::commit();

            return response()->json([

            'statusCode'=>200,

            'message'   =>'Food item added successfully'

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
        try {
            $user    = auth()->user();
            $club_id = $user->club_id;

            $item = FoodItem::with([
                        'foodItemPrice',
                        'foodItemCat'
                    ])
                            ->where('club_id', $club_id)
                            ->where('id', $id)
                            ->firstOrFail();

            return response()->json([
                'data'       => $item,
                'statusCode' => 200,
                'message'    => 'Food item Fetched successfully'
            ]);    

        } 
        
        catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'error'      => $th->getMessage(),
            ]);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user    = auth()->user();
        $club_id = $user->club_id;

        $exists = FoodItem::where('name', $request->itemName)
                        ->where('club_id', $club_id)
                        ->where('item_type','food')
                        ->where('id','!=',$id)
                        ->whereNull('deleted_at')
                        ->exists();

        if($exists){

            return response()->json([

                'statusCode'=>409,

                'message'=>'Food item already exists'

            ]);

        }

        $request->validate([

            'itemName' => 'required|string|max:255',

            'itemCat' => 'required',

            'itemPrice' => 'required|numeric|min:0',

            'itemstatus' => 'required|boolean'

        ]);

        $item = FoodItem::where('club_id',$club_id)
                ->where('id',$id)
                ->firstOrFail();

        $image_path = $item->image;

        if($request->hasFile('itemImage')){

            $dest_path = 'uploads/images';

            $file = $request->file('itemImage');

            $filename = time().rand(1000,9999).'_'.$file->getClientOriginalName();

            $path = $file->storeAs($dest_path,$filename,'public');

            $image_path = 'storage/'.$path;

        }

        $item->update([

            'name' => $request->itemName,

            'category_id' => $request->itemCat,

            'image' => $image_path,

            'code' => $request->code,
            
            'is_active' => $request->itemstatus

        ]);

        $priceRow = FoodItemPrice::where('item_id',$item->id)->first();


        if($priceRow){

            $priceRow->update([

                'price'=>$request->itemPrice

            ]);

        }
        else{

            FoodItemPrice::create([

                'item_id'=>$item->id,

                'price'=>$request->itemPrice,

                'approval_status'=>'pending'

            ]);

        }

        return response()->json([

            'statusCode'=>200,

            'message'=>'Food item updated successfully'

        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
