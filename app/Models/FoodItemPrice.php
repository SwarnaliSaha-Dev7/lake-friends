<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class, 'item_id');
    }
}
