<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class MemberCardMapping extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'club_id',
        'member_id',
        'card_id'
    ];
}
