<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderSession extends Model
{
    protected $fillable = [
        'club_id',
        'member_id',
        'session_no',
        'status',
        'taxable_amount',
        'discount_amount',
        'gst_percentage',
        'gst_amount',
        'net_amount',
        'bill_no',
        'mr_no',
        'wallet_transactions_id',
    ];

    public function orders()
    {
        return $this->hasMany(RestaurantOrder::class, 'session_id');
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
