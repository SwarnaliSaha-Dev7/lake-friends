<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodItemPrice extends Model
{
    protected $fillable = [
        'item_id',
        'price',
        'effective_from',
        'effective_to',
        'approval_status',
        'is_active',
    ];
}
