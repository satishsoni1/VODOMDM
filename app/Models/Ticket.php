<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number', 'device_id', 'employee_id', 'client_id', 'ticket_category_id',
        'raised_by', 'assigned_to', 'subject', 'description', 'priority', 'status',
        'sla_due_at', 'first_response_at', 'resolved_at', 'closed_at',
        'resolution_notes', 'resolution_hours',
    ];

    protected $casts = [
        'sla_due_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function repairOrders(): HasMany
    {
        return $this->hasMany(RepairOrder::class);
    }

    public function isSlaBreached(): bool
    {
        return $this->sla_due_at && now()->gt($this->sla_due_at) && !in_array($this->status, ['resolved', 'closed']);
    }
}
