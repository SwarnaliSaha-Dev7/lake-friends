<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Locker extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'locker_number',
        'is_active',
        'status',
        'club_id',
    ];
}

