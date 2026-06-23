<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecoveryCase extends Model
{
    protected $fillable = [
        'case_number', 'device_id', 'employee_id', 'client_id', 'assigned_to', 'created_by',
        'trigger_reason', 'exit_date', 'recovery_due_date', 'pickup_scheduled_date',
        'recovered_date', 'pickup_address', 'status', 'follow_up_count',
        'last_follow_up_at', 'next_follow_up_date', 'remarks',
    ];

    protected $casts = [
        'exit_date' => 'date',
        'recovery_due_date' => 'date',
        'pickup_scheduled_date' => 'date',
        'recovered_date' => 'date',
        'next_follow_up_date' => 'date',
        'last_follow_up_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'entity_id')->where('entity_type', 'recovery_case');
    }

    public function deviceReturn(): HasMany
    {
        return $this->hasMany(DeviceReturn::class);
    }
}
