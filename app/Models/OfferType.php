<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class OfferType extends Model
{
    use LogsModelChanges;
    
    protected $fillable = ['name', 'slug'];
}
