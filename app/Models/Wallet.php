<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use SoftDeletes, LogsModelChanges;

    protected $fillable = [
        'member_id',
        'current_balance'
    ];
}
