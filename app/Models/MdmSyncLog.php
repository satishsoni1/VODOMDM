<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MdmSyncLog extends Model
{
    protected $fillable = [
        'device_id', 'first_sync_at', 'last_sync_at', 'os_version', 'battery_level',
        'sim_operator', 'sim_number', 'ip_address', 'latitude', 'longitude',
        'location_name', 'is_rooted', 'sim_changed', 'installed_apps', 'raw_payload', 'synced_at',
    ];

    protected $casts = [
        'first_sync_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'synced_at' => 'datetime',
        'is_rooted' => 'boolean',
        'sim_changed' => 'boolean',
        'installed_apps' => 'array',
        'raw_payload' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
