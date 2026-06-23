<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceClaim extends Model
{
    protected $fillable = [
        'claim_number', 'device_id', 'insurance_policy_id', 'raised_by',
        'incident_date', 'incident_type', 'incident_description',
        'claimed_amount', 'approved_amount', 'settled_amount',
        'claim_date', 'settlement_date', 'supporting_documents', 'status',
        'rejection_reason', 'remarks',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'claim_date' => 'date',
        'settlement_date' => 'date',
        'claimed_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'settled_amount' => 'decimal:2',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class, 'insurance_policy_id');
    }

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }
}
