<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'club_id',
        'member_code',
        'name',
        'email',
        'phone',
        'address',
        'status',
        'image',
        'signature'
    ];

    public function memberDetails()
    {
        return $this->hasOne(MembershipFormDetail::class, 'member_id');
    }

    public function cardDetails()
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

    public function paymentHistory()
    {
        return $this->hasMany(PaymentHistory::class, 'member_id');
    }

    public function purchaseHistory()
    {
        return $this->hasMany(MembershipPurchaseHistory::class, 'member_id');
    }
}
