<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MiscBill extends Model
{
    use LogsModelChanges;

    protected $fillable = [
        'club_id',
        'bill_no',
        'mr_no',
        'buyer_name',
        'buyer_contact',
        'ac_head',
        'payment_mode',
        'payment_reference',
        'subtotal',
        'discount_amount',
        'gst_amount',
        'net_amount',
        'status',
        'remarks',
        'created_by',
        'cancelled_by',
        'cancelled_at',
        'cancel_reason',
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MiscBillItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
