<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberCardMapping extends Model
{
    protected $fillable = [
        'card_id',
        'member_id'
    ];
}
