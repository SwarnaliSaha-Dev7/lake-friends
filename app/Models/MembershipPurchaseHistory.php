<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipPurchaseHistory extends Model
{
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
        'end_date' => 'date',
    ];

    public function membershipPlanType(): BelongsTo
    {
        return $this->belongsTo(MembershipPlanType::class, 'membership_plan_type_id');
    }
}
