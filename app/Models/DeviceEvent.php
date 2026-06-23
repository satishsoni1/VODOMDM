<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceEvent extends Model
{
    protected $fillable = [
        'device_id', 'user_id', 'event_type', 'from_status', 'to_status',
        'entity_type', 'entity_id', 'description', 'metadata', 'ip_address', 'event_at',
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
