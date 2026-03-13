<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class AddOn extends Model
{
    use LogsModelChanges;
    protected $fillable = [
        'club_id',
        'name',
        'price',
        'is_active'
    ];
}
