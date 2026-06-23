<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MdmPortalDevice extends Model
{
    protected $fillable = [
        'mdm_number', 'imei', 'serial_number', 'phone', 'description',
        'mdm_group', 'configuration', 'launcher_version', 'mdm_mode', 'kiosk_mode', 'default_launcher',
        'device_status', 'permission_status', 'installation_status', 'sync_time', 'last_update', 'mdm_status',
        'model', 'android_version', 'ip_address', 'public_ip',
        'latitude', 'longitude', 'location_raw', 'division',
        'info_json', 'enrollment_date',
        'device_id', 'employee_id', 'synced_at',
    ];

    protected $casts = [
        'mdm_mode'        => 'boolean',
        'kiosk_mode'      => 'boolean',
        'sync_time'       => 'datetime',
        'enrollment_date' => 'datetime',
        'synced_at'       => 'datetime',
        'latitude'        => 'decimal:7',
        'longitude'       => 'decimal:7',
        'info_json'       => 'array',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // ── Computed helpers ─────────────────────────────────────────────────────

    public function isOnline(): bool
    {
        return strtolower($this->device_status ?? '') === 'on';
    }

    public function isPermissionCompliant(): bool
    {
        return str_contains(strtolower($this->permission_status ?? ''), 'all permissions are granted');
    }

    public function syncAgeMinutes(): ?int
    {
        return $this->sync_time ? (int) now()->diffInMinutes($this->sync_time) : null;
    }

    public function syncAgeLabel(): string
    {
        $mins = $this->syncAgeMinutes();
        if ($mins === null) return 'Never';
        if ($mins < 60)   return $mins . ' min ago';
        if ($mins < 1440) return round($mins / 60) . 'h ago';
        return round($mins / 1440) . 'd ago';
    }

    public function syncFreshnessClass(): string
    {
        $mins = $this->syncAgeMinutes();
        if ($mins === null)  return 'danger';
        if ($mins <= 60)     return 'success';
        if ($mins <= 1440)   return 'warning';
        return 'danger';
    }

    /** Parse installation_status text into structured app array */
    public function parsedApps(): array
    {
        if (!$this->installation_status) return [];
        $apps = [];
        foreach (preg_split('/\r?\n/', $this->installation_status) as $line) {
            $line = trim(ltrim(trim($line), '-'));
            if (empty($line)) continue;
            // Format: "App Name: installed X.Y, available A.B" or "App: installed X.Y"
            if (preg_match('/^(.+?):\s*installed\s+([\d.]+)(?:,\s*available\s+([\d.]+))?/i', $line, $m)) {
                $installed  = $m[2];
                $available  = $m[3] ?? null;
                $outdated   = $available && version_compare($installed, $available, '<');
                $apps[] = [
                    'name'      => trim($m[1]),
                    'installed' => $installed,
                    'available' => $available,
                    'outdated'  => $outdated,
                ];
            }
        }
        return $apps;
    }

    public function hasOutdatedApps(): bool
    {
        foreach ($this->parsedApps() as $app) {
            if ($app['outdated']) return true;
        }
        return false;
    }

    // ── CSV import helper ────────────────────────────────────────────────────

    public static function fromCsvRow(array $row): array
    {
        return [
            'mdm_number'          => trim($row[0] ?? ''),
            'imei'                => trim($row[1] ?? '') ?: null,
            'serial_number'       => trim($row[2] ?? '') ?: null,
            'phone'               => trim($row[3] ?? '') ?: null,
            'description'         => trim($row[4] ?? '') ?: null,
            'mdm_group'           => trim($row[5] ?? '') ?: null,
            'configuration'       => trim($row[6] ?? '') ?: null,
            'launcher_version'    => trim($row[7] ?? '') ?: null,
            'device_status'       => strtolower(trim($row[8] ?? 'unknown')) ?: 'unknown',
            'permission_status'   => trim($row[9] ?? '') ?: null,
            'installation_status' => trim($row[10] ?? '') ?: null,
            'sync_time'           => self::parseDatetime($row[11] ?? null),
            'model'               => trim($row[12] ?? '') ?: null,
            'default_launcher'    => trim($row[13] ?? '') ?: null,
            'ip_address'          => trim($row[14] ?? '') ?: null,
            'mdm_mode'            => strtolower(trim($row[15] ?? '')) === 'true',
            'kiosk_mode'          => strtolower(trim($row[16] ?? '')) === 'true',
            'enrollment_date'     => self::parseDatetime($row[17] ?? null),
            'android_version'     => trim($row[18] ?? '') ?: null,
            'location_raw'        => trim($row[19] ?? '') ?: null,
            'division'            => trim($row[20] ?? '') ?: null,
            'mdm_status'          => trim($row[21] ?? '') ?: null,
            'synced_at'           => now(),
        ];
    }

    private static function parseDatetime(?string $val): ?string
    {
        if (!$val || trim($val) === '') return null;
        try {
            return \Carbon\Carbon::createFromFormat('d-m-Y H:i:s', trim($val))?->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }
}
