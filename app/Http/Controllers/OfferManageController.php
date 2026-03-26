<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\FoodItem;
use App\Models\LiquorServing;
use App\Models\Offer;
use App\Models\OfferItem;
use App\Models\OfferType;
use App\Models\User;
use App\Notifications\ApprovalNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class OfferManageController extends Controller
{
    public function index()
    {
        $page_title = 'Offer Manage';
        $title      = 'Promotions';

        $club_id = auth()->user()->club_id;

        $offers     = Offer::where('club_id', $club_id)
                        ->with(['offerType', 'offerItems.foodItem'])
                        ->latest()
                        ->get();

        $offerTypes = OfferType::all();

        $foodItems  = FoodItem::where('club_id', $club_id)
                        ->where('item_type', 'food')
                        ->where('is_active', 1)
                        ->get(['id', 'name', 'item_type']);

        $beerItems = FoodItem::where('club_id', $club_id)
                        ->where('item_type', 'liquor')
                        ->where('is_beer', 1)
                        ->where('is_active', 1)
                        ->get(['id', 'name'])
                        ->map(fn($item) => ['id' => $item->id, 'name' => $item->name]);

        $spiritServings = LiquorServing::where('club_id', $club_id)
                        ->where('is_active', 1)
                        ->get(['food_item_id', 'name'])
                        ->map(fn($s) => ['id' => $s->food_item_id, 'name' => $s->name]);

        $liquorItems = $beerItems->merge($spiritServings)->unique('id')->values();

        // Item IDs already in a currently active (non-expired) offer
        $activeOfferIds = Offer::where('club_id', $club_id)
                            ->where('status', 'active')
                            ->where('end_at', '>=', now()->toDateString())
                            ->pluck('id');
        $takenItemIds = OfferItem::whereIn('offer_id', $activeOfferIds)
                            ->pluck('food_items_id')->unique()->toArray();

        return view('offers.list', compact('page_title', 'title', 'offers', 'offerTypes', 'foodItems', 'liquorItems', 'takenItemIds'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'          => 'required|string|max:255',
                'offer_type_id' => 'required|exists:offer_types,id',
                'applies_to'    => 'required|in:food,liquor,both',
                'start_at'      => 'required|date',
                'end_at'        => 'required|date|after_or_equal:start_at',
                'items'         => 'required|array|min:1',
                'items.*'       => 'exists:food_items,id',
            ]);

            $offerType = OfferType::findOrFail($request->offer_type_id);

            // Validate discount_value for percentage / flat
            if (in_array($offerType->slug, ['percentage', 'flat'])) {
                $request->validate([
                    'discount_value' => 'required|numeric|min:0',
                ]);
            }

            $isAdmin = Auth::user()->hasRole('admin');

            DB::beginTransaction();

            $offer = Offer::create([
                'club_id'        => auth()->user()->club_id,
                'offer_type_id'  => $request->offer_type_id,
                'name'           => $request->name,
                'applies_to'     => $request->applies_to,
                'discount_value' => $request->discount_value ?? 0,
                'start_at'       => $request->start_at,
                'end_at'         => $request->end_at,
                'status'         => $isAdmin ? 'active' : 'pending',
            ]);

            foreach ($request->items as $itemId) {
                OfferItem::create([
                    'offer_id'      => $offer->id,
                    'food_items_id' => $itemId,
                ]);
            }

            // Admin → auto-approved, no checker needed
            if ($isAdmin) {
                ActionApproval::create([
                    'club_id'                => auth()->user()->club_id,
                    'module'                 => 'offer',
                    'action_type'            => 'create',
                    'entity_model'           => Offer::class,
                    'entity_id'              => $offer->id,
                    'maker_user_id'          => Auth::id(),
                    'checker_user_id'        => Auth::id(),
                    'status'                 => 'approved',
                    'approved_or_rejected_at'=> now(),
                    'request_payload'        => json_encode([
                        'offer_id'       => $offer->id,
                        'name'           => $offer->name,
                        'offer_type'     => $offerType->name,
                        'applies_to'     => $offer->applies_to,
                        'discount_value' => $offer->discount_value,
                        'start_at'       => $offer->start_at,
                        'end_at'         => $offer->end_at,
                        'items'          => $request->items,
                    ]),
                ]);
            } else {
                $approval = ActionApproval::create([
                    'club_id'         => auth()->user()->club_id,
                    'module'          => 'offer',
                    'action_type'     => 'create',
                    'entity_model'    => Offer::class,
                    'entity_id'       => $offer->id,
                    'maker_user_id'   => Auth::id(),
                    'status'          => 'pending',
                    'request_payload' => json_encode([
                        'offer_id'       => $offer->id,
                        'name'           => $offer->name,
                        'offer_type'     => $offerType->name,
                        'applies_to'     => $offer->applies_to,
                        'discount_value' => $offer->discount_value,
                        'start_at'       => $offer->start_at,
                        'end_at'         => $offer->end_at,
                        'items'          => $request->items,
                    ]),
                ]);

                $approvers = User::role(['operator', 'admin'])
                    ->where('id', '!=', Auth::id())
                    ->get();
                Notification::send($approvers, new ApprovalNotification($approval));
            }

            DB::commit();

            $message = $isAdmin ? 'Offer created successfully.' : 'Offer submitted for approval.';
            return response()->json(['statusCode' => 200, 'message' => $message]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['statusCode' => 422, 'error' => collect($e->errors())->flatten()->first()]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $e->getMessage()]);
        }
    }

    public function edit(string $id)
    {
        try {
            $offer = Offer::with(['offerType', 'offerItems'])
                ->where('club_id', auth()->user()->club_id)
                ->findOrFail($id);

            $hasPending = ActionApproval::where('entity_id', $offer->id)
                ->where('entity_model', Offer::class)
                ->where('status', 'pending')
                ->exists();

            if ($hasPending) {
                return response()->json([
                    'statusCode' => 423,
                    'message'    => 'This offer has a pending approval request. Please wait for it to be processed before making changes.',
                ]);
            }

            return response()->json([
                'statusCode' => 200,
                'data' => [
                    'id'              => $offer->id,
                    'name'            => $offer->name,
                    'offer_type_id'   => $offer->offer_type_id,
                    'offer_type_slug' => $offer->offerType?->slug,
                    'applies_to'      => $offer->applies_to,
                    'discount_value'  => $offer->discount_value,
                    'start_at'        => $offer->start_at,
                    'end_at'          => $offer->end_at,
                    'item_ids'        => $offer->offerItems->pluck('food_items_id')->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['statusCode' => 500, 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $offer = Offer::with(['offerType', 'offerItems.foodItem'])
                ->where('club_id', auth()->user()->club_id)
                ->findOrFail($id);

            $hasPending = ActionApproval::where('entity_id', $offer->id)
                ->where('entity_model', Offer::class)
                ->where('status', 'pending')
                ->exists();

            if ($hasPending) {
                return response()->json(['statusCode' => 423, 'error' => 'This offer has a pending approval request.']);
            }

            $request->validate([
                'name'          => 'required|string|max:255',
                'offer_type_id' => 'required|exists:offer_types,id',
                'applies_to'    => 'required|in:food,liquor,both',
                'start_at'      => 'required|date',
                'end_at'        => 'required|date|after_or_equal:start_at',
                'items'         => 'required|array|min:1',
                'items.*'       => 'exists:food_items,id',
            ]);

            $newOfferType = OfferType::findOrFail($request->offer_type_id);

            if (in_array($newOfferType->slug, ['percentage', 'flat'])) {
                $request->validate(['discount_value' => 'required|numeric|min:0']);
            }

            $isAdmin  = Auth::user()->hasRole('admin');
            $oldItems = $offer->offerItems->pluck('food_items_id')->toArray();

            $payload = [
                'offer_id' => $offer->id,
                'old'      => [
                    'name'           => $offer->name,
                    'offer_type_id'  => $offer->offer_type_id,
                    'offer_type'     => $offer->offerType?->name,
                    'applies_to'     => $offer->applies_to,
                    'discount_value' => $offer->discount_value,
                    'start_at'       => $offer->start_at,
                    'end_at'         => $offer->end_at,
                    'items'          => $oldItems,
                ],
                'new'      => [
                    'name'           => $request->name,
                    'offer_type_id'  => $request->offer_type_id,
                    'offer_type'     => $newOfferType->name,
                    'applies_to'     => $request->applies_to,
                    'discount_value' => $request->discount_value ?? 0,
                    'start_at'       => $request->start_at,
                    'end_at'         => $request->end_at,
                    'items'          => $request->items,
                ],
            ];

            // Admin → apply immediately
            if ($isAdmin) {
                DB::beginTransaction();

                $offer->update([
                    'name'           => $request->name,
                    'offer_type_id'  => $request->offer_type_id,
                    'applies_to'     => $request->applies_to,
                    'discount_value' => $request->discount_value ?? 0,
                    'start_at'       => $request->start_at,
                    'end_at'         => $request->end_at,
                ]);

                OfferItem::where('offer_id', $offer->id)->delete();
                foreach ($request->items as $itemId) {
                    OfferItem::create(['offer_id' => $offer->id, 'food_items_id' => $itemId]);
                }

                ActionApproval::create([
                    'club_id'                => auth()->user()->club_id,
                    'module'                 => 'offer',
                    'action_type'            => 'update',
                    'entity_model'           => Offer::class,
                    'entity_id'              => $offer->id,
                    'maker_user_id'          => Auth::id(),
                    'checker_user_id'        => Auth::id(),
                    'status'                 => 'approved',
                    'approved_or_rejected_at'=> now(),
                    'request_payload'        => json_encode($payload),
                ]);

                DB::commit();

                return response()->json(['statusCode' => 200, 'message' => 'Offer updated successfully.']);
            }

            // Operator → submit for approval
            $approval = ActionApproval::create([
                'club_id'         => auth()->user()->club_id,
                'module'          => 'offer',
                'action_type'     => 'update',
                'entity_model'    => Offer::class,
                'entity_id'       => $offer->id,
                'maker_user_id'   => Auth::id(),
                'status'          => 'pending',
                'request_payload' => json_encode($payload),
            ]);

            $approvers = User::role(['operator', 'admin'])
                ->where('id', '!=', Auth::id())
                ->get();
            Notification::send($approvers, new ApprovalNotification($approval));

            return response()->json(['statusCode' => 200, 'message' => 'Edit request submitted for approval.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['statusCode' => 422, 'error' => collect($e->errors())->flatten()->first()]);
        } catch (\Exception $e) {
            return response()->json(['statusCode' => 500, 'error' => $e->getMessage()]);
        }
    }

    public function destroy(string $id)
    {
        try {
            $offer = Offer::with(['offerType', 'offerItems.foodItem'])
                ->where('club_id', auth()->user()->club_id)
                ->findOrFail($id);

            $hasPending = ActionApproval::where('entity_id', $offer->id)
                ->where('entity_model', Offer::class)
                ->where('status', 'pending')
                ->exists();

            if ($hasPending) {
                return response()->json(['statusCode' => 423, 'error' => 'This offer has a pending approval request. Cannot submit delete until it is resolved.']);
            }

            $isAdmin  = Auth::user()->hasRole('admin');
            $deletePayload = [
                'offer_id'       => $offer->id,
                'name'           => $offer->name,
                'offer_type'     => $offer->offerType?->name,
                'applies_to'     => $offer->applies_to,
                'discount_value' => $offer->discount_value,
                'start_at'       => $offer->start_at,
                'end_at'         => $offer->end_at,
                'items'          => $offer->offerItems->pluck('food_items_id')->toArray(),
            ];

            // Admin → delete immediately
            if ($isAdmin) {
                DB::beginTransaction();

                ActionApproval::create([
                    'club_id'                => auth()->user()->club_id,
                    'module'                 => 'offer',
                    'action_type'            => 'delete',
                    'entity_model'           => Offer::class,
                    'entity_id'              => $offer->id,
                    'maker_user_id'          => Auth::id(),
                    'checker_user_id'        => Auth::id(),
                    'status'                 => 'approved',
                    'approved_or_rejected_at'=> now(),
                    'request_payload'        => json_encode($deletePayload),
                ]);

                OfferItem::where('offer_id', $offer->id)->delete();
                $offer->delete();

                DB::commit();

                return response()->json(['statusCode' => 200, 'message' => 'Offer deleted successfully.']);
            }

            // Operator → submit for approval
            $approval = ActionApproval::create([
                'club_id'         => auth()->user()->club_id,
                'module'          => 'offer',
                'action_type'     => 'delete',
                'entity_model'    => Offer::class,
                'entity_id'       => $offer->id,
                'maker_user_id'   => Auth::id(),
                'status'          => 'pending',
                'request_payload' => json_encode($deletePayload),
            ]);

            $approvers = User::role(['operator', 'admin'])
                ->where('id', '!=', Auth::id())
                ->get();
            Notification::send($approvers, new ApprovalNotification($approval));

            return response()->json(['statusCode' => 200, 'message' => 'Delete request submitted for approval.']);
        } catch (\Exception $e) {
            return response()->json(['statusCode' => 500, 'error' => $e->getMessage()]);
        }
    }
}
