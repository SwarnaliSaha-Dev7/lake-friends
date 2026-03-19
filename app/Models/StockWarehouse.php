<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockWarehouse extends Model
{
    protected $table = 'stock_warehouses';

    protected $fillable = ['club_id', 'stock_name'];

    public function currentStocks()
    {
        return $this->hasMany(FoodItemCurrentStock::class, 'warehouse_id');
    }

    public function ledgers()
    {
        return $this->hasMany(StockLedger::class, 'warehouse_id');
    }
}
