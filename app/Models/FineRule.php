<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FineRule extends Model
{
    protected $fillable = [
        'rule_type',
        'per_day_fine_amount',
        'grace_days',
        'max_fine_cap',
        'club_id',
    ];
}
