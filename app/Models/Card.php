<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Card extends Model
{
    use SoftDeletes;

    protected $fillable = [
    'card_no',
    'status',
    'issued_at',
    'club_id',
    'card_type_id',
    ];

    public static function statuses(){
        return ['pending', 'active', 'blocked', 'lost', 'damaged'];
    }
}
