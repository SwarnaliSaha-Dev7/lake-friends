<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodItemCurrentStock extends Model
{
    use LogsModelChanges;
    protected $fillable = [
        'warehouse_id',
        'club_id',
        'location_id',
        'food_items_id',
        'quantity',
        'unit',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class, 'food_items_id');
    }
}
