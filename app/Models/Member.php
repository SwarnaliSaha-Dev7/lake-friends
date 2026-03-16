<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsModelChanges;

class Member extends Model
{
    use SoftDeletes,LogsModelChanges;
    protected $fillable = [
        'club_id',
        'membership_type_id',
        'member_code',
        'name',
        'email',
        'phone',
        'address',
        'status',
        'image',
        'signature'
    ];

    public function memberDetails(): HasOne
    {
        return $this->hasOne(MembershipFormDetail::class, 'member_id');
    }

    public function cardDetails(): HasOneThrough
    {
        return $this->hasOneThrough(
            Card::class,
            MemberCardMapping::class,
            'member_id',
            'id',
            'id',
            'card_id'
        );
    }

    public function paymentHistory(): HasMany
    {
        return $this->hasMany(PaymentHistory::class, 'member_id')->latest();
    }

    public function purchaseHistory(): HasMany
    {
        return $this->hasMany(MembershipPurchaseHistory::class, 'member_id')->latest();
    }

    public function clubDetails(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    public function walletDetails(): HasOne
    {
        return $this->hasOne(Wallet::class, 'member_id');
    }

    public function latestApproval(){
        return $this->hasOne(ActionApproval::class, 'entity_id')
                    ->where('club_id', auth()->user()->club_id)
                    ->whereIn('module', ['member_create', 'member_edit'])
                    ->where('status', 'approved')
                    ->orderByDesc('created_at');
    }

    public function addOns()
    {
        return $this->belongsToMany(AddOn::class, 'member_add_ons', 'member_id', 'add_on_id')
                    ->withPivot('price');
    }

    // public function addOns()
    // {
    //     return $this->hasMany(MemberAddOn::class);
    // }
}
