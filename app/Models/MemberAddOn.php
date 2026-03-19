<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberAddOn extends Model
{
    protected $fillable = [
        'member_id',
        'add_on_id',
        'start_date',
        'end_date',
        'price',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function addOn()
    {
        return $this->belongsTo(AddOn::class);
    }
}
