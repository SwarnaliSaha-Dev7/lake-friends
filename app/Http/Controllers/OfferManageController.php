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
    // Resolves one item-picker row key back to the real food_items_id it
    // represents plus the exact menu label the admin saw and picked — both get
    // stored on the OfferItem so re-opening the edit form can highlight
    // precisely that row again, not every row that shares the same underlying
    // item. Liquor rows use "srv_{serving_id}" / "item_{food_item_id}" (see
    // index()); food rows are still a plain food_item_id, unchanged.
    private function resolvePickerKey(string $key, int $clubId): ?array
    {
        if (ctype_digit($key)) {
            $item = FoodItem::where('club_id', $clubId)->find((int) $key);
            return $item ? ['food_items_id' => $item->id, 'menu_label' => $item->name, 'picker_key' => $key] : null;
        }

        if (str_starts_with($key, 'srv_')) {
            $serving = LiquorServing::where('club_id', $clubId)
                ->where('id', (int) substr($key, 4))
                ->with('foodItem')
                ->first();
            if (!$serving || !$serving->foodItem) {
                return null;
            }
            return ['food_items_id' => $serving->food_item_id, 'menu_label' => $serving->name, 'picker_key' => $key];
        }

        if (str_starts_with($key, 'item_')) {
            $item = FoodItem::where('club_id', $clubId)->find((int) substr($key, 5));
            if (!$item) {
                return null;
            }
            return ['food_items_id' => $item->id, 'menu_label' => $item->name, 'picker_key' => $key];
        }

        return null;
    }

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

        // One row per Liquor Menu entry (each peg size / cocktail is its own
        // pickable row, named exactly as it appears on the Liquor Menu) — plus
        // one row for any liquor item that has no menu entry configured yet
        // (using its raw catalog name, since there's no menu name to show).
        // Every row gets a UNIQUE id ("srv_{serving_id}" / "item_{food_item_id}")
        // distinct from every other row, even ones on the same base item — so
        // picking exactly one menu entry only ever selects that one row, not
        // every row that happens to share the same underlying item. The real
        // food_item_id each row resolves to travels alongside for the "already
        // in an active offer" check, which still applies per base item.
        $itemsWithServing = LiquorServing::where('club_id', $club_id)
                        ->where('is_active', 1)
                        ->with('foodItem')
                        ->get()
                        ->filter(fn($s) => $s->foodItem && $s->foodItem->item_type === 'liquor')
                        ->map(fn($s) => ['id' => 'srv_' . $s->id, 'food_item_id' => $s->food_item_id, 'name' => $s->name]);

        $servedItemIds = $itemsWithServing->pluck('food_item_id')->unique();

        $itemsWithoutServing = FoodItem::where('club_id', $club_id)
                        ->where('item_type', 'liquor')
                        ->where('is_active', 1)
                        ->whereNotIn('id', $servedItemIds)
                        ->get(['id', 'name'])
                        ->map(fn($item) => ['id' => 'item_' . $item->id, 'food_item_id' => $item->id, 'name' => $item->name]);

        $liquorItems = $itemsWithServing->concat($itemsWithoutServing)->values();

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
                'items.*'       => 'string',
            ]);

            // Resolve every picked row key to its real item + the exact menu
            // label the admin saw, then de-duplicate by that resolved item so
            // picking two rows that happen to be the same underlying item (e.g.
            // both the 30ml and 60ml peg of the same spirit) doesn't create two
            // links — but two DIFFERENT items keep their own distinct labels.
            $clubId = auth()->user()->club_id;
            $resolvedItems = collect($request->items)
                ->map(fn($key) => $this->resolvePickerKey((string) $key, $clubId))
                ->filter()
                ->unique('food_items_id')
                ->values();

            if ($resolvedItems->isEmpty()) {
                return response()->json(['statusCode' => 422, 'error' => 'One or more selected items are no longer available.']);
            }

            $offerType = OfferType::findOrFail($request->offer_type_id);

            // Validate discount_value for percentage / flat
            if (in_array($offerType->slug, ['percentage', 'flat'])) {
                $request->validate([
                    'discount_value' => 'required|numeric|min:0',
                ]);
            }

            // Validate buy/get quantities for b1g1
            if ($offerType->slug === 'b1g1') {
                $request->validate([
                    'buy_qty'   => 'required|integer|min:1',
                    'get_qty'   => 'required|integer|min:1',
                    'volume_ml' => 'nullable|integer|min:1',
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
                'buy_qty'        => $offerType->slug === 'b1g1' ? $request->buy_qty : null,
                'get_qty'        => $offerType->slug === 'b1g1' ? $request->get_qty : null,
                'start_at'       => $request->start_at,
                'end_at'         => $request->end_at,
                'status'         => $isAdmin ? 'active' : 'pending',
            ]);

            // A volume_ml scope (e.g. "only the 60ml peg") is optional and applies
            // uniformly to every selected item — a blank value means the offer
            // covers every serving size of the item, same as before this existed.
            $volumeRule = ($offerType->slug === 'b1g1' && $request->filled('volume_ml'))
                ? (int) $request->volume_ml
                : null;
            foreach ($resolvedItems as $resolved) {
                OfferItem::create([
                    'offer_id'      => $offer->id,
                    'food_items_id' => $resolved['food_items_id'],
                    'rules'         => array_filter([
                        'volume_ml'  => $volumeRule,
                        'menu_label' => $resolved['menu_label'],
                        'picker_key' => $resolved['picker_key'],
                    ], fn($v) => $v !== null),
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
                        'buy_qty'        => $offer->buy_qty,
                        'get_qty'        => $offer->get_qty,
                        'volume_ml'      => $itemRules['volume_ml'] ?? null,
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
                        'buy_qty'        => $offer->buy_qty,
                        'get_qty'        => $offer->get_qty,
                        'volume_ml'      => $itemRules['volume_ml'] ?? null,
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
                    'buy_qty'         => $offer->buy_qty,
                    'get_qty'         => $offer->get_qty,
                    // The volume_ml scope is set uniformly across every item on
                    // the offer (see store()/update()), so the first item's rule
                    // represents the whole offer's scope.
                    'volume_ml'       => $offer->offerItems->first()?->rules['volume_ml'] ?? null,
                    'start_at'        => $offer->start_at,
                    'end_at'          => $offer->end_at,
                    // Pre-select the exact menu-entry row each item was originally
                    // picked as (see resolvePickerKey()). Older offers saved before
                    // that existed have no picker_key on record — best-effort
                    // fall back to any current serving of that item (or the plain
                    // item row if it has none), so the link still shows selected
                    // instead of silently vanishing the next time this gets saved.
                    'item_ids'        => $offer->offerItems->map(function ($oi) {
                        if (!empty($oi->rules['picker_key'])) {
                            return $oi->rules['picker_key'];
                        }
                        $fallbackServing = LiquorServing::where('food_item_id', $oi->food_items_id)
                            ->where('is_active', 1)
                            ->first();
                        return $fallbackServing ? 'srv_' . $fallbackServing->id : 'item_' . $oi->food_items_id;
                    })->toArray(),
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
                'items.*'       => 'string',
            ]);

            // Resolve every picked row key to its real item + the exact menu
            // label the admin saw, then de-duplicate by that resolved item —
            // see store() for the full reasoning.
            $clubId = auth()->user()->club_id;
            $resolvedItems = collect($request->items)
                ->map(fn($key) => $this->resolvePickerKey((string) $key, $clubId))
                ->filter()
                ->unique('food_items_id')
                ->values();

            if ($resolvedItems->isEmpty()) {
                return response()->json(['statusCode' => 422, 'error' => 'One or more selected items are no longer available.']);
            }

            $newOfferType = OfferType::findOrFail($request->offer_type_id);

            if (in_array($newOfferType->slug, ['percentage', 'flat'])) {
                $request->validate(['discount_value' => 'required|numeric|min:0']);
            }
            if ($newOfferType->slug === 'b1g1') {
                $request->validate([
                    'buy_qty'   => 'required|integer|min:1',
                    'get_qty'   => 'required|integer|min:1',
                    'volume_ml' => 'nullable|integer|min:1',
                ]);
            }

            $isAdmin  = Auth::user()->hasRole('admin');
            $oldItems = $offer->offerItems->pluck('food_items_id')->toArray();
            $volumeRule = ($newOfferType->slug === 'b1g1' && $request->filled('volume_ml'))
                ? (int) $request->volume_ml
                : null;

            $payload = [
                'offer_id' => $offer->id,
                'old'      => [
                    'name'           => $offer->name,
                    'offer_type_id'  => $offer->offer_type_id,
                    'offer_type'     => $offer->offerType?->name,
                    'applies_to'     => $offer->applies_to,
                    'discount_value' => $offer->discount_value,
                    'buy_qty'        => $offer->buy_qty,
                    'get_qty'        => $offer->get_qty,
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
                    'buy_qty'        => $newOfferType->slug === 'b1g1' ? $request->buy_qty : null,
                    'get_qty'        => $newOfferType->slug === 'b1g1' ? $request->get_qty : null,
                    'volume_ml'      => $volumeRule,
                    'start_at'       => $request->start_at,
                    'end_at'         => $request->end_at,
                    'items'          => $resolvedItems->pluck('food_items_id')->all(),
                    // Full per-item detail (label/picker_key), used to rebuild
                    // OfferItem.rules correctly whenever this payload gets applied
                    // — 'items' alone only has bare ids, not enough to do that.
                    'items_detail'   => $resolvedItems->values()->all(),
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
                    'buy_qty'        => $newOfferType->slug === 'b1g1' ? $request->buy_qty : null,
                    'get_qty'        => $newOfferType->slug === 'b1g1' ? $request->get_qty : null,
                    'start_at'       => $request->start_at,
                    'end_at'         => $request->end_at,
                ]);

                OfferItem::where('offer_id', $offer->id)->delete();
                foreach ($resolvedItems as $resolved) {
                    OfferItem::create([
                        'offer_id'      => $offer->id,
                        'food_items_id' => $resolved['food_items_id'],
                        'rules'         => array_filter([
                            'volume_ml'  => $volumeRule,
                            'menu_label' => $resolved['menu_label'],
                            'picker_key' => $resolved['picker_key'],
                        ], fn($v) => $v !== null),
                    ]);
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
