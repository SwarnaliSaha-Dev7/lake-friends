<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\AddOn;
use App\Models\Bank;
use App\Models\Card;
use App\Models\GstRate;
use App\Models\Locker;
use App\Models\LockerAllocation;
use App\Models\LockerPrice;
use App\Models\Member;
use App\Models\MemberAddOn;
use App\Models\MemberCardMapping;
use App\Models\MembershipFormDetail;
use App\Models\MembershipPlanType;
use App\Models\MembershipPurchaseHistory;
use App\Models\MembershipType;
use App\Models\PaymentHistory;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\ApprovalNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ClubMemberController extends Controller
{
    public function list()
    {
        try {

            $title      = 'Club Member list';
            $page_title = 'Manage Club Member';

            $clubId     = club_id();
            $membershipPlanTypeList = [];

            $membershipType = MembershipType::where('name', 'Club Membership')
                ->where('club_id', $clubId)
                ->first();
            $membershipTypeId = $membershipType->id;

            $clubMembershipPlanTypeList = MembershipPlanType::where('membership_type_id', $membershipTypeId)
                ->where('is_active', 1)
                ->get();

            $gstPercentage = GstRate::where('club_id', Auth::user()->club_id)
                ->where('gst_type', 'plan_purchase')
                ->value('gst_percentage') ?? 0;

            $bankList = Bank::where('club_id', $clubId)->get();

            // $cards = Card::doesntHave('memberMapping')
            //     ->where('club_id', $clubId)
            //     ->where('status', 'active')
            //     ->get();
            $cards = Card::where('is_assigned', 0)
                ->where('club_id', $clubId)
                ->where('status', 'active')
                ->get();

            $members = Member::where('club_id', $clubId)
                ->with([
                    'memberDetails',
                    'cardDetails',
                    'purchaseHistory',
                    'walletDetails',
                    'latestApproval.checker:id,name'
                ])
                ->whereHas('memberDetails', function ($query) use ($membershipTypeId) {
                    $query->where('membership_type_id', $membershipTypeId);
                })
                ->orderBy('created_at', 'DESC')
                ->get();


            $addonList = AddOn::where('club_id', $clubId)
            ->where('is_active', 1)
            ->get();

            $lockers = Locker::where('is_active', 1)
                                ->where('club_id', $clubId)
                                ->where('status', 'available')
                                ->select('id', 'locker_number')
                                ->get();

            $lockerPrice = LockerPrice::where('club_id', $clubId)->first();

            return view('club_member.list', compact(
                'title',
                'page_title',
                'clubMembershipPlanTypeList',
                'gstPercentage',
                'bankList',
                'cards',
                'members',
                'addonList',
                'lockers',
                'lockerPrice'
            ));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function store(Request $request)
    {
        // return $request->card_id;
        try {

            $clubId = club_id();

            $requestData = $request->except(
                'image',
                'spouse_image'
            );

            //fetch the membership type id
            $membershipType = MembershipType::where('name', 'Club Membership')
                ->where('club_id', $clubId)
                ->first();

            $membershipTypeId = $membershipType->id;

            // Check if email already exists in same club
            $exists = Member::where('email', $request->email)
                ->where('membership_type_id', $membershipTypeId)
                ->where('club_id', $clubId)
                ->exists();
                // ->first();

            if ($exists) {
                return response()->json([
                    'statusCode' => 409,
                    // 'message' => 'Email already exists'
                    'message' => 'Member already registered with this membership type'
                ]);
            }


            DB::beginTransaction();

            // Generate Member Code (example)
            // $memberCode = 'LF-' . time();



            $dest_path = 'uploads/images';
            $image_path = null;
            if ($request->hasFile('image')) {

                $file = $request->file('image');
                $filename = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($dest_path, $filename, 'public');
                $image_path = 'storage/' . $path;
                $requestData['image'] = $image_path;
            }

            $member = Member::create([
                'club_id'     => $clubId,
                'membership_type_id' => $membershipTypeId,
                // 'member_code' => $memberCode,
                'name'        => ucwords($request->name),
                'email'       => $request->email,
                'phone'       => $request->phone,
                'address'     => $request->address,
                'image'       => $image_path,
                // 'status'      => 1
            ]);

            $spouse_image_path = null;

            if ($request->hasFile('spouse_image')) {

                $file = $request->file('spouse_image');
                $filename = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($dest_path, $filename, 'public');
                $spouse_image_path = 'storage/' . $path;
                $requestData['spouse_image'] = $spouse_image_path;
            }


            MembershipFormDetail::create([
                'member_id' => $member->id,
                'membership_type_id' => $membershipTypeId,
                'details' => [
                    'blood_grp' => $request->blood_grp,
                    'spouse_name' => ucwords($request->spouse_name),
                    'spouse_email' => $request->spouse_email,
                    'spouse_phone' => $request->spouse_phone,
                    'spouse_blood_grp' => $request->spouse_blood_grp,
                    'spouse_address' => $request->spouse_address,
                    'spouse_image' => $spouse_image_path,
                ]
            ]);

            // Get Plan Details
            $plan = MembershipPlanType::where('id', $request->membership_plan_type_id)
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

            // $fee = $plan->price;
            $fee = $request->taxable_amount;
            $fineAmount = 0;
            //$netAmount = $fee + $fineAmount;

            // $gstPercentage = GstRate::where('club_id', $clubId)
            //     ->where('gst_type', 'plan_purchase')
            //     ->value('gst_percentage') ?? 0;

            $gstPercentage = $request->gstPercentage;


            $gst_amt = ($fee * $gstPercentage) / 100;

            $netAmount = $fee + $fineAmount + $gst_amt;

            // Store Purchase History
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

            //$card_no = $request->card_id;

            $payment_history = PaymentHistory::create([
                'member_id' => $member->id,
                'club_id' =>  $clubId,
                'purpose' => 'plan_purchase',
                'membership_purchase_history_id' => $purchase_history->id,
                'wallet_transaction_id' => null,
                'mr_no' => generateMrNo(),
                'bill_no' => generateBillNo(),
                'ac_head' => $request->ac_head,
                'taxable_amount' => $fee,
                'gst_percentage' => $gstPercentage,
                'gst_amount' => $gst_amt,
                'net_amount' => $netAmount,
                'payment_mode' => $request->payment_mode,
                'payment_status' => 'success',
                'bank_id' => $request->bank_id,
                'remarks' => $request->remarks
            ]);

            $currentCard = Card::find($request->card_id);
            if ($currentCard) {
                $currentCard->update([
                    'is_assigned' => 1
                ]);
            }

            $card_mapping = MemberCardMapping::create([
                'card_id' => $request->card_id,
                'member_id' => $member->id
            ]);

            Wallet::create([
                'member_id' => $member->id,
                'current_balance' => 0
            ]);

            $approval = ActionApproval::create([
                'club_id' => $clubId,
                'module' => 'member_create',
                'action_type' => 'create',
                'entity_model' => 'Member',
                'membership_type_id' => $membershipTypeId,
                'entity_id' => $member->id,
                'maker_user_id' => Auth::id(),
                'request_payload' => json_encode($requestData)
            ]);

            if (Auth::user()->hasRole('admin')) {

                $member->update([
                    'status' => 'active'
                ]);

                $approval->update([
                    'checker_user_id' => Auth::id(),
                    'approved_or_rejected_at' => now(),
                    'status' => 'approved'
                ]);

                $purchase_history->update([
                    'status' => 'active'
                ]);
            }

            if (Auth::user()->hasRole('operator')) {
                $approvers = User::role(['operator', 'admin'])
                    ->where('id', '!=', Auth::id())
                    ->get();


                Notification::send($approvers, new ApprovalNotification($approval));
            }

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
                    'paymentHistory',
                    'latestApproval.checker:id,name'
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

    public function update(Request $request)
    {
        try {

            $clubId = club_id();
            $memberId = $request->member_id;

            // $memberId = $request->member_id;

            $exists = ActionApproval::where('club_id', $clubId)
                ->where('entity_id', $memberId)
                ->where(function ($query) {
                    $query->where('module', 'member_create')
                        ->orWhere('module', 'member_edit');
                })
                ->where('status', 'pending')
                ->exists();

            if ($exists) {
                return response()->json([
                    'statusCode' => 409,
                    'message' => 'An edit request is already pending.'
                ]);
            }
            // $exists = Member::where('email', $request->email)
            //     ->where('club_id', $clubId)
            //     ->where('id', '!=', $memberId)
            //     ->exists();

            // if ($exists) {
            //     return response()->json([
            //         'statusCode' => 409,
            //         'message' => 'Email already exists'
            //     ]);
            // }

            DB::beginTransaction();

            $member = Member::find($memberId);

            $dest_path = 'uploads/images';
            $image_path = null;
            if ($request->hasFile('image')) {

                // if ($member->image && file_exists(public_path($member->image))) {
                //     unlink(public_path($member->image));
                // }

                $file = $request->file('image');
                $filename = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($dest_path, $filename, 'public');
                $image_path = 'storage/' . $path;
            } else {
                $image_path = $member->image;
            }

            $memberDetail = MembershipFormDetail::where('member_id', $memberId)->first();

            $spouse_image_path = null;
            if ($request->hasFile('spouse_image')) {

                // if ($memberDetail->details['spouse_image'] && file_exists(public_path($memberDetail->details['spouse_image']))) {
                //     unlink(public_path($memberDetail->details['spouse_image']));
                // }

                $file = $request->file('spouse_image');
                $filename = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($dest_path, $filename, 'public');
                $spouse_image_path = 'storage/' . $path;
            } else {
                $spouse_image_path = $memberDetail->details['spouse_image'] ?? '';
            }

            $data = $request->except(
                'image',
                'spouse_image'
            );

            $data['image'] = $image_path;
            $data['spouse_image'] = $spouse_image_path;

            $card_no = $request->card_id;

            if ($card_no) {

                $newCard = Card::find($card_no);
                if ($newCard) {
                    $newCard->update([
                        'is_assigned' => 1
                    ]);
                }
            }

            //check if any update happend start
            $currentDetails = $memberDetail->details ?? [];
            unset($currentDetails['image'], $currentDetails['spouse_image']);

            $newDetails = [
                'blood_grp' => $request->blood_grp,
                'spouse_name' => $request->spouse_name,
                'spouse_email' => $request->spouse_email,
                'spouse_phone' => $request->spouse_phone,
                'spouse_blood_grp' => $request->spouse_blood_grp,
                'spouse_address' => $request->spouse_address,
            ];

            ksort($currentDetails);
            ksort($newDetails);

            $detailsChanged = $currentDetails != $newDetails;

            $member->fill([
                'name'        => $request->name,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'address'     => $request->address,
                'status'      => $request->club_status
            ]);

            if (
                $member->isDirty() ||
                $detailsChanged ||
                $request->hasFile('image') ||
                $request->hasFile('spouse_image') ||
                $request->filled('card_id')
            ) {
                // return "changed";
                $approval = ActionApproval::create([
                    'club_id' => $clubId,
                    'module' => 'member_edit',
                    'action_type' => 'update',
                    'entity_model' => 'Member',
                    'membership_type_id' => $memberDetail->membership_type_id,
                    'entity_id' => $memberId,
                    'maker_user_id' => Auth::id(),
                    'request_payload' => json_encode($data)
                ]);

                if (Auth::user()->hasRole('admin')) {
                    $approval->update([
                        'checker_user_id' => Auth::id(),
                        'approved_or_rejected_at' => now(),
                        'status' => 'approved'
                    ]);

                    $member->update([
                        'name'        => $request->name,
                        'email'       => $request->email,
                        'phone'       => $request->phone,
                        'address'     => $request->address,
                        'image'       => $image_path,
                        // 'status'      => 'active'
                    ]);


                    $memberDetail->update([
                        'details' => [
                            'blood_grp' => $request->blood_grp,
                            'spouse_name' => $request->spouse_name,
                            'spouse_email' => $request->spouse_email,
                            'spouse_phone' => $request->spouse_phone,
                            'spouse_blood_grp' => $request->spouse_blood_grp,
                            'spouse_address' => $request->spouse_address,
                            'spouse_image' => $spouse_image_path,
                        ]
                    ]);




                    $card_no = $request->card_id;

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
                }

                if (Auth::user()->hasRole('operator')) {
                    $approvers = User::role(['operator', 'admin'])
                        ->where('id', '!=', Auth::id())
                        ->get();


                    Notification::send($approvers, new ApprovalNotification($approval));
                }

                DB::commit();

                return response()->json([
                    // 'data' => $data,
                    'statusCode' => 200,
                    'message' => 'Member updated successfully'
                ]);
            }
            else{
                DB::commit();
                return response()->json([
                    // 'data' => $data,
                    'statusCode' => 200,
                    'message' => 'No changes were made'
                ]);
            }
            //check if any update happend end


            // $member->update([
            //     'name'        => $request->name,
            //     'email'       => $request->email,
            //     'phone'       => $request->phone,
            //     'address'     => $request->address,
            //     'image'       => $image_path
            //     // 'status'      => 'pending_approval'
            // ]);


            // $memberDetail->update([
            //     'details' => [
            //         'blood_grp' => $request->blood_grp,
            //         'spouse_name' => $request->spouse_name,
            //         'spouse_email' => $request->spouse_email,
            //         'spouse_phone' => $request->spouse_phone,
            //         'spouse_blood_grp' => $request->spouse_blood_grp,
            //         'spouse_address' => $request->spouse_address,
            //         'spouse_image' => $spouse_image_path,
            //     ]
            // ]);




            // $card_no = $request->card_id;

            // if ($card_no) {
            //     $currentCardMapping = MemberCardMapping::where('member_id', $memberId)->first();

            //     $currentCard = Card::find($currentCardMapping->card_id);
            //     if ($currentCard) {
            //         $currentCard->update([
            //             'is_assigned' => 0
            //         ]);
            //     }

            //     $newCard = Card::find($card_no);
            //     if ($newCard) {
            //         $newCard->update([
            //             'is_assigned' => 1
            //         ]);

            //         $currentCardMapping->update([
            //             'card_id' => $card_no
            //         ]);
            //     }
            // }

        } catch (\Throwable $th) {
            DB::rollBack();

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
                ->where('status', '!=', 'pending')
                ->get();

            return response()->json([
                'data' => $membershipPlans,
                'statusCode' => 200,
                'message' => 'Membership Plan Fetched successfully'
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

            $walletTransactionHistory = WalletTransaction::with([
                'creator:id,name',
                'payment:id,wallet_transaction_id,remarks'
            ])
                ->where('member_id', $id)
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

    public function rechargeWalletBalance(Request $request)
    {
        try {
            $clubId = club_id();
            $memberId = $request->wallet_member_id;
            $rechargeAmount = $request->wallet_recharge_amount;
            $paymentMode = $request->wallet_payment_mode;
            $acHead = $request->wallet_ac_head;
            $bank = $request->wallet_bank_id;
            $remarks = $request->wallet_remarks;

            $purpose = 'recharge';

            $wallet = Wallet::where('member_id', $memberId)->first();

            // if (!$wallet) {
            //     return response()->json([
            //         'statusCode' => 404,
            //         'error' => "Wallet Not Found",
            //     ]);
            // }

            $currentBalance = $wallet->current_balance + $rechargeAmount;
            $wallet->update([
                'current_balance' => $currentBalance
            ]);

            $walletTransactionHistory = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'member_id' => $memberId,
                'amount' => $rechargeAmount,
                'direction' => 'credit',
                'txn_type' => $purpose,
                'created_by' => auth()->id(),
            ]);

            $paymentHistory = PaymentHistory::create([
                'member_id' => $memberId,
                'club_id' => $clubId,
                'purpose' => $purpose,
                'wallet_transaction_id' => $walletTransactionHistory->id,
                'bank_id' => $bank,
                'ac_head' => $acHead,
                'mr_no' => generateMrNo(),
                'bill_no' => generateBillNo(),
                'taxable_amount' => $rechargeAmount,
                'net_amount' => $rechargeAmount,
                'payment_mode' => $paymentMode,
                'payment_status' => 'success',
                'remarks' => $remarks
            ]);

            return response()->json([
                'data' => $currentBalance,
                'statusCode' => 200,
                'message' => 'Wallet Balance added successfully'
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
            $clubId = club_id();

            $member = Member::find($id);

            if (!$member) {
                return response()->json([
                    // 'data' => $data,
                    'statusCode' => 404,
                    'message' => 'Member Not Found'
                ]);
            }

            //ADMIN → skip approval
            if(Auth::user()->hasRole('admin')){

                try {

                    DB::beginTransaction();

                    $card_mapping = MemberCardMapping::where('member_id', $member->id)->first();

                    $card = null;

                    if ($card_mapping) {
                        $card = Card::find($card_mapping->card_id);
                    }

                    if ($card_mapping) {
                        $card_mapping->delete();
                    }

                    if ($card) {
                        $card->update([
                            'is_assigned' => 0
                        ]);
                    }

                    $member->delete();

                    // $approvalRequests = ActionApproval::where('club_id', $clubId)
                    //                                   ->where('entity_id', $id)
                    //                                   ->where('status', 'pending')
                    //                                   ->delete();

                    DB::commit();

                    return response()->json([
                        'statusCode' => 200,
                        'message' => 'Member Deleted successfully'
                    ]);


                } catch (\Throwable $th) {
                    return $th->getMessage();
                }

            }

            // If operator → send approval request

            $existingRequest = ActionApproval::where('entity_id', $member->id)
                            ->where('module', 'member_delete')
                            ->where('status', 'pending')
                            ->first();

            if ($existingRequest) {
                return response()->json([
                    'statusCode' => 409,
                    'message' => 'Delete request already pending'
                ]);
            }

            $memberDetail = $member->memberDetails;

            ActionApproval::create([
                            'club_id' => $clubId,
                            'module' => 'member_delete',
                            'membership_type_id' => $memberDetail->membership_type_id,
                            'entity_model' => 'Member',
                            'entity_id' => $member->id,
                            'maker_user_id' => Auth::id(),
                            'status' => 'pending'
                        ]);

            return response()->json([
                'statusCode' => 200,
                'message' => 'Delete request sent for approval'
            ]);

            // $card_mapping = MemberCardMapping::where('member_id', $member->id)->first();

            // $card = null;

            // if ($card_mapping) {
            //     $card = Card::find($card_mapping->card_id);
            // }

            // if ($card_mapping) {
            //     $card_mapping->delete();
            // }

            // if ($card) {
            //     $card->update([
            //         'is_assigned' => 0
            //     ]);
            // }

            // $member->delete();

            // $approvalRequests = ActionApproval::where('club_id', $clubId)
            //     ->where('entity_id', $id)
            //     ->where('status', 'pending')
            //     ->delete();



            // return response()->json([
            //     'statusCode' => 200,
            //     'message' => 'Member Deleted successfully'
            // ]);
        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function purchaseAddOn(Request $request)
    {
        try {
            // return $request;

            DB::beginTransaction();
            $wallet = Wallet::where('member_id', $request->member_id)->lockForUpdate()->first();
            $amount = $request->amount;

            // $wallet = Wallet::where('member_id', $id)->value('current_balance');

            // CHECK BALANCE
            if (!$wallet || $wallet->current_balance < $amount) {
                return response()->json([
                    'statusCode' => 422,
                    'message' => 'Insufficient wallet balance'
                ]);
            }

             //DEDUCT WALLET
            $wallet->current_balance -= $amount;
            $wallet->save();

            $startDate = carbon::now();
            $endDate   = carbon::now()->addMonths(6);

            foreach ($request->addons as $addonId) {

                $addon = AddOn::find($addonId);

                MemberAddOn::create([
                        'member_id' => $request->member_id,
                        'add_on_id' => $addonId,
                        'price' => $addon->price,
                        'start_date'=> $startDate,
                        'end_date'  => $endDate,
                    ]);
            }

            // WALLET LOG
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'member_id' => $request->member_id,
                'amount'    => $amount,
                'direction' => 'debit',
                'txn_type'  => 'add_on_purchase',
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'data' => '',
                'statusCode' => 200,
                'message' => 'Add-ons purchased successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function memberAddonList(Request $request)
    {
        try {

            //fetch the activated add on
            $addons = MemberAddOn::where('member_id', $request->member_id)
                ->whereDate('end_date', '>=', carbon::now())
                ->get();

            return response()->json([
                'statusCode' => 200,
                'message'    => 'Add-ons fetched successfully',
                'data'       => $addons
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function purchaseLocker(Request $request)
    {
        try {
            $request->validate([
                'member_id' => ['required', 'integer'],
                'locker_id' => ['required', 'integer'],
            ]);

            $clubId = club_id();
            $startDate = Carbon::today();
            $endDate = Carbon::today()->addMonths(6);

            DB::beginTransaction();

            $locker = Locker::where('id', $request->locker_id)
                ->where('club_id', $clubId)
                ->where('is_active', 1)
                ->lockForUpdate()
                ->first();

            if (!$locker) {
                DB::rollBack();
                return response()->json([
                    'statusCode' => 404,
                    'message' => 'Locker not found'
                ]);
            }

            $existingLockerAllocation = LockerAllocation::where('locker_id', $request->locker_id)->first();
            if ($existingLockerAllocation && $existingLockerAllocation->member_id != $request->member_id) {
                DB::rollBack();
                return response()->json([
                    'statusCode' => 409,
                    'message' => 'Locker already allocated'
                ]);
            }

            $previousAllocations = LockerAllocation::where('member_id', $request->member_id)
                                                    ->latest('id')
                                                    ->first();

            if($previousAllocations){
                Locker::where('id', $previousAllocations->locker_id)->update([
                    'status' => 'available'
                ]);

                $previousAllocations->delete();
            }

            LockerAllocation::create([
                'club_id' => $clubId,
                'locker_id' => $request->locker_id,
                'member_id' => $request->member_id,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            $locker->update([
                'status' => 'occupied'
            ]);

            DB::commit();

            return response()->json([
                'statusCode' => 200,
                'message' => 'Locker purchased successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function getMemberLockerAllocation($memberId)
    {
        try {
            $today = Carbon::today()->toDateString();

            $allocation = LockerAllocation::with('locker:id,locker_number')
                ->where('member_id', $memberId)
                ->whereDate('start_date', '<=', $today)
                ->where(function ($query) use ($today) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $today);
                })
                ->latest()
                ->first();

            return response()->json([
                'statusCode' => 200,
                'data' => $allocation
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }

    // public function mealPriceEdit(Request $request)
    // {
    //     try {
    //         $data['mealPrice'] = SchoolMealPrice::where('school_id', $request->schoolId)->first();

    //         return response()->json([
    //             'data' => $data,
    //             'statusCode' => 200,
    //         ]);

    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'statusCode' => 500,
    //             // 'error' => $th->getMessage(),
    //         ]);
    //     }
    // }
}
