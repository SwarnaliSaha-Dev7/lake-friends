<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class VerifyOtp extends Model
{
    use LogsModelChanges;
    protected $fillable = [
        'email',
        'otp',
        'otp_expire',
        'is_verified'
    ];
}
