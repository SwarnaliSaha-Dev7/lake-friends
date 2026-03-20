<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\Card;
use App\Models\FoodItem;
use App\Models\FoodItemCurrentStock;
use App\Models\FoodItemPrice;
use App\Models\Locker;
use App\Models\LockerAllocation;
use App\Models\LockerPrice;
use App\Models\Location;
use App\Models\LiquorServing;
use App\Models\Member;
use App\Models\MemberAddOn;
use App\Models\MemberCardMapping;
use App\Models\MembershipFormDetail;
use App\Models\MembershipPurchaseHistory;
use App\Models\MembershipType;
use App\Models\PaymentHistory;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Offer;
use App\Models\OfferItem;
use App\Models\StockLedger;
use App\Models\StockWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BarStockController;

class ActionApprovalController extends Controller
{
    public function index()
    {
        try {

            $title = 'Action Approval list';
            $page_title = 'Action Approval Member';

            $clubId = club_id();

            $swimmingMembershipId = MembershipType::where('name', 'Swimming Membership')->value('id');

            $clubMembershipId = MembershipType::where('name', 'Club Membership')->value('id');

            $swimmingMembershipData = ActionApproval::with(['operatorDetails','entity'])
                ->where('maker_user_id', '!=', Auth::id())
                ->where('membership_type_id', $swimmingMembershipId)
                ->where('status', 'pending')
                ->latest()
                ->get();

            // $swimmingMembershipData = ActionApproval::with('operatorDetails')
            //     ->where('maker_user_id', '!=', Auth::id())
            //     ->where(function ($q) use ($swimmingMembershipId) {

            //         $q->where('membership_type_id', $swimmingMembershipId)
            //         ->orWhere('module', 'member_delete');

            //     })
            //     ->where('status', 'pending')
            //     ->latest()
            //     ->get();

            // $swimmingMembershipData = ActionApproval::with('operatorDetails')
            //     ->where('maker_user_id', '!=', Auth::id())
            //     ->where('status', 'pending')
            //     ->where(function ($q) use ($swimmingMembershipId) {

            //         $q->where('membership_type_id', $swimmingMembershipId)

            //         ->orWhere(function ($sub) use ($swimmingMembershipId) {

            //             $sub->where('module', 'member_delete')
            //                 ->whereHas('entity.memberDetail', function ($m) use ($swimmingMembershipId) {
            //                     $m->where('membership_type_id', $swimmingMembershipId);
            //                 });

            //         });

            //     })
            //     ->latest()
            //     ->get();

            $clubMembershipData = ActionApproval::with(['operatorDetails','entity'])
                ->where('maker_user_id', '!=', Auth::id())
                ->where('membership_type_id', $clubMembershipId)
                ->where('status', 'pending')
                ->latest()
                ->get();

            $clubMembershipData->each(function ($row) {
                if ($row->module === 'plan_renewal' && $row->entity) {
                    $row->entity->load('member:id,name', 'membershipPlanType:id,name');
                }
            });

            // $clubMembershipData = ActionApproval::with('operatorDetails')
            //     ->where('maker_user_id', '!=', Auth::id())
            //     ->where(function ($q) use ($clubMembershipId) {

            //         $q->where('membership_type_id', $clubMembershipId)
            //         ->orWhere('module', 'member_delete');

            //     })
            //     ->where('status', 'pending')
            //     ->latest()
            //     ->get();

            // $clubMembershipData = ActionApproval::with('operatorDetails')
            //     ->where('maker_user_id', '!=', Auth::id())
            //     ->where('status', 'pending')
            //     ->where(function ($q) use ($clubMembershipId) {

            //         $q->where('membership_type_id', $clubMembershipId)

            //         ->orWhere(function ($sub) use ($clubMembershipId) {

            //             $sub->where('module', 'member_delete')
            //                 ->whereHas('entity.memberDetail', function ($m) use ($clubMembershipId) {
            //                     $m->where('membership_type_id', $clubMembershipId);
            //                 });

            //         });

            //     })
            //     ->latest()
            //     ->get();
            // dd($data);

            $cards = Card::where('club_id', $clubId)
                ->where('status', 'active')
                ->get();

            return view('action_approval.list', compact(
                'title',
                'page_title',
                'swimmingMembershipData',
                'clubMembershipData',
                'cards'
            ));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function offerApprovalList()
    {
        try {
            $title      = 'Offer Approval List';
            $page_title = 'Offer Approval';

            $clubId = club_id();

            $offerApprovalData = ActionApproval::with(['operatorDetails', 'entity.offerType', 'entity.offerItems.foodItem'])
                ->where('club_id', $clubId)
                ->where('module', 'offer')
                ->where('maker_user_id', '!=', Auth::id())
                ->where('status', 'pending')
                ->latest()
                ->get();

            return view('action_approval.offer.list', compact('title', 'page_title', 'offerApprovalData'));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function foodItemPriceList()
    {
        try {
                $title = 'Food Item Price Approval List';
                $page_title = 'Food Item Price Approval';

                $clubId = club_id();
                // return 68736;

                $foodPriceData = ActionApproval::with('operatorDetails','entity')
                                            ->where('club_id', $clubId)
                                            ->where('module', 'food_price_update')
                                            ->where('status', 'pending')
                                            ->where('maker_user_id', '!=', Auth::id())
                                            ->latest()
                                            ->get();

                return view('action_approval.food_item_price.list',compact('title','page_title','foodPriceData'));
        }

        catch (\Throwable $th) {
            return $th->getMessage();
        }

    }

    public function approve($id)
    {
        try {

            $data = ActionApproval::findOrFail($id);

            if ($data->status != "pending") {
                return response()->json([
                    'statusCode' => 403,
                    'message' => 'Approval Cannot Be Possible'
                ]);
            }

            if ($data->module == 'member_edit') {
                $payloadJson = $data->request_payload;
                $payload = json_decode($payloadJson);
                // return $payload;
                $clubId = club_id();
                $memberId = $payload->member_id;

                // $exists = Member::where('email', $payload->swim_email)
                //     ->where('club_id', $clubId)
                //     ->exists();

                // if ($exists) {
                //     return response()->json([
                //         'statusCode' => 409,
                //         'message' => 'Email already exists'
                //     ]);
                // }

                DB::beginTransaction();

                $member = Member::find($memberId);

                // if ($member->image && $payload->image != $member->image && file_exists(public_path($member->image))) {
                //     unlink(public_path($member->image));
                // }



                $memberDetail = MembershipFormDetail::where('member_id', $memberId)->first();

                $membershipType = MembershipType::find($memberDetail->membership_type_id);

                $membershipTypeName = $membershipType->name;


                if ($membershipTypeName == 'Club Membership') {

                    if ($member->image && $payload->image != $member->image && file_exists(public_path($member->image))) {
                        unlink(public_path($member->image));
                    }

                    if ($member->spouse_image && isset($memberDetail->details['spouse_image']) && $payload->spouse_image != $memberDetail->details['spouse_image'] && file_exists(public_path($memberDetail->details['spouse_image']))) {
                        unlink(public_path($memberDetail->details['spouse_image']));
                    }

                    $member->update([
                        'name'        => ucwords($payload->name),
                        'email'       => $payload->email,
                        'phone'       => $payload->phone,
                        'address'     => $payload->address,
                        'image'       => $payload->image,
                        'status'      => $payload->club_status
                    ]);


                    $memberDetail->update([
                        'details' => [

                            'blood_grp' => $payload->blood_grp,
                            'spouse_name' => ucwords($payload->spouse_name),
                            'spouse_email' => $payload->spouse_email,
                            'spouse_phone' => $payload->spouse_phone,
                            'spouse_blood_grp' => $payload->spouse_blood_grp,
                            'spouse_address' => $payload->spouse_address,
                            'spouse_image' => $payload->spouse_image,

                        ]
                    ]);
                }

                if ($membershipTypeName == 'Swimming Membership') {

                    if ($member->image && $payload->swim_image != $member->image && file_exists(public_path($member->image))) {
                        unlink(public_path($member->image));
                    }

                    if ($member->swim_guardian_image && $payload->swim_guardian_image != $memberDetail->details['guardian_image'] && file_exists(public_path($memberDetail->details['guardian_image']))) {
                        unlink(public_path($memberDetail->details['guardian_image']));
                    }

                    $member->update([
                        'name'        => ucwords($payload->swim_name),
                        'email'       => $payload->swim_email,
                        'phone'       => $payload->swim_phone,
                        'address'     => $payload->swim_address,
                        'image'       => $payload->swim_image,
                        'status'      => $payload->swim_status
                    ]);

                    $details = [
                        'police_station' => $payload->swim_member_police_station,
                        'age' => $payload->swim_age,
                        'sex' => $payload->swim_sex,
                        'height' => $payload->swim_height,
                        'weight' => $payload->swim_weight,
                        'pulse_rate' => $payload->swim_pulse_rate,
                        'batch' => $payload->swim_batch,
                        'vaccination' => $payload->swim_vaccination,
                        'i_agree' => 1,
                        'guardian_name' => ucwords($payload->swim_guardian_name),
                        'guardian_occupation' => $payload->swim_guardian_occupation,
                        'guardian_image' => $payload->swim_guardian_image,
                    ];

                    if (isset($payload->swim_disease)) {
                        $details['disease'] = $payload->swim_disease;
                    }


                    $memberDetail->update([
                        'details' => $details
                    ]);

                    // if (isset($payload->swim_disease)) {
                    //     $memberDetail->update([
                    //         'details' => [
                    //             'disease' => $payload->swim_disease,
                    //         ]
                    //     ]);
                    // }
                }






                // $card_no = $payload->swim_card_id ?? $payload->card_id;
                if (isset($payload->swim_card_id)) {
                    $card_no = $payload->swim_card_id;
                } elseif (isset($payload->card_id)) {
                    $card_no = $payload->card_id;
                } else {
                    $card_no = 0;
                }

                if ($card_no) {
                    $currentCardMapping = MemberCardMapping::where('member_id', $memberId)->first();

                    if ($currentCardMapping) {
                        $currentCard = Card::find($currentCardMapping->card_id);
                        if ($currentCard) {
                            $currentCard->update([
                                'is_assigned' => 0
                            ]);
                        }
                    }

                    $newCard = Card::find($card_no);
                    if ($newCard) {
                        // $newCard->update([
                        //     'is_assigned' => 1
                        // ]);
                        if ($currentCardMapping) {
                            $currentCardMapping->update([
                                'card_id' => $card_no
                            ]);
                        } else {
                            $card_mapping = MemberCardMapping::create([
                                'card_id' => $card_no,
                                'member_id' => $member->id
                            ]);
                        }
                    }
                }


                DB::commit();
            }

            if ($data->module == 'member_create') {

                $clubId = club_id();
                $memberId = $data->entity_id;

                // DB::beginTransaction();

                $member = Member::find($memberId);

                $membershipPlanPurchase = MembershipPurchaseHistory::where('club_id', $clubId)
                    ->where('member_id', $memberId)
                    ->where('status', 'pending')
                    ->first();

                if ($membershipPlanPurchase) {
                    $membershipPlanPurchase->update([
                        'status' => 'active'
                    ]);
                }

                $member->update([
                    'status'      => 'active'
                ]);
            }

            if ($data->module == 'plan_renewal') {
                $purchase = MembershipPurchaseHistory::find($data->entity_id);
                if ($purchase) {
                    $purchase->update(['status' => 'active']);
                }
            }

            if ($data->module == 'member_delete') {

                DB::beginTransaction();

                $member = Member::find($data->entity_id);

                if ($member) {

                    $cardMapping = MemberCardMapping::where('member_id', $member->id)->first();

                    if ($cardMapping) {

                        $card = Card::find($cardMapping->card_id);

                        if ($card) {
                            $card->update([
                                'is_assigned' => 0
                            ]);
                        }

                        $cardMapping->delete();
                    }

                    $lockerAllocation = LockerAllocation::where('member_id', $member->id)->first();

                    $locker = null;

                    if($lockerAllocation){
                        $locker = Locker::find($lockerAllocation->locker_id);
                    }

                    if($lockerAllocation){
                        $lockerAllocation->delete();
                    }

                    if($locker){
                        $locker->update([
                            'status' => 'available',
                        ]);
                    }

                    $member->delete();

                    // $approvalRequests = ActionApproval::where('club_id', $clubId)
                    //                                   ->where('entity_id', $id)
                    //                                   ->where('status', 'pending')
                    //                                   ->delete();
                }

                DB::commit();
            }

            if ($data->module == 'offer') {
                $offer = Offer::find($data->entity_id);
                if ($offer) {
                    if ($data->action_type === 'create') {
                        $offer->update(['status' => 'active']);

                    } elseif ($data->action_type === 'update') {
                        $payload = is_array($data->request_payload)
                            ? $data->request_payload
                            : json_decode($data->request_payload, true);
                        $new = $payload['new'];

                        $offer->update([
                            'name'           => $new['name'],
                            'offer_type_id'  => $new['offer_type_id'],
                            'applies_to'     => $new['applies_to'],
                            'discount_value' => $new['discount_value'] ?? 0,
                            'start_at'       => $new['start_at'],
                            'end_at'         => $new['end_at'],
                        ]);

                        OfferItem::where('offer_id', $offer->id)->delete();
                        foreach ($new['items'] as $itemId) {
                            OfferItem::create(['offer_id' => $offer->id, 'food_items_id' => $itemId]);
                        }

                    } elseif ($data->action_type === 'delete') {
                        OfferItem::where('offer_id', $offer->id)->delete();
                        $offer->delete();
                    }
                }
            }

            if($data->module == 'food_price_update' || $data->module == 'liquor_price_update'){

                $payload  = is_array($data->request_payload) ? (object) $data->request_payload : json_decode($data->request_payload);
                $itemId   = $data->entity_id;
                $newPrice = $payload->new_price;

                DB::beginTransaction();

                $currentPrice = FoodItemPrice::where('item_id', $itemId)
                                             ->where('is_active', 1)
                                             ->first();

                if ($currentPrice) {
                    $currentPrice->update([
                        'is_active'    => 0,
                        'effective_to' => now()
                    ]);
                }

                FoodItemPrice::create([
                    'item_id'        => $itemId,
                    'price'          => $newPrice,
                    'effective_from' => now(),
                    'is_active'      => 1
                ]);

                DB::commit();
            }

            if ($data->module == 'locker_purchase') {
                $payload = is_array($data->request_payload)
                    ? $data->request_payload
                    : json_decode($data->request_payload, true);

                // $lockerId = $payload['locker_id'] ?? null;
                $lockerAllocationId = $payload['locker_allocation_id'] ?? null;
                $memberId = $data->entity_id;
                $lockerPrice = $payload['locker_price'] ?? 0;

                DB::beginTransaction();

                $allocation = null;
                if ($lockerAllocationId) {
                    $allocation = LockerAllocation::where('id', $lockerAllocationId)
                        ->where('member_id', $memberId)
                        ->first();
                }

                if ($allocation) {
                    $allocation->update([
                        'status' => 'active',
                    ]);
                }

                DB::commit();
            }

            if ($data->module == 'liquor_item_create') {
                $payload = is_array($data->request_payload) ? (object) $data->request_payload : json_decode($data->request_payload);
                $item    = FoodItem::find($data->entity_id);
                if ($item) {
                    $item->update(['is_active' => $payload->is_active ?? 1]);
                }
            }

            if ($data->module == 'liquor_item_delete') {
                $item = FoodItem::find($data->entity_id);
                if ($item) {
                    $item->delete();
                }
            }

            if ($data->module == 'liquor_serving_create') {
                $serving = LiquorServing::find($data->entity_id);
                if ($serving) {
                    $serving->update(['is_active' => 1]);
                }
            }

            if ($data->module == 'liquor_serving_update') {
                $payload = is_array($data->request_payload) ? $data->request_payload : json_decode($data->request_payload, true);
                $serving = LiquorServing::find($data->entity_id);
                if ($serving && isset($payload['new'])) {
                    $new = $payload['new'];
                    $serving->update([
                        'name'      => $new['name'],
                        'volume_ml' => $new['volume_ml'],
                        'price'     => $new['price'],
                    ]);
                }
            }

            if ($data->module == 'liquor_serving_delete') {
                $serving = LiquorServing::find($data->entity_id);
                if ($serving) {
                    $serving->delete();
                }
            }

            if ($data->module == 'bar_stock_transfer') {
                $payload = is_array($data->request_payload) ? (object) $data->request_payload : json_decode($data->request_payload);

                DB::beginTransaction();

                $godownStock = FoodItemCurrentStock::where('warehouse_id', $payload->warehouse_id)
                    ->where('location_id', $payload->godown_location_id)
                    ->where('food_items_id', $payload->food_items_id)
                    ->first();

                if (!$godownStock || $godownStock->quantity < $payload->bottles) {
                    return response()->json([
                        'statusCode' => 422,
                        'message'    => 'Insufficient godown stock at time of approval.',
                    ]);
                }

                (new BarStockController)->executeTransfer(
                    $payload->warehouse_id,
                    $payload->godown_location_id,
                    $payload->bar_location_id,
                    $payload->food_items_id,
                    $payload->bottles,
                    $payload->bar_qty,
                    $godownStock
                );

                DB::commit();
            }

            if ($data->module == 'stock_adjustment') {
                $payload = is_array($data->request_payload) ? (object) $data->request_payload : json_decode($data->request_payload);

                DB::beginTransaction();

                StockLedger::create([
                    'warehouse_id'   => $payload->warehouse_id,
                    'location_id'    => $payload->location_id,
                    'food_items_id'  => $payload->food_items_id,
                    'movement_type'  => $payload->movement_type,
                    'direction'      => $payload->direction,
                    'quantity'       => $payload->quantity,
                    'reference_type' => 'manual',
                ]);

                $currentStock = FoodItemCurrentStock::where('warehouse_id', $payload->warehouse_id)
                    ->where('location_id', $payload->location_id)
                    ->where('food_items_id', $payload->food_items_id)
                    ->first();

                if ($payload->direction === 'in') {
                    if ($currentStock) {
                        $currentStock->increment('quantity', $payload->quantity);
                    } else {
                        FoodItemCurrentStock::create([
                            'warehouse_id'  => $payload->warehouse_id,
                            'location_id'   => $payload->location_id,
                            'food_items_id' => $payload->food_items_id,
                            'quantity'      => $payload->quantity,
                        ]);
                    }
                } else {
                    if ($currentStock) {
                        $currentStock->decrement('quantity', $payload->quantity);
                    }
                }

                DB::commit();
            }

            if ($data->module == 'add_on_purchase') {
                $payload = is_array($data->request_payload)
                    ? $data->request_payload
                    : json_decode($data->request_payload, true);

                $addonIds = $payload['member_addon_ids'] ?? [];
                if (!empty($addonIds)) {
                    MemberAddOn::whereIn('id', $addonIds)
                        ->update(['status' => 'active']);
                }
            }

            $data->update([
                'checker_user_id' => Auth::id(),
                'status' => 'approved',
                'approved_or_rejected_at' => now(),
            ]);


            // dd($data);

            return response()->json([
                'statusCode' => 200,
                'message' => 'Approval Successfull'
            ]);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function reject($id)
    {
        try {
            $clubId = club_id();

            $data = ActionApproval::with('membershipType')->find($id);
            $membershipType = $data->membershipType?->name ?? '';

            if ($data->status != "pending") {
                return response()->json([
                    'statusCode' => 403,
                    'message' => 'Rejection Cannot Be Possible'
                ]);
            }

            if ($data->module == 'member_create') {
                $cardMapping = MemberCardMapping::where('member_id', $data->entity_id)
                    ->latest()
                    ->first();

                if ($cardMapping) {
                    $card = Card::find($cardMapping->card_id);

                    if ($card) {
                        $card->update([
                            'is_assigned' => 0
                        ]);
                    }

                    $cardMapping->delete();
                }

                $memberId = $data->entity_id;
                $member = Member::find($memberId);


                $member->update([
                    'status'      => 'rejected'
                ]);


                $membershipPlanPurchase = MembershipPurchaseHistory::where('club_id', $clubId)
                    ->where('member_id', $memberId)
                    ->where('status', 'pending')
                    ->first();

                if ($membershipPlanPurchase) {
                    $membershipPlanPurchase->update([
                        'status' => 'cancelled'
                    ]);
                }
            } elseif ($data->module == 'offer') {
                $offer = Offer::find($data->entity_id);
                if ($offer) {
                    $offer->update(['status' => 'rejected']);
                }
            } elseif ($data->module == 'liquor_item_create') {
                $item = FoodItem::find($data->entity_id);
                if ($item) {
                    $item->delete();
                }
            } elseif ($data->module == 'liquor_serving_create') {
                $serving = LiquorServing::find($data->entity_id);
                if ($serving) {
                    $serving->forceDelete();
                }
            } elseif ($data->module == 'plan_renewal') {
                $purchase = MembershipPurchaseHistory::find($data->entity_id);
                if ($purchase) {
                    $purchase->update(['status' => 'cancelled']);
                }
            }
            // liquor_serving_update / liquor_serving_delete: no rollback needed
            elseif ($data->module == 'member_edit') {
                $payloadJson = $data->request_payload;
                $payload = json_decode($payloadJson);

                if (isset($payload->swim_card_id)) {
                    $card_id = $payload->swim_card_id;
                } elseif (isset($payload->card_id)) {
                    $card_id = $payload->card_id;
                } else {
                    $card_id = 0;
                }

                // $card_id = $payload->swim_card_id;
                if ($card_id) {
                    $card = Card::find($card_id);

                    if ($card) {
                        $card->update([
                            'is_assigned' => 0
                        ]);
                    }
                }
                // return $payload;
            }elseif ($data->module == 'locker_purchase') {
                $payloadJson = $data->request_payload;
                $payload = json_decode($payloadJson, true);

                $lockerId = $payload['locker_id'] ?? null;
                $lockerAllocationId = $payload['locker_allocation_id'] ?? null;
                $memberId = $data->entity_id;

                DB::beginTransaction();

                if ($lockerAllocationId) {
                    $allocation = LockerAllocation::where('id', $lockerAllocationId)
                        ->where('member_id', $memberId)
                        ->first();

                    if ($allocation) {
                        $allocation->delete();
                    }
                }

                if ($lockerId) {
                    Locker::where('id', $lockerId)->update([
                        'status' => 'available'
                    ]);
                }

                if ($membershipType && $membershipType === 'Swimming Membership' && $lockerAllocationId) {
                    PaymentHistory::where('locker_allocation_id', $lockerAllocationId)
                                    ->update(['payment_status' => 'refunded']);
                }
                else{
                    //REFUND in wallet for club members
                    // $refundAmount = LockerPrice::where('club_id', $clubId)->value('price') ?? 0;
                    $refundAmount = $payload['locker_price'];
                    $wallet = Wallet::where('member_id', $memberId)->lockForUpdate()->first();
                    if ($wallet) {
                        $wallet->current_balance += $refundAmount;
                        $wallet->save();

                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'member_id' => $memberId,
                            'amount'    => $refundAmount,
                            'direction' => 'credit',
                            'txn_type'  => 'refund',
                            'created_by' => auth()->id(),
                        ]);
                    }
                }

                DB::commit();
            } elseif ($data->module == 'add_on_purchase') {
                $payloadJson = $data->request_payload;
                $payload = json_decode($payloadJson, true);

                $addonIds = $payload['member_addon_ids'] ?? [];
                $refundAmount = (float) ($payload['total_price'] ?? 0);
                $memberId = $data->entity_id;

                DB::beginTransaction();

                if (!empty($addonIds)) {
                    MemberAddOn::whereIn('id', $addonIds)->delete();
                }

                if ($refundAmount > 0) {
                    $wallet = Wallet::where('member_id', $memberId)->lockForUpdate()->first();
                    if ($wallet) {
                        $wallet->current_balance += $refundAmount;
                        $wallet->save();

                        WalletTransaction::create([
                            'wallet_id'  => $wallet->id,
                            'member_id'  => $memberId,
                            'amount'     => $refundAmount,
                            'direction'  => 'credit',
                            'txn_type'   => 'refund',
                            'created_by' => auth()->id(),
                        ]);
                    }
                }

                DB::commit();
            }
            // stock_adjustment: no rollback needed, stock was never added while pending

            $data->update([
                'status' => 'rejected',
                'checker_user_id' => Auth::id(),
                'approved_or_rejected_at' => now(),
            ]);


            // dd($data);

            return response()->json([
                'statusCode' => 200,
                'message' => 'Rejection Successfull'
            ]);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }


    // public function view($id)
    // {
    //     try {
    //         $clubId = club_id();

    //         $approval = ActionApproval::where('club_id', $clubId)
    //             ->find($id);

    //         $details = json_decode($approval->request_payload);

    //         return response()->json([
    //             'data' => $details,
    //             'statusCode' => 200,
    //             'message' => 'Approval Details Fetched successfully'
    //         ]);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'statusCode' => 500,
    //             'error' => $th->getMessage(),
    //         ]);
    //     }
    // }

    public function view($id)
    {
        try {
            $clubId = club_id();

            $approval = ActionApproval::where('club_id', $clubId)->find($id);

            $details = json_decode($approval->request_payload, true);

            // Locker part
            if ($approval->module == 'locker_purchase') {

                $locker = Locker::find($details['locker_id'] ?? null);
                $allocation = LockerAllocation::find($details['locker_allocation_id'] ?? null);

                return response()->json([
                    'statusCode' => 200,
                    'data' => [
                        'locker_name' => $locker->locker_number ?? 'N/A',
                        'start_date' => $allocation->start_date ?? null,
                        'end_date' => $allocation->end_date ?? null,
                        'locker_price' => $details['locker_price'] ?? 0,
                    ]
                ]);
            }

            else{

                // club/swimming
                return response()->json([
                    'data' => $details,
                    'statusCode' => 200,
                    'message' => 'Approval Details Fetched successfully'
                ]);
            }

        }

        catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }


    public function liquorApprovalList()
    {
        try {
            $title      = 'Liquor Approval List';
            $page_title = 'Liquor Approval';
            $clubId     = club_id();

            $liquorApprovalData = ActionApproval::with(['operatorDetails', 'entity'])
                ->where('club_id', $clubId)
                ->whereIn('module', ['liquor_item_create', 'liquor_item_delete', 'liquor_price_update'])
                ->where('maker_user_id', '!=', Auth::id())
                ->where('status', 'pending')
                ->latest()
                ->get();

            return view('action_approval.liquor.list', compact('title', 'page_title', 'liquorApprovalData'));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function godownStockApprovalList()
    {
        try {
            $title      = 'Godown Stock Approval List';
            $page_title = 'Godown Stock Approval';
            $clubId     = club_id();

            $stockApprovalData = ActionApproval::with(['operatorDetails', 'entity'])
                ->where('club_id', $clubId)
                ->where('module', 'stock_adjustment')
                ->where('maker_user_id', '!=', Auth::id())
                ->where('status', 'pending')
                ->latest()
                ->get();

            return view('action_approval.godown_stock.list', compact('title', 'page_title', 'stockApprovalData'));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function barStockApprovalList()
    {
        try {
            $title      = 'Bar Stock Transfer Approval List';
            $page_title = 'Bar Stock Approval';
            $clubId     = club_id();

            $transferApprovalData = ActionApproval::with(['operatorDetails', 'entity'])
                ->where('club_id', $clubId)
                ->where('module', 'bar_stock_transfer')
                ->where('maker_user_id', '!=', Auth::id())
                ->where('status', 'pending')
                ->latest()
                ->get();

            return view('action_approval.bar_stock.list', compact('title', 'page_title', 'transferApprovalData'));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function liquorServingApprovalList()
    {
        try {
            $title      = 'Liquor Menu Approval List';
            $page_title = 'Liquor Menu Approval';
            $clubId     = club_id();

            $approvalData = ActionApproval::with(['operatorDetails', 'entity'])
                ->where('club_id', $clubId)
                ->whereIn('module', ['liquor_serving_create', 'liquor_serving_update', 'liquor_serving_delete'])
                ->where('maker_user_id', '!=', Auth::id())
                ->where('status', 'pending')
                ->latest()
                ->get();

            return view('action_approval.liquor_serving.list', compact('title', 'page_title', 'approvalData'));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function allApprovalList()
    {
        try {

            $title = 'All Action Approval list';
            $page_title = 'All Action Approval Member';

            $clubId = club_id();

            $actionApprovalList = ActionApproval::with([
                    'operatorDetails',
                    'entity',
                    'membershipType:id,name',
                    'checker:id,name',
                ])
                ->where('club_id', $clubId)
                ->latest('id')
                ->get();

            // Eager load renewal-specific relations
            $actionApprovalList->each(function ($row) {
                if ($row->module === 'plan_renewal' && $row->entity) {
                    $row->entity->load('member:id,name', 'membershipPlanType:id,name');
                }
            });

            return view('all-action-approval-list', compact(
                'title',
                'page_title',
                'actionApprovalList',
            ));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
