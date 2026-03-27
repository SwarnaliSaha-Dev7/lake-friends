<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\FoodItem;
use App\Models\LiquorServing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ApprovalNotification;

class LiquorServingController extends Controller
{
    public function index()
    {
        try {
            $clubId     = club_id();
            $page_title = 'Liquor Menu';
            $title      = 'Liquor Menu';

            $liquorItems = FoodItem::where('club_id', $clubId)
                ->where('item_type', 'liquor')
                ->where('is_beer', 0)
                ->where('is_active', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'size_ml']);

            $servings = LiquorServing::where('club_id', $clubId)
                ->with('foodItem')
                ->latest()
                ->get();

            $pendingByServing = ActionApproval::where('club_id', $clubId)
                ->whereIn('module', ['liquor_serving_create', 'liquor_serving_update', 'liquor_serving_delete'])
                ->where('status', 'pending')
                ->get(['entity_id', 'module'])
                ->groupBy('entity_id')
                ->map(fn($rows) => $rows->pluck('module')->toArray());

            $pendingCreateIds = $pendingByServing->filter(fn($m) => in_array('liquor_serving_create', $m))->keys()->toArray();
            $pendingUpdateIds = $pendingByServing->filter(fn($m) => in_array('liquor_serving_update', $m))->keys()->toArray();
            $pendingDeleteIds = $pendingByServing->filter(fn($m) => in_array('liquor_serving_delete', $m))->keys()->toArray();
            $pendingAnyIds    = $pendingByServing->keys()->toArray();

            return view('liquor_servings.index', compact(
                'servings', 'liquorItems', 'page_title', 'title',
                'pendingCreateIds', 'pendingUpdateIds', 'pendingDeleteIds', 'pendingAnyIds'
            ));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $clubId  = club_id();
            $isAdmin = Auth::user()->hasRole('admin');

            $request->validate([
                'food_item_id' => 'required|integer',
                'volume_ml'    => 'required|integer|min:1',
                'price'        => 'required|numeric|min:0|decimal:0,2',
            ]);

            $foodItem = FoodItem::where('club_id', $clubId)
                ->where('id', $request->food_item_id)
                ->where('item_type', 'liquor')
                ->where('is_beer', 0)
                ->firstOrFail();

            $duplicate = LiquorServing::where('club_id', $clubId)
                ->where('food_item_id', $foodItem->id)
                ->where('volume_ml', $request->volume_ml)
                ->exists();

            if ($duplicate) {
                return response()->json(['statusCode' => 422, 'message' => $foodItem->name . ' ' . $request->volume_ml . 'ml already exists.']);
            }

            $name = $foodItem->name . ' ' . $request->volume_ml . 'ml';

            $serving = LiquorServing::create([
                'club_id'      => $clubId,
                'food_item_id' => $foodItem->id,
                'name'         => $name,
                'volume_ml'    => $request->volume_ml,
                'price'        => $request->price,
                'is_active'    => $isAdmin ? 1 : 0,
                'created_by'   => Auth::id(),
            ]);

            $payload = [
                'serving_id'   => $serving->id,
                'name'         => $name,
                'food_item_id' => $foodItem->id,
                'item_name'    => $foodItem->name,
                'volume_ml'    => $request->volume_ml,
                'price'        => $request->price,
            ];

            $approval = ActionApproval::create([
                'club_id'                 => $clubId,
                'module'                  => 'liquor_serving_create',
                'action_type'             => 'create',
                'entity_model'            => 'LiquorServing',
                'entity_id'               => $serving->id,
                'maker_user_id'           => Auth::id(),
                'checker_user_id'         => $isAdmin ? Auth::id() : null,
                'request_payload'         => json_encode($payload),
                'status'                  => $isAdmin ? 'approved' : 'pending',
                'approved_or_rejected_at' => $isAdmin ? now() : null,
            ]);

            if (!$isAdmin) {
                $recipients = User::role(['operator', 'admin'])
                    ->where('id', '!=', Auth::id())
                    ->get();
                Notification::send($recipients, new ApprovalNotification($approval));
            }

            DB::commit();

            return response()->json([
                'statusCode' => 200,
                'message'    => $isAdmin ? 'Liquor menu item added successfully.' : 'Liquor menu item submitted for approval.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function edit($id)
    {
        try {
            $clubId  = club_id();
            $serving = LiquorServing::where('club_id', $clubId)->with('foodItem')->findOrFail($id);

            $pendingApproval = ActionApproval::where('club_id', $clubId)
                ->where('entity_id', $id)
                ->whereIn('module', ['liquor_serving_create', 'liquor_serving_update', 'liquor_serving_delete'])
                ->where('status', 'pending')
                ->latest()
                ->first();

            return response()->json([
                'statusCode'      => 200,
                'data'            => $serving,
                'pendingApproval' => $pendingApproval,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $clubId  = club_id();
            $isAdmin = Auth::user()->hasRole('admin');

            $request->validate([
                'volume_ml' => 'required|integer|min:1',
                'price'     => 'required|numeric|min:0|decimal:0,2',
            ]);

            $serving = LiquorServing::where('club_id', $clubId)->with('foodItem')->findOrFail($id);

            $pendingApproval = ActionApproval::where('club_id', $clubId)
                ->where('entity_id', $id)
                ->whereIn('module', ['liquor_serving_create', 'liquor_serving_update', 'liquor_serving_delete'])
                ->where('status', 'pending')
                ->first();

            if ($pendingApproval) {
                return response()->json([
                    'statusCode' => 422,
                    'message'    => 'This item already has a pending request. Please wait for it to be approved or rejected.',
                ]);
            }

            // Duplicate check: same item + volume must not exist (excluding current record)
            $duplicate = LiquorServing::where('club_id', $clubId)
                ->where('food_item_id', $serving->food_item_id)
                ->where('volume_ml', $request->volume_ml)
                ->where('id', '!=', $id)
                ->exists();

            if ($duplicate) {
                DB::rollBack();
                return response()->json([
                    'statusCode' => 422,
                    'message'    => 'A serving with this item and volume already exists.',
                ]);
            }

            $newName = $serving->foodItem->name . ' ' . $request->volume_ml . 'ml';

            $serving->fill([
                'name'      => $newName,
                'volume_ml' => $request->volume_ml,
                'price'     => $request->price,
            ]);

            if (!$serving->isDirty()) {
                DB::rollBack();
                return response()->json(['statusCode' => 200, 'message' => 'No changes were made']);
            }

            $payload = [
                'serving_id' => $serving->id,
                'item_name'  => $serving->foodItem->name ?? '—',
                'old'        => [
                    'name'      => $serving->getOriginal('name'),
                    'volume_ml' => $serving->getOriginal('volume_ml'),
                    'price'     => $serving->getOriginal('price'),
                ],
                'new'        => [
                    'name'      => $newName,
                    'volume_ml' => $request->volume_ml,
                    'price'     => $request->price,
                ],
            ];

            if ($isAdmin) {
                $serving->update([
                    'name'      => $newName,
                    'volume_ml' => $request->volume_ml,
                    'price'     => $request->price,
                ]);

                ActionApproval::create([
                    'club_id'                 => $clubId,
                    'module'                  => 'liquor_serving_update',
                    'action_type'             => 'update',
                    'entity_model'            => 'LiquorServing',
                    'entity_id'               => $serving->id,
                    'maker_user_id'           => Auth::id(),
                    'checker_user_id'         => Auth::id(),
                    'request_payload'         => json_encode($payload),
                    'status'                  => 'approved',
                    'approved_or_rejected_at' => now(),
                ]);

                DB::commit();
                return response()->json(['statusCode' => 200, 'message' => 'Liquor menu item updated successfully.']);
            }

            $approval = ActionApproval::create([
                'club_id'         => $clubId,
                'module'          => 'liquor_serving_update',
                'action_type'     => 'update',
                'entity_model'    => 'LiquorServing',
                'entity_id'       => $serving->id,
                'maker_user_id'   => Auth::id(),
                'request_payload' => json_encode($payload),
                'status'          => 'pending',
            ]);

            $recipients = User::role(['operator', 'admin'])
                ->where('id', '!=', Auth::id())
                ->get();
            Notification::send($recipients, new ApprovalNotification($approval));

            DB::commit();
            return response()->json(['statusCode' => 200, 'message' => 'Update request submitted for approval.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $clubId  = club_id();
            $isAdmin = Auth::user()->hasRole('admin');

            $serving = LiquorServing::where('club_id', $clubId)->findOrFail($id);

            $pendingApproval = ActionApproval::where('club_id', $clubId)
                ->where('entity_id', $id)
                ->whereIn('module', ['liquor_serving_create', 'liquor_serving_update', 'liquor_serving_delete'])
                ->where('status', 'pending')
                ->first();

            if ($pendingApproval) {
                return response()->json([
                    'statusCode' => 422,
                    'message'    => 'This item already has a pending request. Please wait for it to be approved or rejected.',
                ]);
            }

            if ($isAdmin) {
                $serving->delete();
                return response()->json(['statusCode' => 200, 'message' => 'Liquor menu item deleted successfully.']);
            }

            $approval = ActionApproval::create([
                'club_id'         => $clubId,
                'module'          => 'liquor_serving_delete',
                'action_type'     => 'delete',
                'entity_model'    => 'LiquorServing',
                'entity_id'       => $id,
                'maker_user_id'   => Auth::id(),
                'request_payload' => json_encode(['serving_id' => $id, 'item_name' => $serving->name]),
                'status'          => 'pending',
            ]);

            $recipients = User::role(['operator', 'admin'])
                ->where('id', '!=', Auth::id())
                ->get();
            Notification::send($recipients, new ApprovalNotification($approval));

            return response()->json(['statusCode' => 200, 'message' => 'Delete request submitted for approval.']);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }
}
