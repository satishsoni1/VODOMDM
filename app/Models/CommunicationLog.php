<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommunicationLog extends Model
{
    protected $fillable = [
        'created_by', 'channel', 'direction', 'stakeholder_type', 'stakeholder_id',
        'device_id', 'ticket_id', 'recovery_case_id', 'communication_datetime',
        'subject', 'outcome', 'remarks', 'follow_up_date',
    ];

    protected $casts = [
        'communication_datetime' => 'datetime',
        'follow_up_date' => 'date',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function recoveryCase(): BelongsTo
    {
        return $this->belongsTo(RecoveryCase::class);
    }
}
