<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class FoodItemCurrentStock extends Model
{
    use LogsModelChanges;
    protected $table = 'food_item_current_stocks';

    protected $fillable = [
        'warehouse_id',
        'location_id',
        'food_items_id',
        'quantity',
    ];

    public function foodItem()
    {
        return $this->belongsTo(FoodItem::class, 'food_items_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(StockWarehouse::class, 'warehouse_id');
    }
}
