<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepreciationRecord extends Model
{
    protected $fillable = [
        'device_id', 'purchase_value', 'current_value', 'depreciation_amount',
        'depreciation_rate', 'depreciation_method', 'as_of_date', 'calculated_by',
    ];

    protected $casts = [
        'as_of_date' => 'date',
        'purchase_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'depreciation_amount' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }
}
