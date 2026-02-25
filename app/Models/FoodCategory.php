<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FoodCategory extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'club_id',
        'name',
        'item_type'
    ];
}
