<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class StockLedger extends Model
{
    use LogsModelChanges;

    protected $table = 'stock_ledgers';

    protected $fillable = [
        'warehouse_id',
        'location_id',
        'food_items_id',
        'movement_type',
        'direction',
        'quantity',
        'unit_price',
        'reference_type',
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
