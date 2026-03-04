<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MinimumSpendRule extends Model
{
    protected $fillable = [
        'minimum_amount',
        'duration_type',
        'club_id',
    ];
}
