<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LockerAllocation extends Model
{
    use SoftDeletes, LogsModelChanges;

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
