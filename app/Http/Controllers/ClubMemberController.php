<?php

namespace App\Http\Controllers;

use App\Models\ActionApproval;
use App\Models\AddOn;
use App\Models\Bank;
use App\Models\FineRule;
use App\Models\Card;
use App\Models\GstRate;
use App\Models\Locker;
use App\Models\LockerAllocation;
use App\Models\LockerPrice;
use App\Models\Member;
use App\Models\MemberAddOn;
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
                    'latestApproval.checker:id,name',
                    'pendingFines'
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
                //'status'                  => 1
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

    public function renew(Request $request)
    {
        try {
            $clubId = club_id();

            $member = Member::where('club_id', $clubId)->findOrFail($request->member_id);

            $membershipType = MembershipType::where('name', 'Club Membership')
                ->where('club_id', $clubId)
                ->first();

            $plan = MembershipPlanType::where('id', $request->membership_plan_type_id)
                ->where('is_active', 1)
                ->first();

            if (!$plan) {
                return response()->json(['statusCode' => 404, 'message' => 'Membership plan not found']);
            }

            // Block if a renewal request is already pending approval
            $pendingRenewal = ActionApproval::where('club_id', $clubId)
                ->where('module', 'plan_renewal')
                ->where('status', 'pending')
                ->whereHasMorph('entity', [MembershipPurchaseHistory::class], function ($q) use ($member) {
                    $q->where('member_id', $member->id);
                })
                ->exists();

            if ($pendingRenewal) {
                return response()->json(['statusCode' => 422, 'message' => 'A renewal request is already pending approval for this member.']);
            }

            DB::beginTransaction();

            // Start date: day after current expiry or today if already expired
            // Include 'pending' so a newly approved plan's expiry is used as base
            $lastPurchase = MembershipPurchaseHistory::where('member_id', $member->id)
                ->whereIn('status', ['active', 'pending'])
                ->latest('expiry_date')
                ->first();

            $startDate = Carbon::today();
            if ($lastPurchase && $lastPurchase->expiry_date && Carbon::parse($lastPurchase->expiry_date)->isFuture()) {
                $startDate = Carbon::parse($lastPurchase->expiry_date)->addDay();
            }

            $expiryDate = $plan->is_lifetime ? null : $startDate->copy()->addMonths($plan->duration_months);

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

            // Mark pending fines as paid
            MemberFine::where('member_id', $member->id)
                ->where('status', 'pending')
                ->update(['status' => 'paid']);

            // Record projected FY shortfall as paid so year-end command doesn't double-charge
            $this->recordFyShortfallAtRenewal($member, $clubId);

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
                    'checker_user_id'          => Auth::id(),
                    'approved_or_rejected_at'  => now(),
                    'status'                   => 'approved',
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

    public function view($id)
    {
        try {
            $clubId = club_id();

            $member = Member::where('club_id', $clubId)
                ->with([
                    'memberDetails',
                    'cardDetails',
                    'clubDetails',
                    'walletDetails',
                    'paymentHistory',
                    'latestApproval.checker:id,name',
                    'pendingFines',
                ])
                ->find($id);

            $purchase_history = MembershipPurchaseHistory::with('membershipPlanType')
                ->where('member_id', $id)
                ->where('start_date', '<=', Carbon::now()->toDateString())
                ->where(function ($query) {
                    $query->whereNull('expiry_date')
                        ->orWhere('expiry_date', '>=', Carbon::now()->toDateString());
                })
                // ->where('status', 'active')
                ->whereIn('status', ['active', 'pending'])
                ->first();


            // Calculate suggested fine based on membership plan's fine rule
            $suggestedFine = [
                'amount'      => 0,
                'days'        => 0,
                'per_day'     => 0,
                'has_fine'    => false,
            ];

            $latestPurchase = $member->purchaseHistory
                ->where('status', 'active')
                ->sortByDesc('expiry_date')->first();

            // Only dynamically calculate expiry fine if no stored pending record exists
            $hasStoredExpiryFine = $member->pendingFines
                ->where('fine_type', 'membership_expiry_fine')
                ->isNotEmpty();

            if (!$hasStoredExpiryFine && $latestPurchase && $latestPurchase->expiry_date) {
                $expiry = Carbon::parse($latestPurchase->expiry_date);
                if ($expiry->isPast() && $latestPurchase->membershipPlanType) {
                    $plan = $latestPurchase->membershipPlanType;

                    // Get fine rule for this plan (plan-specific only — expiry fine is plan-type scoped)
                    $fineRule = FineRule::where('club_id', $clubId)
                        ->where('rule_type', 'membership_expiry')
                        ->where('membership_plan_type_id', $plan->id)
                        ->first()
                        ?? FineRule::where('club_id', $clubId)
                        ->where('rule_type', 'membership_expiry')
                        ->whereNull('membership_plan_type_id')
                        ->first();

                    // Only show fine if a rule is configured for this plan
                    if ($fineRule) {
                        $perDay       = (float) ($fineRule->per_day_fine_amount ?? 0);
                        $graceDays    = (int) ($fineRule->grace_days ?? 0);
                        $maxCap       = $fineRule->max_fine_cap ? (float) $fineRule->max_fine_cap : null;

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
            }

            // Calculate projected FY minimum spend shortfall (even if FY not yet closed)
            // Only applicable for plans marked as is_minimum_spend_applicable
            $fyShortfalls = [];
            $activePlan   = $latestPurchase?->membershipPlanType;
            $spendRule    = \App\Models\MinimumSpendRule::where('club_id', $clubId)->first();
            if ($spendRule && $activePlan?->is_minimum_spend_applicable) {
                $today      = Carbon::today();
                $monthlyMin = (float) $spendRule->minimum_amount / 12;

                // Determine current FY
                if ($today->month >= 4) {
                    $fyLabel = $today->year . '-' . ($today->year + 1);
                    $fyStart = Carbon::create($today->year, 4, 1);
                    $fyEnd   = Carbon::create($today->year + 1, 3, 31);
                } else {
                    $fyLabel = ($today->year - 1) . '-' . $today->year;
                    $fyStart = Carbon::create($today->year - 1, 4, 1);
                    $fyEnd   = Carbon::create($today->year, 3, 31);
                }

                // Pro-rated minimum: use first ACTIVE purchase start_date as join date
                $firstPurchase  = $member->purchaseHistory
                    ->where('status', 'active')
                    ->sortBy('start_date')->first();
                $joinDate       = $firstPurchase
                    ? Carbon::parse($firstPurchase->start_date)->startOfMonth()
                    : Carbon::parse($member->created_at)->startOfMonth();
                $effectiveStart = $joinDate->gt($fyStart) ? $joinDate : $fyStart;
                $months         = (int) $effectiveStart->diffInMonths($fyEnd->copy()->addDay());
                $minimumRequired = round($monthlyMin * $months, 2);

                // Get existing financial summary for current FY
                $fy = \App\Models\FinancialYear::where('club_id', $clubId)->where('fy_label', $fyLabel)->first();
                $totalSpend = 0;
                if ($fy) {
                    $summary = \App\Models\MemberFinancialSummary::where('member_id', $member->id)
                        ->where('financial_year_id', $fy->id)->first();
                    $totalSpend = $summary ? (float) $summary->total_spend : 0;
                }

                // Check if already has a pending min_spend_shortfall fine for current FY
                $existingFine = $member->pendingFines
                    ->where('fine_type', 'minimum_spend_shortfall')
                    ->first();

                $shortfall = max(0, $minimumRequired - $totalSpend);
                if ($shortfall > 0 && !$existingFine) {
                    $fyShortfalls[] = [
                        'fy_label'         => $fyLabel,
                        'minimum_required' => $minimumRequired,
                        'total_spend'      => $totalSpend,
                        'shortfall'        => $shortfall,
                    ];
                }
            }

            return response()->json([
                'data'           => $member,
                'purchase_history' => $purchase_history,
                'suggested_fine' => $suggestedFine,
                'fy_shortfalls'  => $fyShortfalls,
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

                        if($currentCardMapping){
                            $currentCard = Card::find($currentCardMapping->card_id);

                            if ($currentCard) {
                                $currentCard->update([
                                    'is_assigned' => 0
                                ]);
                            }
                        }

                        $newCard = Card::find($card_no);
                        if ($newCard) {
                            $newCard->update([
                                'is_assigned' => 1
                            ]);

                            if($currentCardMapping){
                                $currentCardMapping->update([
                                    'card_id' => $card_no
                                ]);
                            }
                            else{
                                MemberCardMapping::create([
                                    'card_id' => $card_no,
                                    'member_id' => $member->id
                                ]);
                            }
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
            } else {
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
                ->limit(5)
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

    public function fetchWalletHistory($id)
    {
        try {
            $walletTransactionHistory = WalletTransaction::with([
                'creator:id,name',
                'payment:id,wallet_transaction_id,remarks'
            ])
                ->where('member_id', $id)
                ->orderBy('created_at', 'DESC')
                ->get();

            return response()->json([
                'data' => $walletTransactionHistory,
                'statusCode' => 200,
                'message' => 'Wallet History Fetched successfully'
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
                        'created_at' => $t->created_at?->toDateTimeString(),
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
                        'created_at' => $p->created_at?->toDateTimeString(),
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
            if (Auth::user()->hasRole('admin')) {

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

                    $lockerAllocation = LockerAllocation::where('member_id', $memberId)->first();

                    $locker = null;

                    if ($lockerAllocation) {
                        $locker = Locker::find($lockerAllocation->locker_id);
                    }

                    if ($lockerAllocation) {
                        $lockerAllocation->delete();
                    }

                    if ($locker) {
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

            // $existingRequest = ActionApproval::where('entity_id', $member->id)
            //                 ->where('module', 'member_delete')
            //                 ->where('status', 'pending')
            //                 ->first();

            // if ($existingRequest) {
            //     return response()->json([
            //         'statusCode' => 409,
            //         'message' => 'Delete request already pending'
            //     ]);
            // }

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
            $clubId = club_id();
            $wallet = Wallet::where('member_id', $request->member_id)->lockForUpdate()->first();
            $amount = $request->amount;
            $memberDtls = Member::find($request->member_id);

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

            $memberAddOnIds = [];
            $addonStatus = Auth::user()->hasRole('admin') ? 'active' : 'pending';
            foreach ($request->addons as $addonId) {

                $addon = AddOn::find($addonId);

                $memberAddOn = MemberAddOn::create([
                    'member_id' => $request->member_id,
                    'add_on_id' => $addonId,
                    'price' => $addon->price,
                    'start_date' => $startDate,
                    'end_date'  => $endDate,
                    'status'    => $addonStatus,
                ]);

                $memberAddOnIds[] = $memberAddOn->id;
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

            $requestData = [
                'member_addon_ids' => $memberAddOnIds,
                'total_price' => $amount,
            ];

            $approval = ActionApproval::create([
                'club_id' => $clubId,
                'module' => 'add_on_purchase',
                'action_type' => 'create',
                'entity_model' => 'Member',
                'entity_id' => $request->member_id,
                'membership_type_id' => $memberDtls->membership_type_id,
                'maker_user_id' => Auth::id(),
                'request_payload' => json_encode($requestData)
            ]);

            if (Auth::user()->hasRole('admin')) {
                $approval->update([
                    'checker_user_id' => Auth::id(),
                    'approved_or_rejected_at' => now(),
                    'status' => 'approved'
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

            // fetch latest record per add_on_id for this member
            $addons = MemberAddOn::where('member_id', $request->member_id)
                ->whereIn('id', function ($query) use ($request) {
                    $query->selectRaw('MAX(id)')
                        ->from('member_add_ons')
                        ->where('member_id', $request->member_id)
                        ->groupBy('add_on_id');
                })
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

            $memberDtls = Member::find($request->member_id);

            $lockerAmount = LockerPrice::where('club_id', $clubId)->value('price') ?? 0;

            $wallet = Wallet::where('member_id', $request->member_id)->lockForUpdate()->first();
            if (!$wallet) {
                DB::rollBack();
                return response()->json([
                    'statusCode' => 404,
                    'message' => 'Wallet not found'
                ]);
            }

            if ($wallet->current_balance < $lockerAmount) {
                DB::rollBack();
                return response()->json([
                    'statusCode' => 422,
                    'message' => 'Insufficient wallet balance'
                ]);
            }

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

            if ($previousAllocations) {
                Locker::where('id', $previousAllocations->locker_id)->update([
                    'status' => 'available'
                ]);

                $previousAllocations->delete();
            }

            $lockerAllocation = LockerAllocation::create([
                'club_id' => $clubId,
                'locker_id' => $request->locker_id,
                'member_id' => $request->member_id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'price' => $lockerAmount,
            ]);

            // DEDUCT WALLET
            $wallet->current_balance -= $lockerAmount;
            $wallet->save();

            $locker->update([
                'status' => 'occupied'
            ]);

            // WALLET LOG
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'member_id' => $request->member_id,
                'amount'    => $lockerAmount,
                'direction' => 'debit',
                'txn_type'  => 'locker_purchase',
                'created_by' => auth()->id(),
            ]);

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
                'membership_type_id' => $memberDtls->membership_type_id,
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
            $allocation = LockerAllocation::with('locker:id,locker_number')
                ->where('member_id', $memberId)
                ->latest()
                ->first();

            if ($allocation) {
                $today = Carbon::today()->toDateString();
                $allocation->is_expired = $allocation->end_date
                    ? (Carbon::parse($allocation->end_date)->toDateString() < $today)
                    : false;
            }

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

    private function recordFyShortfallAtRenewal(Member $member, int $clubId): void
    {
        // Only applicable for Annual/Annual Silver plan types
        $activePurchase = $member->purchaseHistory()
            ->where('status', 'active')
            ->latest('expiry_date')
            ->with('membershipPlanType')
            ->first();
        if (!$activePurchase?->membershipPlanType?->is_minimum_spend_applicable) return;

        $spendRule = \App\Models\MinimumSpendRule::where('club_id', $clubId)->first();
        if (!$spendRule) return;

        $today      = Carbon::today();
        $monthlyMin = (float) $spendRule->minimum_amount / 12;

        if ($today->month >= 4) {
            $fyLabel = $today->year . '-' . ($today->year + 1);
            $fyStart = Carbon::create($today->year, 4, 1);
            $fyEnd   = Carbon::create($today->year + 1, 3, 31);
        } else {
            $fyLabel = ($today->year - 1) . '-' . $today->year;
            $fyStart = Carbon::create($today->year - 1, 4, 1);
            $fyEnd   = Carbon::create($today->year, 3, 31);
        }

        $firstPurchase  = $member->purchaseHistory()
            ->where('status', 'active')->orderBy('start_date')->first();
        $joinDate       = $firstPurchase
            ? Carbon::parse($firstPurchase->start_date)->startOfMonth()
            : Carbon::parse($member->created_at)->startOfMonth();
        $effectiveStart  = $joinDate->gt($fyStart) ? $joinDate : $fyStart;
        $months          = (int) $effectiveStart->diffInMonths($fyEnd->copy()->addDay());
        $minimumRequired = round($monthlyMin * $months, 2);

        $fy         = \App\Models\FinancialYear::where('club_id', $clubId)->where('fy_label', $fyLabel)->first();
        $totalSpend = 0;
        if ($fy) {
            $summary    = \App\Models\MemberFinancialSummary::where('member_id', $member->id)
                ->where('financial_year_id', $fy->id)->first();
            $totalSpend = $summary ? (float) $summary->total_spend : 0;
        }

        $shortfall = max(0, $minimumRequired - $totalSpend);
        if ($shortfall <= 0) return;

        // Ensure FY record exists
        if (!$fy) {
            $fy = \App\Models\FinancialYear::firstOrCreate(
                ['club_id' => $clubId, 'fy_label' => $fyLabel],
                ['start_date' => $fyStart->toDateString(), 'end_date' => $fyEnd->toDateString(), 'is_closed' => false]
            );
        }

        // Create or update as paid — prevents year-end command from double-charging
        \App\Models\MemberFine::firstOrCreate(
            ['club_id' => $clubId, 'member_id' => $member->id, 'financial_year_id' => $fy->id, 'fine_type' => 'minimum_spend_shortfall'],
            [
                'fine_amount' => $shortfall,
                'reference_amount' => $shortfall,
                'fine_date' => $today->toDateString(),
                'status' => 'paid',
                'notes' => "FY {$fyLabel} shortfall collected at renewal"
            ]
        );
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
