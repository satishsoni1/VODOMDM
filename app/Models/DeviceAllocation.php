<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceAllocation extends Model
{
    protected $fillable = [
        'device_id', 'client_id', 'client_project_id', 'allocated_by',
        'region', 'allocation_date', 'status', 'remarks',
    ];

    protected $casts = ['allocation_date' => 'date'];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function allocatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }
}
