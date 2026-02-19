<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipDurationType extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'duration_months',
        'is_lifetime',
        'club_id'
    ];
}
