<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifyOtp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'otp_expire',
    ];
}
