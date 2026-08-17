<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\FoodCategory;
use App\Models\FoodItem;
use App\Models\FoodItemPrice;
use App\Models\User;
use App\Notifications\ApprovalNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class BeverageItemManageController extends Controller
{
    public function index()
    {
        try {
            $page_title = 'Manage Beverage Items';
            $title      = 'Beverage Items List';
            $user       = auth()->user();
            $club_id    = $user->club_id;

            $beverageItemsList = FoodItem::where('club_id', $club_id)
                ->with(['foodItemPrice', 'foodItemCat'])
                ->where('item_type', 'beverage')
                ->latest()
                ->get();

            $beverageCatList = FoodCategory::where('club_id', $club_id)
                ->where('item_type', 'beverage')
                ->get();

            $pendingByItem = ActionApproval::where('club_id', $club_id)
                ->whereIn('module', ['beverage_item_create', 'beverage_item_delete', 'beverage_price_update'])
                ->where('status', 'pending')
                ->get(['entity_id', 'module'])
                ->groupBy('entity_id')
                ->map(fn($rows) => $rows->pluck('module')->toArray());

            $pendingCreateIds = $pendingByItem->filter(fn($m) => in_array('beverage_item_create', $m))->keys()->toArray();
            $pendingDeleteIds = $pendingByItem->filter(fn($m) => in_array('beverage_item_delete', $m))->keys()->toArray();
            $pendingAnyIds    = $pendingByItem->keys()->toArray();

            return view('beverage_items.list', compact(
                'beverageItemsList', 'beverageCatList', 'page_title', 'title',
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
                'itemName'            => ['required', 'string', 'max:255'],
                'itemCat'             => 'required',
                'itemPrice'           => 'required|numeric|min:0|max:9999999999|decimal:0,2',
                'itemImage'           => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
                'itemCode'            => ['required', 'string', 'max:255'],
                'itemstatus'          => 'required|boolean',
                'size_ml'             => 'required|numeric|min:1',
                'low_stock_alert_qty' => 'nullable|numeric|min:0',
            ]);

            $dupName = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'beverage')
                ->whereNull('deleted_at')
                ->where('name', $request->itemName)
                ->exists();

            $dupCode = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'beverage')
                ->whereNull('deleted_at')
                ->where('code', $request->itemCode)
                ->exists();

            if ($dupName || $dupCode) {
                $message = $dupName && $dupCode
                    ? 'Item name and code already exist.'
                    : ($dupName ? 'Item name already exists.' : 'Item code already exists.');

                return response()->json([
                    'statusCode' => 409,
                    'message'    => $message,
                ]);
            }

            $sizeMl = (int) $request->size_ml;
            $price  = (float) $request->itemPrice;

            $image_path = null;
            if ($request->hasFile('itemImage')) {
                $file       = $request->file('itemImage');
                $filename   = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path       = $file->storeAs('uploads/images', $filename, 'public');
                $image_path = 'storage/' . $path;
            }

            $isAdmin = Auth::user()->hasRole('admin');

            // Beverages are always sold as a whole unit (bottle/can/glass) — never a
            // partial pour — so they reuse the same "whole-unit" storage shape as beer.
            $beverageItem = FoodItem::create([
                'club_id'             => $club_id,
                'name'                => $request->itemName,
                'category_id'         => $request->itemCat,
                'item_type'           => 'beverage',
                'image'               => $image_path,
                'code'                => $request->itemCode,
                'is_active'           => $isAdmin ? $request->itemstatus : 0,
                'unit'                => 'bottle',
                'size_ml'             => $sizeMl,
                'is_beer'             => true,
                'low_stock_alert_qty' => $request->low_stock_alert_qty,
            ]);

            FoodItemPrice::create([
                'item_id'        => $beverageItem->id,
                'price'          => $price,
                'effective_from' => now(),
                'is_active'      => 1,
            ]);

            $payload = [
                'item_id'             => $beverageItem->id,
                'name'                => $request->itemName,
                'category_id'         => $request->itemCat,
                'code'                => $request->itemCode,
                'is_active'           => $request->itemstatus,
                'size_ml'             => $sizeMl,
                'unit'                => 'bottle',
                'low_stock_alert_qty' => $request->low_stock_alert_qty,
                'price'               => $price,
                'image'               => $image_path,
            ];

            $approval = ActionApproval::create([
                'club_id'                 => $club_id,
                'module'                  => 'beverage_item_create',
                'action_type'             => 'create',
                'entity_model'            => 'FoodItem',
                'entity_id'               => $beverageItem->id,
                'maker_user_id'           => Auth::id(),
                'checker_user_id'         => $isAdmin ? Auth::id() : null,
                'request_payload'         => json_encode($payload),
                'status'                  => $isAdmin ? 'approved' : 'pending',
                'approved_or_rejected_at' => $isAdmin ? now() : null,
            ]);

            DB::commit();

            if (!$isAdmin) {
                $approvers = User::role(['operator', 'admin'])->where('id', '!=', Auth::id())->get();
                Notification::send($approvers, new ApprovalNotification($approval));
            }

            return response()->json([
                'statusCode' => 200,
                'message'    => $isAdmin ? 'Beverage item added successfully.' : 'Beverage item submitted for approval.',
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

            $beverageItem = FoodItem::with(['foodItemPrice', 'foodItemCat'])
                ->where('club_id', $club_id)
                ->where('item_type', 'beverage')
                ->where('id', $id)
                ->firstOrFail();

            $pendingPriceApproval = ActionApproval::where('club_id', $club_id)
                ->where('module', 'beverage_price_update')
                ->where('entity_model', 'FoodItem')
                ->where('entity_id', $beverageItem->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            return response()->json([
                'data'            => $beverageItem,
                'pendingApproval' => $pendingPriceApproval,
                'statusCode'      => 200,
                'message'         => 'Beverage item fetched successfully',
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
                'itemName'            => ['required', 'string', 'max:255'],
                'itemCat'             => 'required',
                'itemCode'            => ['required', 'string', 'max:255'],
                'itemstatus'          => 'required|boolean',
                'size_ml'             => 'required|numeric|min:1',
                'low_stock_alert_qty' => 'nullable|numeric|min:0',
            ]);

            $beverageItem = FoodItem::where('club_id', $club_id)
                ->where('id', $id)
                ->where('item_type', 'beverage')
                ->firstOrFail();

            $dupName = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'beverage')
                ->whereNull('deleted_at')
                ->where('name', $request->itemName)
                ->where('id', '!=', $beverageItem->id)
                ->exists();

            $dupCode = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'beverage')
                ->whereNull('deleted_at')
                ->where('code', $request->itemCode)
                ->where('id', '!=', $beverageItem->id)
                ->exists();

            if ($dupName || $dupCode) {
                $message = $dupName && $dupCode
                    ? 'Item name and code already exist.'
                    : ($dupName ? 'Item name already exists.' : 'Item code already exists.');

                return response()->json([
                    'statusCode' => 409,
                    'message'    => $message,
                ]);
            }

            $sizeMl     = (int) $request->size_ml;
            $image_path = $beverageItem->image;

            if ($request->hasFile('itemImage')) {
                if ($beverageItem->image && file_exists(public_path($beverageItem->image))) {
                    unlink(public_path($beverageItem->image));
                }
                $file       = $request->file('itemImage');
                $filename   = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path       = $file->storeAs('uploads/images', $filename, 'public');
                $image_path = 'storage/' . $path;
            }

            $beverageItem->update([
                'name'                => $request->itemName,
                'category_id'         => $request->itemCat,
                'image'               => $image_path,
                'code'                => $request->itemCode,
                'is_active'           => $request->itemstatus,
                'size_ml'             => $sizeMl,
                'low_stock_alert_qty' => $request->low_stock_alert_qty,
            ]);

            DB::commit();

            return response()->json(['statusCode' => 200, 'message' => 'Beverage item updated successfully.']);
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

            $beverageItem = FoodItem::where('club_id', $club_id)
                ->where('id', $request->item_id)
                ->where('item_type', 'beverage')
                ->firstOrFail();

            $currentPrice = FoodItemPrice::where('item_id', $beverageItem->id)
                ->where('is_active', 1)
                ->first();

            $payload = [
                'item_id'   => $beverageItem->id,
                'old_price' => $currentPrice?->price ?? 0,
                'new_price' => $request->new_price,
            ];

            if (Auth::user()->hasRole('admin')) {
                DB::beginTransaction();

                if ($currentPrice) {
                    $currentPrice->update(['is_active' => 0, 'effective_to' => now()]);
                }

                FoodItemPrice::create([
                    'item_id'        => $beverageItem->id,
                    'price'          => $request->new_price,
                    'effective_from' => now(),
                    'is_active'      => 1,
                ]);

                ActionApproval::create([
                    'club_id'                 => $club_id,
                    'module'                  => 'beverage_price_update',
                    'action_type'             => 'update',
                    'entity_model'            => 'FoodItem',
                    'entity_id'               => $beverageItem->id,
                    'maker_user_id'           => Auth::id(),
                    'checker_user_id'         => Auth::id(),
                    'request_payload'         => json_encode($payload),
                    'status'                  => 'approved',
                    'approved_or_rejected_at' => now(),
                ]);

                DB::commit();

                return response()->json(['statusCode' => 200, 'message' => 'Price updated successfully.']);
            }

            $approval = ActionApproval::create([
                'club_id'         => $club_id,
                'module'          => 'beverage_price_update',
                'action_type'     => 'update',
                'entity_model'    => 'FoodItem',
                'entity_id'       => $beverageItem->id,
                'maker_user_id'   => Auth::id(),
                'request_payload' => json_encode($payload),
                'status'          => 'pending',
            ]);

            $approvers = User::role(['operator', 'admin'])->where('id', '!=', Auth::id())->get();
            Notification::send($approvers, new ApprovalNotification($approval));

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

            $beverageItem = FoodItem::where('club_id', $club_id)
                ->where('item_type', 'beverage')
                ->where('id', $id)
                ->firstOrFail();

            $pendingApproval = ActionApproval::where('club_id', $club_id)
                ->where('entity_id', $id)
                ->whereIn('module', ['beverage_item_create', 'beverage_item_delete', 'beverage_price_update'])
                ->where('status', 'pending')
                ->first();

            if ($pendingApproval) {
                $label = match($pendingApproval->module) {
                    'beverage_item_create'  => 'add',
                    'beverage_item_delete'  => 'delete',
                    'beverage_price_update' => 'price change',
                    default                 => 'approval',
                };

                return response()->json([
                    'statusCode' => 423,
                    'message'    => "This item already has a pending {$label} request. Please wait for it to be processed first.",
                ]);
            }

            $isAdmin = Auth::user()->hasRole('admin');

            if ($isAdmin) {
                DB::beginTransaction();

                ActionApproval::create([
                    'club_id'                 => $club_id,
                    'module'                  => 'beverage_item_delete',
                    'action_type'             => 'delete',
                    'entity_model'            => 'FoodItem',
                    'entity_id'               => $beverageItem->id,
                    'maker_user_id'           => Auth::id(),
                    'checker_user_id'         => Auth::id(),
                    'status'                  => 'approved',
                    'approved_or_rejected_at' => now(),
                ]);

                $beverageItem->delete();

                DB::commit();

                return response()->json(['statusCode' => 200, 'message' => 'Beverage item deleted successfully.']);
            }

            $approval = ActionApproval::create([
                'club_id'         => $club_id,
                'module'          => 'beverage_item_delete',
                'action_type'     => 'delete',
                'entity_model'    => 'FoodItem',
                'entity_id'       => $beverageItem->id,
                'maker_user_id'   => Auth::id(),
                'status'          => 'pending',
            ]);

            $approvers = User::role(['operator', 'admin'])->where('id', '!=', Auth::id())->get();
            Notification::send($approvers, new ApprovalNotification($approval));

            return response()->json(['statusCode' => 200, 'message' => 'Delete request submitted for approval.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }
}
