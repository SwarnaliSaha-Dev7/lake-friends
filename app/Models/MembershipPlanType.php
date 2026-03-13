<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class MembershipPlanType extends Model
{
    use LogsModelChanges;
    protected $casts = [
        'duration_months' => 'integer',
    ];
}
