<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MdmDeviceHardware extends Model
{
    protected $table = 'mdm_device_hardware';

    protected $fillable = [
        'mdm_device_id', 'recorded_at', 'battery',
        'total_ram', 'available_ram',
        'total_internal_storage', 'available_internal_storage',
        'brand', 'manufacturer', 'android_id', 'raw_json', 'pg_synced_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'raw_json'    => 'array',
        'pg_synced_at'=> 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(MdmDevice::class, 'mdm_device_id');
    }

    public function storageUsedPercent(): ?int
    {
        if (! $this->total_internal_storage) return null;
        $used = $this->total_internal_storage - ($this->available_internal_storage ?? 0);
        return (int) round(($used / $this->total_internal_storage) * 100);
    }

    public function ramUsedPercent(): ?int
    {
        if (! $this->total_ram) return null;
        $used = $this->total_ram - ($this->available_ram ?? 0);
        return (int) round(($used / $this->total_ram) * 100);
    }
}
