<?php

namespace App\Services;

use App\Models\ActionApproval;
use App\Models\Locker;
use App\Models\LockerAllocation;
use App\Models\LockerPrice;
use App\Models\Member;
use App\Models\PaymentHistory;
use App\Models\User;
use App\Notifications\ApprovalNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class LockerPurchaseService
{
    const CLUB_TERM_MONTHS = 12;
    const SWIM_TERM_MONTHS = 6;

    public function settings(int $clubId): LockerPrice
    {
        return LockerPrice::firstOrCreate(
            ['club_id' => $clubId],
            ['price' => 0, 'is_active' => 1, 'club_first_price' => 2500, 'club_renewal_price' => 200, 'swim_price' => 200, 'gst_percentage' => 18]
        );
    }

    /**
     * Default bill for this member's next locker purchase. A club member who has ever had
     * an approved locker pays the yearly rent; everyone else pays the first-time cost.
     */
    public function quote(Member $member, bool $isSwim): array
    {
        $settings = $this->settings($member->club_id);

        if ($isSwim) {
            $type   = 'swim';
            $amount = $settings->swim_price;
            $months = self::SWIM_TERM_MONTHS;
        } else {
            $hadLockerBefore = LockerAllocation::withTrashed()
                ->where('member_id', $member->id)
                ->where('status', 'active')
                ->exists();

            $type   = $hadLockerBefore ? 'renewal' : 'first';
            $amount = $hadLockerBefore ? $settings->club_renewal_price : $settings->club_first_price;
            $months = self::CLUB_TERM_MONTHS;
        }

        return [
            'purchase_type'  => $type,
            'taxable_amount' => (float) $amount,
            'gst_percentage' => (float) $settings->gst_percentage,
            'term_months'    => $months,
            'valid_till'     => Carbon::today()->addMonths($months)->toDateString(),
        ];
    }

    public function purchase(Request $request, bool $isSwim): array
    {
        $validator = Validator::make($request->all(), [
            'member_id'      => ['required', 'integer'],
            'locker_id'      => ['required', 'integer'],
            'taxable_amount' => ['required', 'numeric', 'min:0'],
            'gst_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'payment_mode'   => ['nullable', 'string', 'max:100'],
            'bank_id'        => ['nullable', 'integer'],
            'ac_head'        => ['nullable', 'string', 'max:191'],
            'remarks'        => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return ['statusCode' => 422, 'message' => $validator->errors()->first()];
        }

        $clubId = club_id();
        $member = Member::where('club_id', $clubId)->find($request->member_id);

        if (!$member) {
            return ['statusCode' => 404, 'message' => 'Member not found'];
        }

        $quote = $this->quote($member, $isSwim);

        // Staff may edit the bill; totals are always recomputed here from what they entered.
        $taxableAmount = round((float) $request->taxable_amount, 2);
        $gstPercentage = round((float) $request->gst_percentage, 2);
        $gstAmount     = round($taxableAmount * $gstPercentage / 100, 2);
        $netAmount     = round($taxableAmount + $gstAmount, 2);

        DB::beginTransaction();
        try {
            $locker = Locker::where('id', $request->locker_id)
                ->where('club_id', $clubId)
                ->where('is_active', 1)
                ->lockForUpdate()
                ->first();

            if (!$locker) {
                DB::rollBack();
                return ['statusCode' => 404, 'message' => 'Locker not found'];
            }

            $existingLockerAllocation = LockerAllocation::where('locker_id', $locker->id)->first();
            if ($existingLockerAllocation && $existingLockerAllocation->member_id != $member->id) {
                DB::rollBack();
                return ['statusCode' => 409, 'message' => 'Locker already allocated'];
            }

            $previousAllocation = LockerAllocation::where('member_id', $member->id)->latest('id')->first();
            if ($previousAllocation) {
                Locker::where('id', $previousAllocation->locker_id)->update(['status' => 'available']);
                $previousAllocation->delete();
            }

            $lockerAllocation = LockerAllocation::create([
                'club_id'    => $clubId,
                'locker_id'  => $locker->id,
                'member_id'  => $member->id,
                'start_date' => Carbon::today(),
                'end_date'   => Carbon::today()->addMonths($quote['term_months']),
                'price'      => $netAmount,
            ]);

            $locker->update(['status' => 'occupied']);

            PaymentHistory::create([
                'member_id'            => $member->id,
                'club_id'              => $clubId,
                'purpose'              => $isSwim ? 'swim_locker_purchase' : 'club_locker_purchase',
                'locker_allocation_id' => $lockerAllocation->id,
                'mr_no'                => generateMrNo(),
                'bill_no'              => generateBillNo(),
                'ac_head'              => $request->ac_head,
                'taxable_amount'       => $taxableAmount,
                'gst_percentage'       => $gstPercentage,
                'gst_amount'           => $gstAmount,
                'net_amount'           => $netAmount,
                'payment_mode'         => $request->payment_mode,
                'payment_status'       => 'success',
                'bank_id'              => $request->bank_id,
                'remarks'              => $request->remarks,
            ]);

            $approval = ActionApproval::create([
                'club_id'            => $clubId,
                'module'             => 'locker_purchase',
                'action_type'        => 'create',
                'entity_model'       => 'Member',
                'entity_id'          => $member->id,
                'membership_type_id' => $member->membership_type_id,
                'maker_user_id'      => Auth::id(),
                'request_payload'    => json_encode([
                    'locker_id'            => $locker->id,
                    'locker_allocation_id' => $lockerAllocation->id,
                    'purchase_type'        => $quote['purchase_type'],
                    'taxable_amount'       => $taxableAmount,
                    'gst_percentage'       => $gstPercentage,
                    'gst_amount'           => $gstAmount,
                    'net_amount'           => $netAmount,
                    'locker_price'         => $netAmount,
                ]),
            ]);

            if (Auth::user()->hasRole('admin')) {
                $approval->update([
                    'checker_user_id'         => Auth::id(),
                    'approved_or_rejected_at' => now(),
                    'status'                  => 'approved',
                ]);
                $lockerAllocation->update(['status' => 'active']);
            }

            if (Auth::user()->hasRole('operator')) {
                $approvers = User::role(['operator', 'admin'])->where('id', '!=', Auth::id())->get();
                Notification::send($approvers, new ApprovalNotification($approval));
            }

            DB::commit();

            return ['statusCode' => 200, 'message' => 'Locker purchased successfully'];
        } catch (\Throwable $th) {
            DB::rollBack();
            return ['statusCode' => 500, 'error' => $th->getMessage()];
        }
    }
}
