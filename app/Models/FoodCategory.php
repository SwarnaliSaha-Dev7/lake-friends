<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FoodCategory extends Model
{
    use SoftDeletes, LogsModelChanges;

    protected $fillable = [
        'club_id',
        'name',
        'item_type'
    ];
}
