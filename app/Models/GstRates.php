<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class GstRates extends Model
{
    use LogsModelChanges;

    protected $fillable = [
        'gst_percentage',
        'gst_type',
        'club_id ',
    ];
}
