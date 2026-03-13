<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\FoodCategory;
use App\Models\FoodItem;
use App\Models\FoodItemPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                                    ->where('item_type', 'food')
                                    ->latest()
                                    ->get();

            $foodCatList    = FoodCategory::where('club_id', $club_id)
                                          ->where('item_type', 'food')
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

                // 'itemPrice' => 'required|numeric|min:0|max:9999999999|decimal:0,2',

                'itemImage' => 'required|image|mimes:jpeg,png,jpg|max:5120',

                'itemCode' => ['required','string','max:255',
                                Rule::unique('food_items','code')
                                    ->where(function ($query) use ($club_id) {
                                        return $query->where('club_id', $club_id)
                                                     ->where('item_type','food')
                                                     ->whereNull('deleted_at');
                                                }),
                            ],

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
                'is_active'           => $request->itemstatus,
                'unit'                => 'plate'
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

            $foodItem = FoodItem::with([
                        'foodItemPrice',
                        'foodItemCat'
                        ])
                        ->where('club_id', $club_id)
                        ->where('item_type','food')
                        ->where('id', $id)
                        ->firstOrFail();

            $pendingApproval = ActionApproval::where('club_id', $club_id)
                                             ->where('module', 'food_price_update')
                                             ->where('entity_model', 'FoodItem')
                                             ->where('entity_id', $foodItem->id)
                                             ->where('status', 'pending')
                                             ->latest()
                                             ->first();

            return response()->json([
                'data'       => $foodItem,
                'pendingApproval' => $pendingApproval,
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
        try {

            $user    = auth()->user();
            $club_id = $user->club_id;

            DB::beginTransaction();

            $request->validate([

                'itemName' => ['required', 'string', 'max:255',
                                Rule::unique('food_items','name')
                                ->ignore($id)
                                ->where(function ($query) use ($club_id) {
                                    return $query->where('club_id', $club_id)
                                                    ->where('item_type','food')
                                                    ->whereNull('deleted_at');
                                            }),
                              ],

                'itemCat' => 'required',

                // 'itemPrice' => 'required|numeric|min:0|max:9999999999|decimal:0,2',

                'itemCode'   => ['required','string','max:255',
                                Rule::unique('food_items','code')
                                    ->ignore($id)
                                    ->where(function ($query) use ($club_id) {
                                        return $query->where('club_id', $club_id)
                                                     ->where('item_type','food')
                                                     ->whereNull('deleted_at');
                                                }),
                                ],

                'itemstatus' => 'required|boolean',

            ]);

            $foodItem =  FoodItem::where('club_id',$club_id)
                                 ->where('id',$id)
                                 ->where('item_type','food')
                                 ->firstOrFail();

            $dest_path = 'uploads/images';
            $image_path = null;

            if($request->hasFile('itemImage')){

                if($foodItem->image && file_exists(public_path($foodItem->image))){
                    unlink(public_path($foodItem->image));
                }

                $file = $request->file('itemImage');
                $filename = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path = $file->storeAs( $dest_path, $filename, 'public');
                $image_path = 'storage/' . $path;
            }

            else{
                $image_path = $foodItem->image;
            }

            $foodItem->update([

                'name' => $request->itemName,

                'category_id' => $request->itemCat,

                'image' => $image_path,

                'code' => $request->itemCode,

                'is_active' => $request->itemstatus

            ]);

            // Get current active price
            // $currentPrice = FoodItemPrice::where('item_id',$foodItem->id)
            //                              ->where('is_active', 1)
            //                              ->first();

            // if($currentPrice){
                // If price changed
                // if($currentPrice->price != $request->itemPrice){

                    // Deactivate old price
                    // $currentPrice->update([
                    //     'is_active' => 0,
                    //     'effective_to' => now(),
                    // ]);

                    // Insert new price
                    // FoodItemPrice::create([
                    //     'item_id' => $foodItem->id,
                    //     'price' => $request->itemPrice,
                    //     'effective_from' => now(),
                    //     'is_active' => '1'
                    // ]);
                // }
            // }

            // else{

            //     // If no price exists yet

            //     FoodItemPrice::create([
            //         'item_id'        => $foodItem->id,
            //         'price'          => $request->itemPrice,
            //         'effective_from' => now(),
            //         'is_active'      => 1
            //     ]);
            // }

            DB::commit();

            return response()->json([

                'statusCode'=>200,

                'message'=>'Food item updated successfully'

            ]);


        }

        catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
            'statusCode' => 500,
            'error' => $th->getMessage(),
            ]);
        }


    }

    public function requestPriceChange(Request $request){

        try {

            $user = auth()->user();
            //dd($user->role);
            $club_id = $user->club_id;

            $request->validate([
                'item_id' => 'required',
                'new_price' => 'required|numeric|min:0|max:9999999999|decimal:0,2',
            ]);

            $foodItem =  FoodItem::where('club_id',$club_id)
                                 ->where('id',$request->item_id)
                                 ->where('item_type','food')
                                 ->firstOrFail();

            $currentPrice = FoodItemPrice::where('item_id', $foodItem->id)
                                        ->where('is_active', 1)
                                        ->first();

            $payload = [
                'item_id' => $foodItem->id,
                // 'item_name' => $foodItem->name,
                'old_price' => $currentPrice?->price ?? 0,
                'new_price' => $request->new_price
            ];

            //ADMIN → skip approval
            if(Auth::user()->hasRole('admin')){

                DB::beginTransaction();

                if($currentPrice){
                    $currentPrice->update([
                        'is_active' => 0,
                        'effective_to' => now()
                    ]);
                }

                FoodItemPrice::create([
                    'item_id' => $foodItem->id,
                    'price' => $request->new_price,
                    'effective_from' => now(),
                    'is_active' => 1
                ]);

                ActionApproval::create([
                    'club_id' => $club_id,
                    'module' => 'food_price_update',
                    'action_type' => 'update',
                    'entity_model' => 'FoodItem',
                    'entity_id' => $foodItem->id,
                    'maker_user_id' => Auth::id(),
                    'checker_user_id' => Auth::id(),
                    'request_payload' => json_encode($payload),
                    'status' => 'approved',
                    'approved_or_rejected_at' => now()
                ]);

                DB::commit();

                return response()->json([
                    'statusCode' => 200,
                    'message' => 'Price updated successfully'
                ]);
            }

            //NORMAL USER → maker checker

            ActionApproval::create([
                'club_id' => $club_id,
                'module' => 'food_price_update',
                'action_type' => 'update',
                'entity_model' => 'FoodItem',
                'entity_id' => $foodItem->id,
                'maker_user_id' => auth()->id(),
                'request_payload' => json_encode($payload),
                'status' => 'pending'
            ]);

            return response()->json([
                'statusCode' => 200,
                'message' => 'Price change request sent for approval'
            ]);

        }
        catch (\Throwable $th){

            DB::rollBack();

            return response()->json([
            'statusCode' => 500,
            'error' => $th->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $user = auth()->user();
            $club_id = $user->club_id;

            $foodItem = FoodItem::where('club_id', $club_id)
                                ->where('item_type','food')
                                ->where('id', $id)
                                ->firstOrFail();

            $foodItem->delete();

            return response()->json([
                'statusCode' => 200,
                'message' => 'Food item deleted successfully'
            ]);

        } catch (\Throwable $th) {

            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage()
            ]);

        }
    }
}
