<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddOn extends Model
{
    protected $fillable = [
        'club_id',
        'name',
        // 'is_locker',
        'price',
        'is_active'
    ];

    // public function members()
    // {
    //     return $this->belongsToMany(Member::class, 'member_add_ons', 'add_on_id', 'member_id')
    //                 ->withPivot('price');
    // }

    public function memberAddOns()
    {
        return $this->hasMany(MemberAddOn::class);
    }

}
