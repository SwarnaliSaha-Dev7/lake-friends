<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'club_id', 'offer_type_id', 'name', 'applies_to',
        'discount_value', 'min_amount', 'buy_qty', 'get_qty',
        'start_at', 'end_at', 'status', 'conditions',
    ];

    public function offerType()
    {
        return $this->belongsTo(OfferType::class, 'offer_type_id');
    }

    public function offerItems()
    {
        return $this->hasMany(OfferItem::class);
    }
}
