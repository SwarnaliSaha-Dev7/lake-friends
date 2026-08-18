<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MiscItemPrice extends Model
{
    protected $fillable = [
        'misc_item_id',
        'price',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    public function miscItem(): BelongsTo
    {
        return $this->belongsTo(MiscItem::class);
    }
}
