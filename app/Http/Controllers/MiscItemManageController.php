<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\MiscCategory;
use App\Models\MiscItem;
use App\Models\MiscItemPrice;
use App\Models\User;
use App\Notifications\ApprovalNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MiscItemManageController extends Controller
{
    public function index()
    {
        try {
            $page_title = 'Manage Misc Items';
            $title      = 'Misc Items List';
            $user       = auth()->user();
            $club_id    = $user->club_id;

            $miscItemsList = MiscItem::where('club_id', $club_id)
                ->with(['miscItemPrice', 'miscItemCat'])
                ->latest()
                ->get();

            $miscCatList = MiscCategory::where('club_id', $club_id)->get();

            $pendingByItem = ActionApproval::where('club_id', $club_id)
                ->whereIn('module', ['misc_item_create', 'misc_item_delete', 'misc_price_update'])
                ->where('status', 'pending')
                ->get(['entity_id', 'module'])
                ->groupBy('entity_id')
                ->map(fn($rows) => $rows->pluck('module')->toArray());

            $pendingCreateIds = $pendingByItem->filter(fn($m) => in_array('misc_item_create', $m))->keys()->toArray();
            $pendingDeleteIds = $pendingByItem->filter(fn($m) => in_array('misc_item_delete', $m))->keys()->toArray();
            $pendingAnyIds    = $pendingByItem->keys()->toArray();

            return view('misc_items.list', compact(
                'miscItemsList', 'miscCatList', 'page_title', 'title',
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
                'itemName'          => ['required', 'string', 'max:255'],
                'itemCat'           => 'required',
                'itemPrice'         => 'required|numeric|min:0|max:9999999999|decimal:0,2',
                'itemImage'         => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
                'itemCode'          => ['required', 'string', 'max:255'],
                'itemstatus'        => 'required|boolean',
                'unit'              => 'nullable|string|max:50',
                'gstPercentage'     => 'required|numeric|min:0|max:100',
                'isPriceEditable'   => 'nullable|boolean',
                'description'       => 'nullable|string|max:500',
            ]);

            $dupName = MiscItem::where('club_id', $club_id)
                ->whereNull('deleted_at')
                ->where('name', $request->itemName)
                ->exists();

            $dupCode = MiscItem::where('club_id', $club_id)
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

            $price = (float) $request->itemPrice;

            $image_path = null;
            if ($request->hasFile('itemImage')) {
                $file       = $request->file('itemImage');
                $filename   = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path       = $file->storeAs('uploads/images', $filename, 'public');
                $image_path = 'storage/' . $path;
            }

            $isAdmin = Auth::user()->hasRole('admin');

            $miscItem = MiscItem::create([
                'club_id'            => $club_id,
                'name'               => $request->itemName,
                'misc_category_id'   => $request->itemCat,
                'image'              => $image_path,
                'code'               => $request->itemCode,
                'description'        => $request->description,
                'is_active'          => $isAdmin ? $request->itemstatus : 0,
                'unit'               => $request->unit ?: 'pcs',
                'gst_percentage'     => $request->gstPercentage,
                'is_price_editable'  => (bool) $request->isPriceEditable,
            ]);

            MiscItemPrice::create([
                'misc_item_id'   => $miscItem->id,
                'price'          => $price,
                'effective_from' => now(),
                'is_active'      => 1,
            ]);

            $payload = [
                'item_id'            => $miscItem->id,
                'item_name'          => $request->itemName,
                'name'               => $request->itemName,
                'misc_category_id'   => $request->itemCat,
                'code'               => $request->itemCode,
                'is_active'          => $request->itemstatus,
                'unit'               => $request->unit ?: 'pcs',
                'gst_percentage'     => $request->gstPercentage,
                'is_price_editable'  => (bool) $request->isPriceEditable,
                'price'              => $price,
                'image'              => $image_path,
            ];

            $approval = ActionApproval::create([
                'club_id'                 => $club_id,
                'module'                  => 'misc_item_create',
                'action_type'             => 'create',
                'entity_model'            => 'MiscItem',
                'entity_id'               => $miscItem->id,
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
                'message'    => $isAdmin ? 'Misc item added successfully.' : 'Misc item submitted for approval.',
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

            $miscItem = MiscItem::with(['miscItemPrice', 'miscItemCat'])
                ->where('club_id', $club_id)
                ->where('id', $id)
                ->firstOrFail();

            $pendingPriceApproval = ActionApproval::where('club_id', $club_id)
                ->where('module', 'misc_price_update')
                ->where('entity_model', 'MiscItem')
                ->where('entity_id', $miscItem->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            return response()->json([
                'data'            => $miscItem,
                'pendingApproval' => $pendingPriceApproval,
                'statusCode'      => 200,
                'message'         => 'Misc item fetched successfully',
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
                'itemName'          => ['required', 'string', 'max:255'],
                'itemCat'           => 'required',
                'itemCode'          => ['required', 'string', 'max:255'],
                'itemstatus'        => 'required|boolean',
                'unit'              => 'nullable|string|max:50',
                'gstPercentage'     => 'required|numeric|min:0|max:100',
                'isPriceEditable'   => 'nullable|boolean',
                'description'       => 'nullable|string|max:500',
            ]);

            $miscItem = MiscItem::where('club_id', $club_id)
                ->where('id', $id)
                ->firstOrFail();

            $dupName = MiscItem::where('club_id', $club_id)
                ->whereNull('deleted_at')
                ->where('name', $request->itemName)
                ->where('id', '!=', $miscItem->id)
                ->exists();

            $dupCode = MiscItem::where('club_id', $club_id)
                ->whereNull('deleted_at')
                ->where('code', $request->itemCode)
                ->where('id', '!=', $miscItem->id)
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

            $image_path = $miscItem->image;

            if ($request->hasFile('itemImage')) {
                if ($miscItem->image && file_exists(public_path($miscItem->image))) {
                    unlink(public_path($miscItem->image));
                }
                $file       = $request->file('itemImage');
                $filename   = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path       = $file->storeAs('uploads/images', $filename, 'public');
                $image_path = 'storage/' . $path;
            }

            $miscItem->update([
                'name'               => $request->itemName,
                'misc_category_id'   => $request->itemCat,
                'image'              => $image_path,
                'code'               => $request->itemCode,
                'description'        => $request->description,
                'is_active'          => $request->itemstatus,
                'unit'               => $request->unit ?: 'pcs',
                'gst_percentage'     => $request->gstPercentage,
                'is_price_editable'  => (bool) $request->isPriceEditable,
            ]);

            DB::commit();

            return response()->json(['statusCode' => 200, 'message' => 'Misc item updated successfully.']);
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

            $miscItem = MiscItem::where('club_id', $club_id)
                ->where('id', $request->item_id)
                ->firstOrFail();

            $currentPrice = MiscItemPrice::where('misc_item_id', $miscItem->id)
                ->where('is_active', 1)
                ->first();

            $payload = [
                'item_id'   => $miscItem->id,
                'item_name' => $miscItem->name,
                'old_price' => $currentPrice?->price ?? 0,
                'new_price' => $request->new_price,
            ];

            if (Auth::user()->hasRole('admin')) {
                DB::beginTransaction();

                if ($currentPrice) {
                    $currentPrice->update(['is_active' => 0, 'effective_to' => now()]);
                }

                MiscItemPrice::create([
                    'misc_item_id'   => $miscItem->id,
                    'price'          => $request->new_price,
                    'effective_from' => now(),
                    'is_active'      => 1,
                ]);

                ActionApproval::create([
                    'club_id'                 => $club_id,
                    'module'                  => 'misc_price_update',
                    'action_type'             => 'update',
                    'entity_model'            => 'MiscItem',
                    'entity_id'               => $miscItem->id,
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
                'module'          => 'misc_price_update',
                'action_type'     => 'update',
                'entity_model'    => 'MiscItem',
                'entity_id'       => $miscItem->id,
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

            $miscItem = MiscItem::where('club_id', $club_id)
                ->where('id', $id)
                ->firstOrFail();

            $pendingApproval = ActionApproval::where('club_id', $club_id)
                ->where('entity_id', $id)
                ->whereIn('module', ['misc_item_create', 'misc_item_delete', 'misc_price_update'])
                ->where('status', 'pending')
                ->first();

            if ($pendingApproval) {
                $label = match($pendingApproval->module) {
                    'misc_item_create'  => 'add',
                    'misc_item_delete'  => 'delete',
                    'misc_price_update' => 'price change',
                    default             => 'approval',
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
                    'module'                  => 'misc_item_delete',
                    'action_type'             => 'delete',
                    'entity_model'            => 'MiscItem',
                    'entity_id'               => $miscItem->id,
                    'maker_user_id'           => Auth::id(),
                    'checker_user_id'         => Auth::id(),
                    'status'                  => 'approved',
                    'approved_or_rejected_at' => now(),
                    'request_payload'         => json_encode(['item_id' => $id, 'item_name' => $miscItem->name]),
                ]);

                $miscItem->delete();

                DB::commit();

                return response()->json(['statusCode' => 200, 'message' => 'Misc item deleted successfully.']);
            }

            $approval = ActionApproval::create([
                'club_id'         => $club_id,
                'module'          => 'misc_item_delete',
                'action_type'     => 'delete',
                'entity_model'    => 'MiscItem',
                'entity_id'       => $miscItem->id,
                'maker_user_id'   => Auth::id(),
                'status'          => 'pending',
                'request_payload' => json_encode(['item_id' => $id, 'item_name' => $miscItem->name]),
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
