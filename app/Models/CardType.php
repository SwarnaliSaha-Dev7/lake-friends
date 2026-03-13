<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardType extends Model
{
    use SoftDeletes, LogsModelChanges;

    protected $fillable = [
        'name',
        'club_id'
    ];
}
