<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Card;
use App\Models\GstRate;
use App\Models\Member;
use App\Models\MemberCardMapping;
use App\Models\MembershipFormDetail;
use App\Models\MembershipPlanType;
use App\Models\MembershipPurchaseHistory;
use App\Models\MembershipType;
use App\Models\PaymentHistory;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SwimmingMemberController extends Controller
{
    public function list()
    {
        try {

            $title = 'Swimming Member list';
            $page_title = 'Manage Swimming Member';

            $clubId = club_id();

            $membershipTypeId = MembershipType::where('name', 'Swimming Membership')
                ->value('id');

            $membershipPlanList = MembershipPlanType::where('membership_type_id', $membershipTypeId)
                ->where('is_active', 1)
                ->get();

            $gstPercentage = GstRate::where('club_id', $clubId)
                ->where('gst_type', 'plan_purchase')
                ->value('gst_percentage') ?? 0;

            $bankList = Bank::where('club_id', $clubId)->get();

            $cards = Card::doesntHave('memberMapping')
                ->where('club_id', $clubId)
                ->where('status', 'active')
                ->get();


            $members = Member::where('club_id', $clubId)
                ->with([
                    'memberDetails',
                    'cardDetails',
                    'purchaseHistory',
                    'walletDetails'
                ])
                ->whereHas('memberDetails', function ($query) use ($membershipTypeId) {
                    $query->where('membership_type_id', $membershipTypeId);
                })
                ->orderBy('created_at', 'DESC')
                ->get();

            // dd($members);

            return view('swimming_member.list', compact(
                'title',
                'page_title',
                'membershipPlanList',
                'gstPercentage',
                'bankList',
                'cards',
                'members'
            ));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }


    public function getPlanPrice(Request $request)
    {
        try {
            $data['plan'] = MembershipPlanType::find($request->planTypeId);

            if (!$data['plan']) {
                return response()->json([
                    'statusCode' => 404,
                    'message' => 'Plan not found'
                ]);
            }

            return response()->json([
                'data' => $data,
                'statusCode' => 200,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                // 'error' => $th->getMessage(),
            ]);
        }
    }

    public function store(Request $request)
    {
        try {

            $clubId = club_id();
            $exists = Member::where('email', $request->email)
                ->where('club_id', $clubId)
                ->exists();

            if ($exists) {
                return response()->json([
                    'statusCode' => 409,
                    'message' => 'Email already exists'
                ]);
            }

            DB::beginTransaction();

            $memberCode = 'LF-' . time();



            $membershipType = MembershipType::where('name', 'Swimming Membership')
                ->where('club_id', $clubId)
                ->first();

            $membershipTypeId = $membershipType->id;

            $dest_path = 'uploads/images';
            $image_path = null;
            if ($request->hasFile('swim_image')) {

                $file = $request->file('swim_image');
                $filename = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($dest_path, $filename, 'public');
                $image_path = 'storage/' . $path;
            }

            $member = Member::create([
                'club_id'     => $clubId,
                'member_code' => $memberCode,
                'name'        => $request->swim_name,
                'email'       => $request->swim_email,
                'phone'       => $request->swim_phone,
                'address'     => $request->swim_address,
                'image'       => $image_path
                // 'status'      => 'pending_approval'
            ]);
            $guardian_image_path = null;
            if ($request->hasFile('swim_guardian_image')) {

                $file = $request->file('swim_guardian_image');
                $filename = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($dest_path, $filename, 'public');
                $guardian_image_path = 'storage/' . $path;
            }

            MembershipFormDetail::create([
                'member_id' => $member->id,
                'membership_type_id' => $membershipTypeId,
                'details' => [
                    'age' => $request->swim_age,
                    'sex' => $request->swim_sex,
                    'height' => $request->swim_height,
                    'weight' => $request->swim_weight,
                    'pulse_rate' => $request->swim_pulse_rate,
                    'batch' => $request->swim_batch,
                    'vaccination' => $request->swim_vaccination,
                    'i_agree' => 1,
                    'disease' => $request->input('swim_disease', []),
                    'image' => $image_path,
                    'guardian_name' => $request->swim_guardian_name,
                    'guardian_occupation' => $request->swim_guardian_occupation,
                    'guardian_image' => $guardian_image_path
                ]
            ]);

            $plan = MembershipPlanType::where('id', $request->swim_membership_plan_type)
                ->where('is_active', 1)
                ->first();

            if (!$plan) {
                DB::rollBack();
                return response()->json([
                    'statusCode' => 404,
                    'message' => 'Membership plan not found'
                ]);
            }

            $startDate = Carbon::today();

            if ($plan->is_lifetime) {
                $expiryDate = null; // Lifetime membership
            } else {
                $expiryDate = $startDate->copy()->addMonths($plan->duration_months);
            }

            $fee = $plan->price;
            $fineAmount = 0;

            $gstPercentage = GstRate::where('club_id', $clubId)
                ->where('gst_type', 'plan_purchase')
                ->value('gst_percentage') ?? 0;

            $gst_amt = ($fee * $gstPercentage) / 100;

            $netAmount = $fee + $fineAmount + $gst_amt;

            $purchase_history = MembershipPurchaseHistory::create([
                'club_id'                 => $clubId,
                'member_id'               => $member->id,
                'membership_type_id'      => $membershipTypeId,
                'membership_plan_type_id' => $plan->id,
                'fee'                     => $fee,
                'fine_amount'             => $fineAmount,
                'net_amount'              => $netAmount,
                'start_date'              => $startDate,
                'expiry_date'             => $expiryDate,
                'status'                  => 1
            ]);


            $card_no = $request->swim_card_no;

            $payment_history = PaymentHistory::create([
                'member_id' => $member->id,
                'club_id' =>  $clubId,
                'purpose' => 'plan_purchase',
                'membership_purchase_history_id' => $purchase_history->id,
                'wallet_transaction_id' => null,
                'mr_no' => null,
                'bill_no' => null,
                'ac_head' => $request->swim_ac_head,
                'taxable_amount' => $fee,
                'gst_percentage' => $gstPercentage,
                'gst_amount' => $gst_amt,
                'net_amount' => $netAmount,
                'payment_mode' => $request->swim_payment_mode,
                'payment_status' => 'success',
                'bank_id' => $request->swim_bank_id,
                'remarks' => $request->swim_remarks
            ]);

            $card_mapping = MemberCardMapping::create([
                'card_id' => $request->swim_card_id,
                'member_id' => $member->id
            ]);

            DB::commit();

            return response()->json([
                // 'data' => $data,
                'statusCode' => 200,
                'message' => 'Member created successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function update(Request $request)
    {
        try {

            $clubId = club_id();
            $memberId = $request->member_id;
            $exists = Member::where('email', $request->email)
                ->where('club_id', $clubId)
                ->exists();

            if ($exists) {
                return response()->json([
                    'statusCode' => 409,
                    'message' => 'Email already exists'
                ]);
            }

            DB::beginTransaction();

            $member = Member::find($memberId);

            $dest_path = 'uploads/images';
            $image_path = null;
            if ($request->hasFile('swim_image')) {

                if ($member->image && file_exists(public_path($member->image))) {
                    unlink(public_path($member->image));
                }

                $file = $request->file('swim_image');
                $filename = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($dest_path, $filename, 'public');
                $image_path = 'storage/' . $path;
            } else {
                $image_path = $member->image;
            }

            $memberDetail = MembershipFormDetail::where('member_id', $memberId)->first();

            $guardian_image_path = null;
            if ($request->hasFile('swim_guardian_image')) {

                if ($memberDetail->details['guardian_image'] && file_exists(public_path($memberDetail->details['guardian_image']))) {
                    unlink(public_path($memberDetail->details['guardian_image']));
                }

                $file = $request->file('swim_guardian_image');
                $filename = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($dest_path, $filename, 'public');
                $guardian_image_path = 'storage/' . $path;
            } else {
                $guardian_image_path = $memberDetail->details['guardian_image'];
            }


            $member->update([
                'name'        => $request->swim_name,
                'email'       => $request->swim_email,
                'phone'       => $request->swim_phone,
                'address'     => $request->swim_address,
                'image'       => $image_path
                // 'status'      => 'pending_approval'
            ]);


            $memberDetail->update([
                'details' => [
                    'age' => $request->swim_age,
                    'sex' => $request->swim_sex,
                    'height' => $request->swim_height,
                    'weight' => $request->swim_weight,
                    'pulse_rate' => $request->swim_pulse_rate,
                    'batch' => $request->swim_batch,
                    'vaccination' => $request->swim_vaccination,
                    'i_agree' => 1,
                    'disease' => $request->input('swim_disease', []),
                    'guardian_name' => $request->swim_guardian_name,
                    'guardian_occupation' => $request->swim_guardian_occupation,
                    'guardian_image' => $guardian_image_path
                ]
            ]);




            $card_no = $request->swim_card_id;

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

            return response()->json([
                // 'data' => $data,
                'statusCode' => 200,
                'message' => 'Member updated successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function view($id)
    {
        try {
            $clubId = club_id();

            $member = Member::where('club_id', $clubId)
                ->with([
                    'memberDetails',
                    'cardDetails',
                    'purchaseHistory.membershipPlanType',
                    'clubDetails',
                    'walletDetails',
                    'paymentHistory'
                ])
                ->find($id);

            return response()->json([
                'data' => $member,
                'statusCode' => 200,
                'message' => 'Member Fetched successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function membershipPlan($id)
    {
        try {
            $clubId = club_id();

            $membershipPlans = MembershipPurchaseHistory::where('club_id', $clubId)
                ->with('membershipPlanType')
                ->where('member_id', $id)
                ->get();

            return response()->json([
                'data' => $membershipPlans,
                'statusCode' => 200,
                'message' => 'Member Fetched successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function fetchWalletBalance($id)
    {
        try {
            // $clubId = club_id();

            $walletBalance = Wallet::where('member_id', $id)
                ->value('current_balance');

            $walletTransactionHistory = WalletTransaction::where('member_id', $id)
                ->orderBy('created_at', 'DESC')
                ->get();

            $data = [
                'walletBalance' => $walletBalance,
                'walletTransactionHistory' => $walletTransactionHistory
            ];

            return response()->json([
                'data' => $data,
                'statusCode' => 200,
                'message' => 'Wallet Balance Fetched successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }


    public function delete($id)
    {
        try {
            // $clubId = club_id();

            $member = Member::find($id);

            if (!$member) {
                return response()->json([
                    // 'data' => $data,
                    'statusCode' => 404,
                    'message' => 'Member Not Found'
                ]);
            }

            $card_mapping = MemberCardMapping::where('member_id', $member->id);

            if ($card_mapping) {
                $card_mapping->delete();
            }

            $member->delete();



            return response()->json([
                'statusCode' => 200,
                'message' => 'Member Deleted successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }
}
