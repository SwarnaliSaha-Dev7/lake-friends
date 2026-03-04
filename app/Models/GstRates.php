<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GstRates extends Model
{
    protected $fillable = [
        'gst_percentage',
        'gst_type',
        'club_id ',
    ];
}
