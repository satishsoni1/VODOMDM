<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceReturn extends Model
{
    protected $fillable = [
        'return_number', 'device_id', 'employee_id', 'recovery_case_id', 'received_by',
        'return_date', 'device_condition', 'accessories_returned', 'accessories_list',
        'inspection_notes', 'data_wiped', 'data_wiped_at', 'status', 'next_action', 'remarks',
    ];

    protected $casts = [
        'return_date' => 'date',
        'data_wiped_at' => 'datetime',
        'accessories_returned' => 'boolean',
        'data_wiped' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function recoveryCase(): BelongsTo
    {
        return $this->belongsTo(RecoveryCase::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
