<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipFormDetail extends Model
{
    protected $fillable = [
        'member_id',
        'membership_type_id',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];
}
