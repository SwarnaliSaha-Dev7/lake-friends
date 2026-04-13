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
        'name',
        'volume_ml',
        'price',
        'is_active',
        'is_cocktail',
        'created_by',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_cocktail' => 'boolean',
        'volume_ml'   => 'integer',
        'price'       => 'float',
    ];

    public function foodItem()
    {
        return $this->belongsTo(FoodItem::class, 'food_item_id');
    }
}
