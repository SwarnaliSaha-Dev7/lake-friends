<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantOrderItem extends Model
{
    protected $fillable = [
        'restaurant_order_id',
        'food_item_id',
        'quantity',
        'unit',
        'unit_price',
        'offer_applied',
        'total_amount',
        'metadata',
    ];

    protected $casts = [
        'offer_applied' => 'array',
        'metadata'      => 'array',
    ];

    public function foodItem()
    {
        return $this->belongsTo(FoodItem::class);
    }
}
