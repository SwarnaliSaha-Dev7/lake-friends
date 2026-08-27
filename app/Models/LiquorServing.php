<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiquorServing extends Model
{
    use SoftDeletes, LogsModelChanges;

    protected $fillable = [
        'club_id',
        'food_item_id',
        'secondary_food_item_id',
        'secondary_quantity',
        'name',
        'volume_ml',
        'price',
        'is_active',
        'is_cocktail',
        'created_by',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'is_cocktail'         => 'boolean',
        'volume_ml'           => 'integer',
        'secondary_quantity'  => 'integer',
        'price'               => 'float',
    ];

    public function foodItem()
    {
        return $this->belongsTo(FoodItem::class, 'food_item_id');
    }

    // Optional mixer (e.g. soda) whose stock is deducted alongside the base spirit
    // when this cocktail is sold — never billed as a separate line.
    public function secondaryFoodItem()
    {
        return $this->belongsTo(FoodItem::class, 'secondary_food_item_id');
    }
}
