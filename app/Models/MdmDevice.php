<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MdmDevice extends Model
{
    protected $table    = 'mdm_devices';
    public $incrementing = false;
    protected $keyType  = 'integer';

    protected $fillable = [
        'id', 'pg_number', 'imei', 'serial_number', 'phone', 'description',
        'mdm_group', 'configuration', 'launcher_version', 'mdm_mode', 'kiosk_mode',
        'default_launcher', 'device_status', 'permission_status', 'installation_status',
        'sync_time', 'mdm_status', 'model', 'android_version', 'ip_address', 'public_ip',
        'latitude', 'longitude', 'division', 'enrollment_date',
        'local_device_id', 'local_employee_id', 'pg_synced_at',
    ];

    protected $casts = [
        'mdm_mode'        => 'boolean',
        'kiosk_mode'      => 'boolean',
        'sync_time'       => 'datetime',
        'enrollment_date' => 'datetime',
        'pg_synced_at'    => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────
    public function hardware(): HasOne
    {
        return $this->hasOne(MdmDeviceHardware::class, 'mdm_device_id');
    }

    public function gps(): HasOne
    {
        return $this->hasOne(MdmDeviceGps::class, 'mdm_device_id');
    }

    public function params(): HasOne
    {
        return $this->hasOne(MdmDeviceParams::class, 'mdm_device_id');
    }

    public function locationLatest(): HasOne
    {
        return $this->hasOne(MdmLocationLatest::class, 'mdm_device_id');
    }

    public function locationHistory(): HasMany
    {
        return $this->hasMany(MdmLocationHistory::class, 'mdm_device_id');
    }

    public function localDevice(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'local_device_id');
    }

    public function localEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'local_employee_id');
    }

    /**
     * Resolve the asset-tracking Device for this MDM record, falling back to a
     * live IMEI/serial lookup when local_device_id hasn't been backfilled yet.
     */
    public function matchedDevice(): ?Device
    {
        if ($this->local_device_id) {
            return $this->localDevice;
        }

        $device = null;
        if ($this->imei) {
            $device = Device::where('imei1', $this->imei)->orWhere('imei2', $this->imei)->first();
        }
        if (! $device && $this->serial_number) {
            $device = Device::where('serial_number', $this->serial_number)->first();
        }

        return $device;
    }

    /**
     * Resolve the employee for this MDM record via local_employee_id, falling back
     * to the employee currently assigned to the IMEI/serial-matched device.
     */
    public function resolvedEmployee(): ?Employee
    {
        if ($this->local_employee_id) {
            return $this->localEmployee;
        }

        return $this->matchedDevice()?->currentEmployee;
    }

    // Backward-compatible aliases so existing views work without changes
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'local_employee_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'local_device_id');
    }

    // ── Attribute accessors (backward compat) ────────────────────────────────
    public function getMdmNumberAttribute(): ?string
    {
        return $this->pg_number;
    }

    public function getEmployeeIdAttribute(): ?int
    {
        return $this->local_employee_id;
    }

    public function getDeviceIdAttribute(): ?int
    {
        return $this->local_device_id;
    }

    public function getSyncedAtAttribute(): ?Carbon
    {
        return $this->pg_synced_at;
    }

    // ── Display helpers ──────────────────────────────────────────────────────
    public function isOnline(): bool
    {
        return $this->device_status === 'on';
    }

    public function syncAgeLabel(): string
    {
        if (! $this->sync_time) return 'Never';
        $diff = now()->diffInMinutes($this->sync_time);
        if ($diff < 5)   return 'Just now';
        if ($diff < 60)  return "{$diff}m ago";
        $hours = (int) ($diff / 60);
        if ($hours < 24) return "{$hours}h ago";
        return (int) ($hours / 24) . 'd ago';
    }

    public function syncFreshnessClass(): string
    {
        if (! $this->sync_time) return 'muted';
        $mins = now()->diffInMinutes($this->sync_time);
        if ($mins < 15)  return 'success';
        if ($mins < 120) return 'warning';
        return 'danger';
    }

    public function batteryLevel(): ?int
    {
        return $this->hardware?->battery;
    }

    public function isPermissionCompliant(): bool
    {
        if (! $this->permission_status) return true;
        $s = strtolower($this->permission_status);
        return ! str_contains($s, 'denied')
            && ! str_contains($s, 'missing')
            && ! str_contains($s, 'failed');
    }

    public function parsedApps(): array
    {
        if (! $this->installation_status) return [];
        $apps = [];
        foreach (explode("\n", $this->installation_status) as $line) {
            $line = trim($line);
            if (! str_starts_with($line, '- ')) continue;
            $parts   = explode(': installed ', ltrim($line, '- '), 2);
            $apps[]  = [
                'name'      => trim($parts[0] ?? ''),
                'installed' => trim($parts[1] ?? ''),
                'version'   => trim($parts[1] ?? ''),
                'available' => null,
                'outdated'  => false,
            ];
        }
        return $apps;
    }
}
