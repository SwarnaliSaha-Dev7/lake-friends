<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\Bank;
use App\Models\Card;
use App\Models\FineRule;
use App\Models\GstRate;
use App\Models\Locker;
use App\Models\LockerPrice;
use App\Models\LockerAllocation;
use App\Models\Member;
use App\Models\MemberCardMapping;
use App\Models\MemberFine;
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
use Illuminate\Support\Facades\Validator;

//

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

            // // $cards = Card::doesntHave('memberMapping')
            // //     ->where('club_id', $clubId)
            // //     ->where('status', 'active')
            // //     ->get();
            // $cards = Card::where('is_assigned', 0)
            //     ->where('club_id', $clubId)
            //     ->where('status', 'active')
            //     ->get();


            $members = Member::where('club_id', $clubId)
                ->with([
                    'memberDetails',
                    'cardDetails',
                    'purchaseHistory',
                    'walletDetails',
                    'latestApproval.checker:id,name',
                    'pendingFines',
                ])
                ->whereHas('memberDetails', function ($query) use ($membershipTypeId) {
                    $query->where('membership_type_id', $membershipTypeId);
                })
                ->orderBy('created_at', 'DESC')
                ->get();

            // dd($members);
            // swimming locker part start

            $lockers = Locker::where('is_active', 1)
                                ->where('club_id', $clubId)
                                ->where('status', 'available')
                                ->select('id', 'locker_number')
                                ->get();

            $lockerPrice = LockerPrice::where('club_id', $clubId)->first();

            // swimming locker part end

            return view('swimming_member.list', compact(
                'title',
                'page_title',
                'membershipPlanList',
                'gstPercentage',
                'bankList',
                // 'cards',
                'members',
                'lockers',
                'lockerPrice'
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

            $membershipType = MembershipType::where('name', 'Swimming Membership')
            ->where('club_id', $clubId)
            ->first();

            $membershipTypeId = $membershipType->id;

            $exists = Member::where('email', $request->swim_email)
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

            $requestData = $request->except(
                'image',
                'spouse_image'
            );

            $lastMember = Member::where('club_id', $clubId)
                                ->where('membership_type_id', $membershipTypeId)
                                ->whereNotNull('member_code')
                                ->orderBy('id', 'desc')
                                ->lockForUpdate()
                                ->first();

            if ($lastMember && $lastMember->member_code) {
                $lastCode = (int) $lastMember->member_code;
                $newCode = $lastCode + 1;
            } else {
                $newCode = 1;
            }

            $memberCode = str_pad($newCode, 4, '0', STR_PAD_LEFT);

            $dest_path = 'uploads/images';
            $image_path = null;
            if ($request->hasFile('swim_image')) {

                $file = $request->file('swim_image');
                $filename = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($dest_path, $filename, 'public');
                $image_path = 'storage/' . $path;
                $requestData['swim_image'] = $image_path;
            }

            $member = Member::create([
                'club_id'     => $clubId,
                'membership_type_id' => $membershipTypeId,
                'member_code' => $memberCode,
                'name'        => ucwords($request->swim_name),
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
                $requestData['swim_guardian_image'] = $guardian_image_path;
            }

            MembershipFormDetail::create([
                'member_id' => $member->id,
                'membership_type_id' => $membershipTypeId,
                'details' => [
                    'police_station' => $request->swim_police_station,
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
                    'guardian_name' => ucwords($request->swim_guardian_name),
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

            // $fee = $plan->price;
            $fee = $request->swim_taxable_amt;
            $fineAmount = 0;

            // $gstPercentage = GstRate::where('club_id', $clubId)
            //     ->where('gst_type', 'plan_purchase')
            //     ->value('gst_percentage') ?? 0;

            $gstPercentage = $request->swim_gst_percent;

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
                // 'status'                  => 1
            ]);


            // $card_no = $request->swim_card_no;

            $payment_history = PaymentHistory::create([
                'member_id' => $member->id,
                'club_id' =>  $clubId,
                'purpose' => 'plan_purchase',
                'membership_purchase_history_id' => $purchase_history->id,
                'wallet_transaction_id' => null,
                'mr_no' => generateMrNo(),
                'bill_no' => generateBillNo(),
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

            // $currentCard = Card::find($request->swim_card_id);
            // if ($currentCard) {
            //     $currentCard->update([
            //         'is_assigned' => 1
            //     ]);
            // }

            // $card_mapping = MemberCardMapping::create([
            //     'card_id' => $request->swim_card_id,
            //     'member_id' => $member->id
            // ]);

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
                $approval->update([
                    'checker_user_id' => Auth::id(),
                    'approved_or_rejected_at' => now(),
                    'status' => 'approved'
                ]);

                $member->update([
                    'status' => 'active'
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

    public function update(Request $request)
    {
        try {
            // return $request;

            $clubId = club_id();
            $memberId = $request->member_id;

            $exists = ActionApproval::where('club_id', $clubId)
                ->where('entity_id', $memberId)
                ->where(function ($query) {
                    $query->where('module', 'member_create')
                        ->orWhere('module', 'member_edit')
                        ->orWhere('module', 'member_delete');
                })
                ->where('status', 'pending')
                ->exists();

            if ($exists) {
                return response()->json([
                    'statusCode' => 409,
                    'message' => 'An edit request is already pending.'
                ]);
            }

            // DB::beginTransaction();

            $member = Member::find($memberId);

            $dest_path = 'uploads/images';
            $image_path = null;
            if ($request->hasFile('swim_image')) {

                // if ($member->image && file_exists(public_path($member->image))) {
                //     unlink(public_path($member->image));
                // }

                $file = $request->file('swim_image');
                $filename = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($dest_path, $filename, 'public');
                $image_path = 'storage/' . $path;
            } else {
                $image_path = $member->image;
            }

            $memberDetail = MembershipFormDetail::where('member_id', $memberId)->first();

            // $guardian_image_path = null;
            if ($request->hasFile('swim_guardian_image')) {

                // if ($memberDetail->details['guardian_image'] && file_exists(public_path($memberDetail->details['guardian_image']))) {
                //     unlink(public_path($memberDetail->details['guardian_image']));
                // }

                $file = $request->file('swim_guardian_image');
                $filename = time() . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($dest_path, $filename, 'public');
                $guardian_image_path = 'storage/' . $path;
            } else {
                $guardian_image_path = $memberDetail->details['guardian_image'];
            }

            $data = $request->except(
                'swim_guardian_image',
                'swim_image'
            );

            $data['swim_image'] = $image_path;
            $data['swim_guardian_image'] = $guardian_image_path;

            // $card_no = $request->swim_card_id;

            // if ($card_no) {
            //     $newCard = Card::find($card_no);

            //     if ($newCard) {
            //         $newCard->update([
            //             'is_assigned' => 1
            //         ]);
            //     }
            // }

            //check if any update happend start
            $membershipType = MembershipType::where('name', 'Swimming Membership')
                ->where('club_id', $clubId)
                ->first();

            $membershipTypeId = $membershipType->id;

            $formDetail = MembershipFormDetail::where('member_id', $memberId)
                ->where('membership_type_id', $membershipTypeId)
                ->first();
            $currentDetails = $formDetail->details ?? [];
            unset($currentDetails['image'], $currentDetails['guardian_image']);

            // normalize disease field
            $currentDetails['disease'] = $currentDetails['disease'] ?? [];
            sort($currentDetails['disease']);

            // return $currentDetails;
            $newDetails = [
                'police_station' => $request->swim_member_police_station,
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
                'guardian_occupation' => $request->swim_guardian_occupation
            ];

            // normalize disease field
            sort($newDetails['disease']);

            ksort($currentDetails);
            ksort($newDetails);

            // return [$currentDetails , $newDetails];

            $detailsChanged = $currentDetails != $newDetails;

            $member->fill([
                'name' => $request->swim_name,
                'email' => $request->swim_email,
                'phone' => $request->swim_phone,
                'address' => $request->swim_address,
                'status' => $request->swim_status,
            ]);

            if (
                $member->isDirty() ||
                $detailsChanged ||
                $request->hasFile('swim_image') ||
                $request->hasFile('swim_guardian_image') ||
                $request->filled('swim_card_id')
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
                            'guardian_image' => $guardian_image_path,
                            'police_station' => $request->swim_member_police_station,
                        ]
                    ]);




                    // $card_no = $request->swim_card_id;

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
            //     'name'        => $request->swim_name,
            //     'email'       => $request->swim_email,
            //     'phone'       => $request->swim_phone,
            //     'address'     => $request->swim_address,
            //     'image'       => $image_path
            //     // 'status'      => 'pending_approval'
            // ]);


            // $memberDetail->update([
            //     'details' => [
            //         'age' => $request->swim_age,
            //         'sex' => $request->swim_sex,
            //         'height' => $request->swim_height,
            //         'weight' => $request->swim_weight,
            //         'pulse_rate' => $request->swim_pulse_rate,
            //         'batch' => $request->swim_batch,
            //         'vaccination' => $request->swim_vaccination,
            //         'i_agree' => 1,
            //         'disease' => $request->input('swim_disease', []),
            //         'guardian_name' => $request->swim_guardian_name,
            //         'guardian_occupation' => $request->swim_guardian_occupation,
            //         'guardian_image' => $guardian_image_path
            //     ]
            // ]);




            // $card_no = $request->swim_card_id;

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
                    'latestApproval.checker:id,name',
                    'pendingFines',
                ])
                ->find($id);

            // Calculate suggested expiry fine
            $suggestedFine = [
                'amount'   => 0,
                'days'     => 0,
                'per_day'  => 0,
                'has_fine' => false,
            ];

            $latestPurchase = $member->purchaseHistory
                ->where('status', 'active')
                ->sortByDesc('expiry_date')->first();

            $hasStoredExpiryFine = $member->pendingFines
                ->where('fine_type', 'membership_expiry_fine')
                ->isNotEmpty();

            if (!$hasStoredExpiryFine && $latestPurchase && $latestPurchase->expiry_date) {
                $expiry = Carbon::parse($latestPurchase->expiry_date);
                if ($expiry->isPast() && $latestPurchase->membershipPlanType) {
                    $plan         = $latestPurchase->membershipPlanType;
                    $durationDays = max(1, (int) $plan->duration_months * 30);
                    $perDay       = round((float) $plan->price / $durationDays, 4);

                    $fineRule = FineRule::where('club_id', $clubId)
                        ->where('rule_type', 'membership_expiry')
                        ->where('membership_plan_type_id', $plan->id)
                        ->first()
                        ?? FineRule::where('club_id', $clubId)
                            ->where('rule_type', 'membership_expiry')
                            ->whereNull('membership_plan_type_id')
                            ->first();

                    $graceDays    = (int) ($fineRule?->grace_days ?? 0);
                    $maxCap       = $fineRule?->max_fine_cap ? (float) $fineRule->max_fine_cap : null;
                    $daysExpired  = (int) $expiry->diffInDays(Carbon::today());
                    $billableDays = max(0, $daysExpired - $graceDays);
                    $fineAmount   = round($billableDays * $perDay, 2);

                    if ($maxCap && $fineAmount > $maxCap) {
                        $fineAmount = $maxCap;
                    }

                    $suggestedFine = [
                        'amount'   => $fineAmount,
                        'days'     => $billableDays,
                        'per_day'  => $perDay,
                        'has_fine' => $fineAmount > 0,
                    ];
                }
            }

            return response()->json([
                'data'           => $member,
                'suggested_fine' => $suggestedFine,
                'statusCode'     => 200,
                'message'        => 'Member Fetched successfully'
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
                ->where('status', '!=', 'pending')
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

    public function memberLedger($id)
    {
        try {
            $walletTxns = WalletTransaction::with('creator:id,name', 'payment:id,wallet_transaction_id,remarks')
                ->where('member_id', $id)
                ->get()
                ->map(function ($t) {
                    return [
                        'source'     => 'wallet',
                        'purpose'    => $t->txn_type,
                        'direction'  => $t->direction,
                        'amount'     => (float) $t->amount,
                        'remarks'    => $t->payment?->remarks,
                        'maker'      => $t->creator?->name,
                        'created_at' => $t->created_at,
                    ];
                });

            // Only include payment histories NOT linked to a wallet transaction (direct cash payments)
            $payments = PaymentHistory::where('member_id', $id)
                ->whereNull('wallet_transaction_id')
                ->get()
                ->map(function ($p) {
                    return [
                        'source'     => 'payment',
                        'purpose'    => $p->purpose,
                        'direction'  => 'debit',
                        'amount'     => (float) $p->net_amount,
                        'remarks'    => $p->remarks,
                        'maker'      => null,
                        'created_at' => $p->created_at,
                    ];
                });

            $ledger = $walletTxns
                ->merge($payments)
                ->sortByDesc('created_at')
                ->values();

            return response()->json(['statusCode' => 200, 'data' => $ledger]);
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

            if (!$wallet) {
                return response()->json([
                    'statusCode' => 404,
                    'error' => "Wallet Not Found",
                ]);
            }

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
                'mr_no' => generateMrNo(),
                'bill_no' => generateBillNo(),
                'bank_id' => $bank,
                'ac_head' => $acHead,
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

    // swimming locker part start
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

            $memberDtls = Member::find($request->member_id);

            $lockerAmount = LockerPrice::where('club_id', $clubId)->value('price') ?? 0;

            // $wallet = Wallet::where('member_id', $request->member_id)->lockForUpdate()->first();
            // if (!$wallet) {
            //     DB::rollBack();
            //     return response()->json([
            //         'statusCode' => 404,
            //         'message' => 'Wallet not found'
            //     ]);
            // }

            // if ($wallet->current_balance < $lockerAmount) {
            //     DB::rollBack();
            //     return response()->json([
            //         'statusCode' => 422,
            //         'message' => 'Insufficient wallet balance'
            //     ]);
            // }

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

                $previousAllocations->delete(); //
            }

            $lockerAllocation = LockerAllocation::create([
                'club_id' => $clubId,
                'locker_id' => $request->locker_id,
                'member_id' => $request->member_id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'price' => $lockerAmount,
            ]);

            // // DEDUCT WALLET
            // $wallet->current_balance -= $lockerAmount;
            // $wallet->save();

            $locker->update([
                'status' => 'occupied'
            ]);

            PaymentHistory::create([
                'member_id' => $request->member_id,
                'club_id' => $clubId,
                'purpose' => 'swim_locker_purchase',
                'locker_allocation_id' => $lockerAllocation->id,
                'mr_no' => generateMrNo(),
                'bill_no' => generateBillNo(),
                'taxable_amount' => $lockerAmount,
                'net_amount' => $lockerAmount,
                'payment_status' => 'success',
            ]);

            // // WALLET LOG
            // WalletTransaction::create([
            //     'wallet_id' => $wallet->id,
            //     'member_id' => $request->member_id,
            //     'amount'    => $lockerAmount,
            //     'direction' => 'debit',
            //     'txn_type'  => 'locker_purchase',
            //     'created_by' => auth()->id(),
            // ]);

            $requestData = [
                'locker_id' => $request->locker_id,
                'locker_allocation_id' => $lockerAllocation->id,
                'locker_price' => $lockerAmount,
            ];

            $approval = ActionApproval::create([
                'club_id' => $clubId,
                'module' => 'locker_purchase',
                'action_type' => 'create',
                'entity_model' => 'Member',
                'entity_id' => $request->member_id,
                'membership_type_id' => $memberDtls->membership_type_id ,
                'maker_user_id' => Auth::id(),
                'request_payload' => json_encode($requestData)
            ]);

            if (Auth::user()->hasRole('admin')) {

                $approval->update([
                    'checker_user_id' => Auth::id(),
                    'approved_or_rejected_at' => now(),
                    'status' => 'approved'
                ]);

                $lockerAllocation->update([
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
                'statusCode' => 200,
                'message' => 'Locker purchased successfully'
            ]);
        }

        catch (\Throwable $th) {
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
            $allocation = LockerAllocation::with('locker:id,locker_number')
                ->where('member_id', $memberId)
                ->latest()
                ->first();

            $paymentHistory = PaymentHistory::where('member_id', $memberId)
                ->where('purpose', 'swim_locker_purchase')
                ->orderBy('id', 'DESC')
                ->get(['id','created_at', 'net_amount', 'payment_status']);

            if ($allocation) {
                $today = Carbon::today()->toDateString();
                $allocation->is_expired = $allocation->end_date
                    ? (Carbon::parse($allocation->end_date)->toDateString() < $today)
                    : false;

                $allocation->payment_history = $paymentHistory;
            }

            return response()->json([
                'statusCode' => 200,
                'data' => $allocation,
                'payment_history' => $paymentHistory
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }
    // swimming locker part end

    public function getReceipt($id)
    {
        try {
            $clubId = club_id();

             $member = Member::where('club_id', $clubId)
                             ->with([
                                 'memberDetails',
                                 ])
                             ->find($id);

            if (!$member) {
                return response()->json([
                    'statusCode' => 404,
                    'message' => 'Member not found'
                ]);
            }

            $details = $member->memberDetails->details ?? [];

            $purchase = $member->purchaseHistory->first();

            $date = $purchase?->start_date?->format('d-m-Y');

            $data = [
                    'name' => $member->name,
                    'address' => $member->address,
                    'phone' => $member->phone,
                    'member_code' => $member->member_code,
                    'age' => $details['age'] ?? '-',
                    'height' => $details['height'] ?? '-',
                    'weight' => $details['weight'] ?? '-',
                    'pulse_rate' => $details['pulse_rate'] ?? '-',
                    'police_station' => $details['police_station'] ?? '-',
                    'gender' => $details['sex'] ?? '-',
                    'image' => $details['image'] ?? $member->image,
                    'date' => $date,
                ];

            return response()->json([
                'statusCode' => 200,
                'data' => $data,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
            ]);
        }
    }


    public function renew(Request $request)
    {
        try {
            $clubId = club_id();

            $member = Member::where('club_id', $clubId)->findOrFail($request->member_id);

            $membershipType = MembershipType::where('name', 'Swimming Membership')
                ->where('club_id', $clubId)
                ->first();

            $plan = MembershipPlanType::where('id', $request->membership_plan_type_id)
                ->where('is_active', 1)
                ->first();

            if (!$plan) {
                return response()->json(['statusCode' => 404, 'message' => 'Membership plan not found']);
            }

            DB::beginTransaction();

            $lastPurchase = MembershipPurchaseHistory::where('member_id', $member->id)
                ->where('status', 'active')
                ->latest('expiry_date')
                ->first();

            $startDate = Carbon::today();
            if ($lastPurchase && $lastPurchase->expiry_date && Carbon::parse($lastPurchase->expiry_date)->isFuture()) {
                $startDate = Carbon::parse($lastPurchase->expiry_date)->addDay();
            }

            $expiryDate    = $plan->is_lifetime ? null : $startDate->copy()->addMonths($plan->duration_months);
            $taxableAmount = $request->taxable_amount;
            $fineAmount    = $request->fine_amount ?? 0;
            $gstPercentage = $request->gst_percentage;
            $gstAmount     = ($taxableAmount * $gstPercentage) / 100;
            $netAmount     = $taxableAmount + $fineAmount + $gstAmount;

            $purchase = MembershipPurchaseHistory::create([
                'club_id'                 => $clubId,
                'member_id'               => $member->id,
                'membership_type_id'      => $membershipType->id,
                'membership_plan_type_id' => $plan->id,
                'fee'                     => $taxableAmount,
                'fine_amount'             => $fineAmount,
                'net_amount'              => $netAmount,
                'start_date'              => $startDate,
                'expiry_date'             => $expiryDate,
                'status'                  => 'pending',
            ]);

            PaymentHistory::create([
                'member_id'                      => $member->id,
                'club_id'                        => $clubId,
                'purpose'                        => 'plan_renewal',
                'membership_purchase_history_id' => $purchase->id,
                'mr_no'                          => generateMrNo(),
                'bill_no'                        => generateBillNo(),
                'ac_head'                        => $request->ac_head,
                'taxable_amount'                 => $taxableAmount,
                'gst_percentage'                 => $gstPercentage,
                'gst_amount'                     => $gstAmount,
                'net_amount'                     => $netAmount,
                'payment_mode'                   => $request->payment_mode,
                'payment_status'                 => 'success',
                'bank_id'                        => $request->bank_id,
                'remarks'                        => $request->remarks,
            ]);

            // Mark pending expiry fines as paid
            MemberFine::where('member_id', $member->id)
                ->where('status', 'pending')
                ->where('fine_type', 'membership_expiry_fine')
                ->update(['status' => 'paid']);

            $approval = ActionApproval::create([
                'club_id'            => $clubId,
                'module'             => 'plan_renewal',
                'action_type'        => 'create',
                'entity_model'       => 'MembershipPurchaseHistory',
                'membership_type_id' => $membershipType->id,
                'entity_id'          => $purchase->id,
                'maker_user_id'      => Auth::id(),
                'request_payload'    => json_encode($request->except('_token')),
            ]);

            if (Auth::user()->hasRole('admin')) {
                $purchase->update(['status' => 'active']);
                $approval->update([
                    'checker_user_id'         => Auth::id(),
                    'approved_or_rejected_at' => now(),
                    'status'                  => 'approved',
                ]);
            }

            if (Auth::user()->hasRole('operator')) {
                $approvers = User::role(['operator', 'admin'])->where('id', '!=', Auth::id())->get();
                Notification::send($approvers, new ApprovalNotification($approval));
            }

            DB::commit();

            return response()->json(['statusCode' => 200, 'message' => 'Renewal submitted successfully']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
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

            $memberId = $member->id;

            $exists = ActionApproval::where('club_id', $clubId)
                ->where('entity_id', $memberId)
                ->where(function ($query) {
                    $query->where('module', 'member_create')
                        ->orWhere('module', 'member_edit')
                        ->orWhere('module', 'member_delete');
                })
                ->where('status', 'pending')
                ->exists();

            if ($exists) {
                return response()->json([
                    'statusCode' => 409,
                    'message' => 'A request is already pending.'
                ]);
            }

            //ADMIN → skip approval
            if(Auth::user()->hasRole('admin')){

                try {

                    DB::beginTransaction();

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

                    $lockerAllocation = LockerAllocation::where('member_id', $memberId)->first();

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

            $payload = [
                'member_id' => $member->id,
                // 'name' => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
            ];

            ActionApproval::create([
                            'club_id' => $clubId,
                            'module' => 'member_delete',
                            'action_type' => 'delete',
                            'membership_type_id' => $memberDetail->membership_type_id,
                            'entity_model' => 'Member',
                            'entity_id' => $member->id,
                            'maker_user_id' => Auth::id(),
                            'status' => 'pending',
                            'request_payload' => json_encode($payload)
                        ]);

            return response()->json([
                'statusCode' => 200,
                'message' => 'Delete request sent for approval'
            ]);

            // $card_mapping = MemberCardMapping::where('member_id', $member->id)->latest()->first();

            // $card = Card::find($card_mapping->card_id);

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
}
