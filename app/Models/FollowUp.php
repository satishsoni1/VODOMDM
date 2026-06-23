<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends Model
{
    protected $fillable = [
        'created_by', 'assigned_to', 'entity_type', 'entity_id',
        'scheduled_date', 'scheduled_time', 'subject', 'notes',
        'status', 'completed_date', 'outcome',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
