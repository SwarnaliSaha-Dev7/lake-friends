<?php

namespace App\Models;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipPurchaseHistory extends Model
{
    use SoftDeletes, LogsModelChanges;
    protected $fillable = [
        'club_id',
        'member_id',
        'membership_type_id',
        'membership_plan_type_id',
        'fee',
        'fine_amount',
        'net_amount',
        'start_date',
        'expiry_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function membershipPlanType(): BelongsTo
    {
        return $this->belongsTo(MembershipPlanType::class, 'membership_plan_type_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}
