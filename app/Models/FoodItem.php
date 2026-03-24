<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class FoodItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'club_id',
        'category_id',
        'item_type',
        'name',
        'image',
        'code',
        'sku',
        'unit',
        'size_ml',
        'price',
        'is_beer',
        'low_stock_alert_qty',
        'is_active',
    ];

    public function foodItemPrice(): HasOne
    {
        return $this->hasOne(FoodItemPrice::class,'item_id')
                    ->where('is_active',1)
                    ->orderByDesc('created_at');
    }

    public function foodItemCat(): BelongsTo
    {
        return $this->belongsTo(FoodCategory::class,'category_id');
    }
}
