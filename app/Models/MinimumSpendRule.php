<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class MinimumSpendRule extends Model
{
    use LogsModelChanges;
    protected $fillable = [
        'minimum_amount',
        'duration_type',
        'club_id',
    ];
}
