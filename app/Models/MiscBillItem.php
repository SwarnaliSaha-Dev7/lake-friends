<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MiscBillItem extends Model
{
    protected $fillable = [
        'misc_bill_id',
        'misc_item_id',
        'quantity',
        'unit',
        'unit_price',
        'gst_percentage',
        'total_amount',
        'gst_amount',
    ];

    public function miscBill(): BelongsTo
    {
        return $this->belongsTo(MiscBill::class);
    }

    public function miscItem(): BelongsTo
    {
        // withTrashed: a misc item can be deleted after it's been billed — past
        // bills/receipts should still show what was actually sold.
        return $this->belongsTo(MiscItem::class)->withTrashed();
    }
}
