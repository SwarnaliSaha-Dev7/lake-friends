<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Locker extends Model
{
    use SoftDeletes, LogsModelChanges;

    protected $fillable = [

        'locker_number',
        'is_active',
        'status',
        'club_id',
    ];

    public function latestAllocation(): HasOne
    {
        return $this->hasOne(LockerAllocation::class, 'locker_id')->latestOfMany();
    }
}

