<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisposalRecord extends Model
{
    protected $fillable = [
        'device_id', 'approved_by', 'reason', 'disposal_method',
        'residual_value', 'write_off_value', 'disposal_date',
        'disposal_certificate', 'notes', 'status',
    ];

    protected $casts = [
        'disposal_date' => 'date',
        'residual_value' => 'decimal:2',
        'write_off_value' => 'decimal:2',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
