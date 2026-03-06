<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\Card;
use App\Models\Member;
use App\Models\MemberCardMapping;
use App\Models\MembershipFormDetail;
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

            $data = ActionApproval::with('operatorDetails')
                ->where('maker_user_id', '!=', Auth::id())
                ->where('status', 'pending')
                ->latest()
                ->get();
            // dd($data);

            return view('action_approval.list', compact(
                'title',
                'page_title',
                'data',
            ));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function approve($id)
    {
        try {

            $data = ActionApproval::find($id);

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

                if ($member->image && $payload->swim_image != $member->image && file_exists(public_path($member->image))) {
                    unlink(public_path($member->image));
                }



                $memberDetail = MembershipFormDetail::where('member_id', $memberId)->first();

                if ($member->swim_guardian_image && $payload->swim_guardian_image != $memberDetail->details['guardian_image'] && file_exists(public_path($memberDetail->details['guardian_image']))) {
                    unlink(public_path($memberDetail->details['guardian_image']));
                }

                $member->update([
                    'name'        => $payload->swim_name,
                    'email'       => $payload->swim_email,
                    'phone'       => $payload->swim_phone,
                    'address'     => $payload->swim_address,
                    'image'       => $payload->swim_image
                    // 'status'      => 'pending_approval'
                ]);


                $memberDetail->update([
                    'details' => [
                        'age' => $payload->swim_age,
                        'sex' => $payload->swim_sex,
                        'height' => $payload->swim_height,
                        'weight' => $payload->swim_weight,
                        'pulse_rate' => $payload->swim_pulse_rate,
                        'batch' => $payload->swim_batch,
                        'vaccination' => $payload->swim_vaccination,
                        'i_agree' => 1,
                        // 'disease' => $payload['swim_disease'] ?? [],
                        'disease' => $payload->swim_disease,
                        'guardian_name' => $payload->swim_guardian_name,
                        'guardian_occupation' => $payload->swim_guardian_occupation,
                        'guardian_image' => $payload->swim_guardian_image
                    ]
                ]);




                $card_no = $payload->swim_card_id;

                if ($card_no) {
                    $currentCardMapping = MemberCardMapping::where('member_id', $memberId)->first();

                    $currentCard = Card::find($currentCardMapping->card_id);
                    if ($currentCard) {
                        $currentCard->update([
                            'is_assigned' => 0
                        ]);
                    }

                    $newCard = Card::find($card_no);
                    if ($newCard) {
                        $newCard->update([
                            'is_assigned' => 1
                        ]);

                        $currentCardMapping->update([
                            'card_id' => $card_no
                        ]);
                    }
                }


                DB::commit();
            }

            $data->update([
                'status' => 'approved'
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
            } elseif ($data->module == 'member_edit') {
                $payloadJson = $data->request_payload;
                $payload = json_decode($payloadJson);

                $card_id = $payload->swim_card_id;

                $card = Card::find($card_id);

                if ($card) {
                    $card->update([
                        'is_assigned' => 0
                    ]);
                }
                // return $payload;
            }

            $data->update([
                'status' => 'rejected'
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
}
