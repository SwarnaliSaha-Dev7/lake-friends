<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class MembershipPlanType extends Model
{
    use LogsModelChanges;

    protected $fillable = [
        'club_id',
        'membership_type_id',
        'name',
        'duration_months',
        'is_lifetime',
        'is_active',
        'price',
        'is_minimum_spend_applicable',
    ];

    protected $casts = [
        'duration_months'              => 'integer',
        'is_minimum_spend_applicable'  => 'boolean',
    ];
}
