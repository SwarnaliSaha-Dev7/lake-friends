<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'club_id',
        'member_code',
        'name',
        'email',
        'phone',
        'address',
        'status',
        'image',
        'signature'
    ];
}
