<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 
use App\Traits\LogsModelChanges;

class FineRule extends Model
{
    use LogsModelChanges;
    protected $fillable = [
        'club_id',
        'membership_plan_type_id',
        'rule_type',
        'per_day_fine_amount',
        'grace_days',
        'max_fine_cap',
    ];

    public function membershipPlanType()
    {
        return $this->belongsTo(MembershipPlanType::class, 'membership_plan_type_id');
    }
}
