<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class MembershipFormDetail extends Model
{
    use LogsModelChanges;
    protected $fillable = [
        'member_id',
        'membership_type_id',
        'details'
    ];

    protected $casts = [
        'details' => 'array',
    ];
}
