<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use LogsModelChanges;
    protected $fillable = [
        'wallet_id',
        'member_id',
        'amount',
        'direction',
        'txn_type',
        'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payment()
    {
        return $this->hasOne(PaymentHistory::class, 'wallet_transaction_id');
    }
}
