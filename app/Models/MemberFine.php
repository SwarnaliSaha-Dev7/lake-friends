<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberFine extends Model
{
    protected $fillable = [
        'club_id',
        'member_id',
        'financial_year_id',
        'fine_type',
        'fine_amount',
        'reference_days',
        'reference_amount',
        'fine_date',
        'status',
        'notes',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class, 'financial_year_id');
    }
}
