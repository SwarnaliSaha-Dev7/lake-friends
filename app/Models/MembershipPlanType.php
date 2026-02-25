<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipPlanType extends Model
{
    protected $casts = [
        'duration_months' => 'integer',
    ];
}
