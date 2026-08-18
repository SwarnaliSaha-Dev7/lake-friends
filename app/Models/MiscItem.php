<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MiscItem extends Model
{
    use SoftDeletes, LogsModelChanges;

    protected $fillable = [
        'club_id',
        'misc_category_id',
        'name',
        'code',
        'description',
        'unit',
        'gst_percentage',
        'is_price_editable',
        'image',
        'is_active',
    ];

    protected $casts = [
        'gst_percentage'     => 'decimal:2',
        'is_price_editable'  => 'boolean',
        'is_active'          => 'boolean',
    ];

    public function miscItemPrice(): HasOne
    {
        return $this->hasOne(MiscItemPrice::class, 'misc_item_id')
                    ->where('is_active', 1)
                    ->orderByDesc('created_at');
    }

    public function priceHistory(): HasMany
    {
        return $this->hasMany(MiscItemPrice::class, 'misc_item_id');
    }

    public function miscItemCat(): BelongsTo
    {
        return $this->belongsTo(MiscCategory::class, 'misc_category_id');
    }
}
