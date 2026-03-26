<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberAddOn extends Model
{
    use SoftDeletes, LogsModelChanges;
    protected $fillable = [
        'member_id',
        'add_on_id',
        'start_date',
        'end_date',
        'price',
        'status',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function addOn()
    {
        // return $this->belongsTo(AddOn::class);
        return $this->belongsTo(AddOn::class, 'add_on_id');
    }
}
