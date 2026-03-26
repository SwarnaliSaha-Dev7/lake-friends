<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class RestaurantOrderItem extends Model
{
    use LogsModelChanges;

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
