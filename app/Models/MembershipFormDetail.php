<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipFormDetail extends Model
{
     use SoftDeletes;
     protected $fillable = [
        'member_id',
        'membership_type_id',
        'details'
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function membershipType()
    {
        return $this->belongsTo(MembershipType::class, 'membership_type_id');
    }
}
