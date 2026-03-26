<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class MemberFinancialSummary extends Model
{
    use LogsModelChanges;

    protected $fillable = [
        'club_id',
        'member_id',
        'financial_year_id',
        'minimum_spend_required',
        'total_recharge',
        'total_spend',
        'shortfall_amount',
        'forfeited_amount',
        'carry_forward_amount',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class, 'financial_year_id');
    }

    // Shortfall = minimum_spend_required - total_spend (if positive)
    public function getShortfallAttribute(): float
    {
        return max(0, (float)$this->minimum_spend_required - (float)$this->total_spend);
    }
}
