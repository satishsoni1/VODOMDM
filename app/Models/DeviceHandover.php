<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceHandover extends Model
{
    protected $fillable = [
        'handover_number', 'device_id', 'employee_id', 'client_id', 'client_project_id',
        'handed_over_by', 'dispatch_batch_id', 'handover_date', 'handover_location',
        'handover_city', 'acknowledgement_received', 'acknowledged_at', 'acknowledgement_file',
        'condition_at_handover', 'accessories_handed', 'status', 'remarks',
    ];

    protected $casts = [
        'handover_date' => 'date',
        'acknowledged_at' => 'datetime',
        'acknowledgement_received' => 'boolean',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function handedOverBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_over_by');
    }

    public function dispatchBatch(): BelongsTo
    {
        return $this->belongsTo(DispatchBatch::class);
    }
}
