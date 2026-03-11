<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActionApproval extends Model
{
    protected $fillable = [
        'club_id',
        'module',
        'action_type',
        'entity_model',
        'membership_type_id',
        'entity_id',
        'maker_user_id',
        'checker_user_id',
        'status',
        'request_payload',
        'approved_or_rejected_at',
        'rejection_reason'
    ];

    protected $casts = [
        'request_payload' => 'array',
    ];

    public function operatorDetails(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_user_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_user_id');
    }

    public function entity()
    {
        return $this->morphTo(null, 'entity_model', 'entity_id');
    }

    public function membershipType(): BelongsTo
    {
        return $this->belongsTo(MembershipType::class);
    }
}
