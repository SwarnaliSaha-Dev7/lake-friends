<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantOrder extends Model
{
    protected $fillable = [
        'club_id',
        'member_id',
        'wallet_transactions_id',
        'order_no',
        'mr_no',
        'bill_no',
        'ac_head',
        'taxable_amount',
        'discount_amount',
        'gst_percentage',
        'gst_amount',
        'net_amount',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(RestaurantOrderItem::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function walletTransaction()
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_transactions_id');
    }
}
