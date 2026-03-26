<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class RestaurantOrder extends Model
{
    use LogsModelChanges;

    protected $fillable = [
        'club_id',
        'session_id',
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

    public function session()
    {
        return $this->belongsTo(OrderSession::class, 'session_id');
    }
}
