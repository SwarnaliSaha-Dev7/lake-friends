<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 
use App\Traits\LogsModelChanges;

class FineRule extends Model
{
    use LogsModelChanges;
    protected $fillable = [
        'rule_type',
        'per_day_fine_amount',
        'grace_days',
        'max_fine_cap',
        'club_id',
    ];
}
