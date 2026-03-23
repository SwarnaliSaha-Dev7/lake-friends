<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LockerAllocation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'club_id',
        'locker_id',
        'member_id',
        'start_date',
        'end_date',
        'price',
        'status'
    ];

    public function locker(): BelongsTo
    {
        return $this->belongsTo(Locker::class, 'locker_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}
