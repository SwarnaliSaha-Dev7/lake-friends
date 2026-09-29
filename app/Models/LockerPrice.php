<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LockerPrice extends Model
{
    use SoftDeletes, LogsModelChanges;

    protected $fillable = [
        'club_id',
        'price',
        'is_active',
        'club_first_price',
        'club_renewal_price',
        'swim_price',
        'gst_percentage',
    ];
}
