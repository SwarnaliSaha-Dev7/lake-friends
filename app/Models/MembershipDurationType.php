<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipDurationType extends Model
{
    protected $fillable = [
        'name',
        'duration',
        'unit',
        'is_active'
    ];
}
