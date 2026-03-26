<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentHistory extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'member_id',
        'club_id',
        'purpose',
        'membership_purchase_history_id',
        'wallet_transaction_id',
        'locker_allocation_id',
        'mr_no',
        'bill_no',
        'ac_head',
        'taxable_amount',
        'gst_percentage',
        'gst_amount',
        'net_amount',
        'payment_mode',
        'payment_status',
        'bank_id',
        'remarks'
    ];

    public function lockerAllocation()
    {
        return $this->belongsTo(LockerAllocation::class);
    }
}
