<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'expiry_date' => 'date',
    ];
}
