<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLedger extends Model
{
    use LogsModelChanges;
    protected $fillable = [
        'club_id',
        'warehouse_id',
        'location_id',
        'food_items_id',
        'movement_type',
        'direction',
        'quantity',
        'unit',
        'reference_type',
        'status'
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    // public function warehouse(): BelongsTo
    // {
    //     return $this->belongsTo(Warehouse::class);
    // }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class, 'food_items_id');
    }
}
