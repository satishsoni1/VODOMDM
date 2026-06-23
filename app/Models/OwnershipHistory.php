<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnershipHistory extends Model
{
    protected $table = 'ownership_history';

    protected $fillable = [
        'device_id', 'employee_id', 'client_id', 'location_id', 'ownership_type',
        'from_date', 'to_date', 'transfer_reason', 'transferred_by',
    ];

    protected $casts = [
        'from_date' => 'datetime',
        'to_date' => 'datetime',
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

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
