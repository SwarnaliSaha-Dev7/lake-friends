<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use LogsModelChanges;
    protected $fillable = [
        'member_id',
        'current_balance'
    ];
}
