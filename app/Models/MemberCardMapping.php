<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class MemberCardMapping extends Model
{
    use SoftDeletes, LogsModelChanges;
    protected $fillable = [
        'club_id',
        'member_id',
        'card_id',
        'holder_type',
    ];

    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id');
    }
}
