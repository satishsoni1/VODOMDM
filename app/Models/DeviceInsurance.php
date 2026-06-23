<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceInsurance extends Model
{
    protected $table = 'device_insurance';

    protected $fillable = [
        'device_id', 'insurance_policy_id', 'insured_value', 'effective_date', 'expiry_date', 'status',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'insured_value' => 'decimal:2',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class, 'insurance_policy_id');
    }
}
