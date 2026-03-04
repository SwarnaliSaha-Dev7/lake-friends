<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'member_id',
        'amount',
        'direction',
        'txn_type'
    ];
}
