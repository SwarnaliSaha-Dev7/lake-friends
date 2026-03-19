<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialYear extends Model
{
    protected $fillable = ['club_id', 'fy_label', 'start_date', 'end_date', 'is_closed'];
}
