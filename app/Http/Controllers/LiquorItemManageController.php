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

class LiquorItemManageController extends Controller
{
    public function index()
    {
        try {
            $page_title = 'Manage Liquor Items';
            $title      = 'Liquor Items List';
            $user       = auth()->user();
            $club_id    = $user->club_id;

            $liquorItemsList = FoodItem::where('club_id', $club_id)
                ->with(['foodItemPrice', 'foodItemCat'])
                ->where('item_type', 'liquor')
                ->latest()
                ->get();

            $liquorCatList = FoodCategory::where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->get();

            $pendingByItem = ActionApproval::where('club_id', $club_id)
                ->whereIn('module', ['liquor_item_create', 'liquor_item_delete', 'liquor_price_update'])
                ->where('status', 'pending')
                ->get(['entity_id', 'module'])
                ->groupBy('entity_id')
                ->map(fn($rows) => $rows->pluck('module')->toArray());

            $pendingCreateIds = $pendingByItem->filter(fn($m) => in_array('liquor_item_create', $m))->keys()->toArray();
            $pendingDeleteIds  = $pendingByItem->filter(fn($m) => in_array('liquor_item_delete', $m))->keys()->toArray();
            $pendingAnyIds     = $pendingByItem->keys()->toArray();

            return view('liquor_items.list', compact(
                'liquorItemsList', 'liquorCatList', 'page_title', 'title',
                'pendingCreateIds', 'pendingDeleteIds', 'pendingAnyIds'
            ));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function create() {}

    public function store(Request $request)
    {
        try {
            $user    = auth()->user();
            $club_id = $user->club_id;

            DB::beginTransaction();

            $request->validate([
                'itemName' => ['required', 'string', 'max:255',
                    Rule::unique('food_items', 'name')->where(function ($q) use ($club_id) {
                        return $q->where('club_id', $club_id)->where('item_type', 'liquor')->whereNull('deleted_at');
                    }),
                ],
                'itemCat'             => 'required',
                'itemPrice'           => 'required|numeric|min:0|max:9999999999|decimal:0,2',
                'itemImage'           => 'required|image|mimes:jpeg,png,jpg|max:5120',
                'itemCode'            => ['required', 'string', 'max:255',
                    Rule::unique('food_items', 'code')->where(function ($q) use ($club_id) {
                        return $q->where('club_id', $club_id)->where('item_type', 'liquor')->whereNull('deleted_at');
                    }),
                ],
                'itemstatus'          => 'required|boolean',
                'size_ml'             => 'nullable|numeric|min:0',
                'low_stock_alert_qty' => 'nullable|numeric|min:0',
                'is_beer'             => 'nullable|boolean',
            ]);

            $isBeer = $request->boolean('is_beer');
            $unit   = $isBeer ? 'bottle' : 'ml';

            $image_path = null;
            if ($request->hasFile('itemImage')) {
                $file       = $request->file('itemImage');
                $filename   = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path       = $file->storeAs('uploads/images', $filename, 'public');
                $image_path = 'storage/' . $path;
            }

            $isAdmin = Auth::user()->hasRole('admin');

            $foodItem = FoodItem::create([
                'club_id'             => $club_id,
                'name'                => $request->itemName,
                'category_id'         => $request->itemCat,
                'item_type'           => 'liquor',
                'image'               => $image_path,
                'code'                => $request->itemCode,
                'is_active'           => $isAdmin ? $request->itemstatus : 0,
                'unit'                => $unit,
                'size_ml'             => $request->size_ml,
                'is_beer'             => $isBeer,
                'low_stock_alert_qty' => $request->low_stock_alert_qty,
            ]);

            FoodItemPrice::create([
                'item_id'        => $foodItem->id,
                'price'          => $request->itemPrice,
                'effective_from' => now(),
                'is_active'      => 1,
            ]);

            $payload = [
                'item_id'             => $foodItem->id,
                'name'                => $request->itemName,
                'category_id'         => $request->itemCat,
                'code'                => $request->itemCode,
                'is_active'           => $request->itemstatus,
                'size_ml'             => $request->size_ml,
                'is_beer'             => $isBeer,
                'unit'                => $unit,
                'low_stock_alert_qty' => $request->low_stock_alert_qty,
                'price'               => $request->itemPrice,
                'image'               => $image_path,
            ];

            ActionApproval::create([
                'club_id'                 => $club_id,
                'module'                  => 'liquor_item_create',
                'action_type'             => 'create',
                'entity_model'            => 'FoodItem',
                'entity_id'               => $foodItem->id,
                'maker_user_id'           => Auth::id(),
                'checker_user_id'         => $isAdmin ? Auth::id() : null,
                'request_payload'         => json_encode($payload),
                'status'                  => $isAdmin ? 'approved' : 'pending',
                'approved_or_rejected_at' => $isAdmin ? now() : null,
            ]);

            DB::commit();

            return response()->json([
                'statusCode' => 200,
                'message'    => $isAdmin ? 'Liquor item added successfully.' : 'Liquor item submitted for approval.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function show(string $id) {}

    public function edit(string $id)
    {
        try {
            $user    = auth()->user();
            $club_id = $user->club_id;

            $liquorItem = FoodItem::with(['foodItemPrice', 'foodItemCat'])
                ->where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->where('id', $id)
                ->firstOrFail();

            $pendingPriceApproval = ActionApproval::where('club_id', $club_id)
                ->where('module', 'liquor_price_update')
                ->where('entity_model', 'FoodItem')
                ->where('entity_id', $liquorItem->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            return response()->json([
                'data'            => $liquorItem,
                'pendingApproval' => $pendingPriceApproval,
                'statusCode'      => 200,
                'message'         => 'Liquor item fetched successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $user    = auth()->user();
            $club_id = $user->club_id;

            DB::beginTransaction();

            $request->validate([
                'itemName' => ['required', 'string', 'max:255',
                    Rule::unique('food_items', 'name')->ignore($id)->where(function ($q) use ($club_id) {
                        return $q->where('club_id', $club_id)->where('item_type', 'liquor')->whereNull('deleted_at');
                    }),
                ],
                'itemCat'             => 'required',
                'itemCode'            => ['required', 'string', 'max:255',
                    Rule::unique('food_items', 'code')->ignore($id)->where(function ($q) use ($club_id) {
                        return $q->where('club_id', $club_id)->where('item_type', 'liquor')->whereNull('deleted_at');
                    }),
                ],
                'itemstatus'          => 'required|boolean',
                'size_ml'             => 'nullable|numeric|min:0',
                'low_stock_alert_qty' => 'nullable|numeric|min:0',
                'is_beer'             => 'nullable|boolean',
            ]);

            $liquorItem = FoodItem::where('club_id', $club_id)
                ->where('id', $id)
                ->where('item_type', 'liquor')
                ->firstOrFail();

            $isBeer     = $request->boolean('is_beer');
            $unit       = $isBeer ? 'bottle' : 'ml';
            $image_path = $liquorItem->image;

            if ($request->hasFile('itemImage')) {
                if ($liquorItem->image && file_exists(public_path($liquorItem->image))) {
                    unlink(public_path($liquorItem->image));
                }
                $file       = $request->file('itemImage');
                $filename   = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path       = $file->storeAs('uploads/images', $filename, 'public');
                $image_path = 'storage/' . $path;
            }

            $liquorItem->update([
                'name'                => $request->itemName,
                'category_id'         => $request->itemCat,
                'image'               => $image_path,
                'code'                => $request->itemCode,
                'is_active'           => $request->itemstatus,
                'size_ml'             => $request->size_ml,
                'is_beer'             => $isBeer,
                'unit'                => $unit,
                'low_stock_alert_qty' => $request->low_stock_alert_qty,
            ]);

            DB::commit();

            return response()->json(['statusCode' => 200, 'message' => 'Liquor item updated successfully.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function requestPriceChange(Request $request)
    {
        try {
            $user    = auth()->user();
            $club_id = $user->club_id;

            $request->validate([
                'item_id'   => 'required',
                'new_price' => 'required|numeric|min:0|max:9999999999|decimal:0,2',
            ]);

            $liquorItem = FoodItem::where('club_id', $club_id)
                ->where('id', $request->item_id)
                ->where('item_type', 'liquor')
                ->firstOrFail();

            $currentPrice = FoodItemPrice::where('item_id', $liquorItem->id)
                ->where('is_active', 1)
                ->first();

            $payload = [
                'item_id'   => $liquorItem->id,
                'old_price' => $currentPrice?->price ?? 0,
                'new_price' => $request->new_price,
            ];

            if (Auth::user()->hasRole('admin')) {
                DB::beginTransaction();

                if ($currentPrice) {
                    $currentPrice->update(['is_active' => 0, 'effective_to' => now()]);
                }

                FoodItemPrice::create([
                    'item_id'        => $liquorItem->id,
                    'price'          => $request->new_price,
                    'effective_from' => now(),
                    'is_active'      => 1,
                ]);

                ActionApproval::create([
                    'club_id'                 => $club_id,
                    'module'                  => 'liquor_price_update',
                    'action_type'             => 'update',
                    'entity_model'            => 'FoodItem',
                    'entity_id'               => $liquorItem->id,
                    'maker_user_id'           => Auth::id(),
                    'checker_user_id'         => Auth::id(),
                    'request_payload'         => json_encode($payload),
                    'status'                  => 'approved',
                    'approved_or_rejected_at' => now(),
                ]);

                DB::commit();

                return response()->json(['statusCode' => 200, 'message' => 'Price updated successfully.']);
            }

            ActionApproval::create([
                'club_id'         => $club_id,
                'module'          => 'liquor_price_update',
                'action_type'     => 'update',
                'entity_model'    => 'FoodItem',
                'entity_id'       => $liquorItem->id,
                'maker_user_id'   => Auth::id(),
                'request_payload' => json_encode($payload),
                'status'          => 'pending',
            ]);

            return response()->json(['statusCode' => 200, 'message' => 'Price change request sent for approval.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function destroy(string $id)
    {
        try {
            $user    = auth()->user();
            $club_id = $user->club_id;

            $liquorItem = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'liquor')
                ->where('id', $id)
                ->firstOrFail();

            $pendingApproval = ActionApproval::where('club_id', $club_id)
                ->where('entity_id', $id)
                ->whereIn('module', ['liquor_item_create', 'liquor_item_delete', 'liquor_price_update'])
                ->where('status', 'pending')
                ->first();

            if ($pendingApproval) {
                $label = match($pendingApproval->module) {
                    'liquor_item_create'  => 'add',
                    'liquor_item_delete'  => 'delete',
                    'liquor_price_update' => 'price change',
                    default               => 'approval',
                };
                return response()->json([
                    'statusCode' => 422,
                    'message'    => "This item already has a pending {$label} request. Please wait for it to be approved or rejected before proceeding.",
                ]);
            }

            if (Auth::user()->hasRole('admin')) {
                $liquorItem->delete();
                return response()->json(['statusCode' => 200, 'message' => 'Liquor item deleted successfully.']);
            }

            ActionApproval::create([
                'club_id'         => $club_id,
                'module'          => 'liquor_item_delete',
                'action_type'     => 'delete',
                'entity_model'    => 'FoodItem',
                'entity_id'       => $id,
                'maker_user_id'   => Auth::id(),
                'request_payload' => json_encode(['item_id' => $id, 'item_name' => $liquorItem->name]),
                'status'          => 'pending',
            ]);

            return response()->json(['statusCode' => 200, 'message' => 'Delete request submitted for approval.']);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }
}
