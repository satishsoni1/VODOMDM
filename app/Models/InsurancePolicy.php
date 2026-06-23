<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsurancePolicy extends Model
{
    protected $fillable = [
        'policy_number', 'insurance_provider_id', 'client_id', 'coverage_type',
        'coverage_details', 'premium_amount', 'sum_insured', 'start_date', 'expiry_date', 'status', 'terms',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
        'premium_amount' => 'decimal:2',
        'sum_insured' => 'decimal:2',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(InsuranceProvider::class, 'insurance_provider_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function deviceInsurances(): HasMany
    {
        return $this->hasMany(DeviceInsurance::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(InsuranceClaim::class);
    }

    public function isExpiringSoon(): bool
    {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= 30 && $this->expiry_date->gt(now());
    }
}
