<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairOrder extends Model
{
    protected $fillable = [
        'rma_number', 'device_id', 'service_center_id', 'ticket_id', 'insurance_claim_id', 'created_by',
        'fault_description', 'detailed_notes', 'sent_date', 'estimated_return_date', 'actual_return_date',
        'estimated_cost', 'actual_cost', 'under_warranty', 'repair_type', 'repair_notes',
        'status', 'outcome', 'replacement_device_id',
    ];

    protected $casts = [
        'sent_date' => 'date',
        'estimated_return_date' => 'date',
        'actual_return_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'under_warranty' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function serviceCenter(): BelongsTo
    {
        return $this->belongsTo(ServiceCenter::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function insuranceClaim(): BelongsTo
    {
        return $this->belongsTo(InsuranceClaim::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function replacementDevice(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'replacement_device_id');
    }
}
