<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\Card;
use App\Models\FoodItemPrice;
use App\Models\Member;
use App\Models\MemberCardMapping;
use App\Models\MembershipFormDetail;
use App\Models\MembershipPurchaseHistory;
use App\Models\MembershipType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

            $swimmingMembershipData = ActionApproval::with('operatorDetails')
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

            $clubMembershipData = ActionApproval::with('operatorDetails')
                ->where('maker_user_id', '!=', Auth::id())
                ->where('membership_type_id', $clubMembershipId)
                ->where('status', 'pending')
                ->latest()
                ->get();

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
                    ->where('member_id', $id)
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

                    $member->delete();

                    // $approvalRequests = ActionApproval::where('club_id', $clubId)
                    //                                   ->where('entity_id', $id)
                    //                                   ->where('status', 'pending')
                    //                                   ->delete();
                }

                DB::commit();
            }

            if($data->module == 'food_price_update'){

                $payloadJson = $data->request_payload;

                $payload = json_decode($payloadJson);

                $itemId = $data->entity_id;
                $newPrice = $payload->new_price;

                DB::beginTransaction();

                $currentPrice = FoodItemPrice::where('item_id', $itemId)
                                             ->where('is_active', 1)
                                             ->first();

                if ($currentPrice) {

                    $currentPrice->update([
                        'is_active' => 0,
                        'effective_to' => now()
                    ]);
                }

                FoodItemPrice::create([
                    'item_id' => $itemId,
                    'price' => $newPrice,
                    'effective_from' => now(),
                    'is_active' => 1
                ]);

                DB::commit();
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

            $data = ActionApproval::find($id);

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
            } elseif ($data->module == 'member_edit') {
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
            }

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

    public function view($id)
    {
        try {
            $clubId = club_id();

            $approval = ActionApproval::where('club_id', $clubId)
                ->find($id);

            $details = json_decode($approval->request_payload);

            return response()->json([
                'data' => $details,
                'statusCode' => 200,
                'message' => 'Approval Details Fetched successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }
}
