<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    protected $fillable = [
        'member_id',
        'club_id',
        'purpose',
        'membership_purchase_history_id',
        'wallet_transaction_id',
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
}
