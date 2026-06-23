<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_code', 'name', 'email', 'phone', 'alternate_phone',
        'client_id', 'client_project_id', 'designation', 'department',
        'region', 'hq', 'abm', 'manager_name', 'manager_phone', 'manager_email',
        'location_id', 'joining_date', 'exit_date', 'status',
        'address', 'city', 'state', 'notes',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'exit_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(DeviceHandover::class);
    }

    public function currentDevices(): HasMany
    {
        return $this->hasMany(Device::class, 'current_employee_id');
    }

    public function recoveryCases(): HasMany
    {
        return $this->hasMany(RecoveryCase::class);
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    public function mdmDevice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MdmPortalDevice::class);
    }
}
