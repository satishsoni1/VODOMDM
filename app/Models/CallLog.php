<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallLog extends Model
{
    protected $fillable = [
        'recovery_case_id', 'device_id', 'employee_id', 'called_by', 'phone_number',
        'call_datetime', 'duration_seconds', 'outcome', 'promise_date',
        'next_follow_up_date', 'remarks',
    ];

    protected $casts = [
        'call_datetime' => 'datetime',
        'promise_date' => 'date',
        'next_follow_up_date' => 'date',
    ];

    public function recoveryCase(): BelongsTo
    {
        return $this->belongsTo(RecoveryCase::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function calledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'called_by');
    }
}
