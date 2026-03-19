<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferItem extends Model
{
    protected $fillable = ['offer_id', 'food_items_id', 'rules'];

    protected $casts = ['rules' => 'array'];

    public function foodItem()
    {
        return $this->belongsTo(FoodItem::class, 'food_items_id');
    }
}
