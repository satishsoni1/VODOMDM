<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Device extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'qr_token', 'asset_tag', 'serial_number', 'imei1', 'imei2',
        'device_model_id', 'grn_id', 'purchase_order_id', 'vendor_id', 'client_id',
        'box_number', 'color', 'purchase_date', 'purchase_price',
        'warranty_months', 'warranty_expiry', 'accessories',
        'lifecycle_status', 'condition', 'current_location_id', 'current_employee_id',
        'current_group', 'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (Device $device) {
            $device->qr_token ??= (string) Str::uuid();
        });
    }

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'purchase_price' => 'decimal:2',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(DeviceModel::class, 'device_model_id');
    }

    public function grn(): BelongsTo
    {
        return $this->belongsTo(Grn::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }

    public function currentEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'current_employee_id');
    }

    public function accessories(): HasMany
    {
        return $this->hasMany(DeviceAccessory::class);
    }

    public function mdmPortalDevice(): HasOne
    {
        return $this->hasOne(MdmPortalDevice::class);
    }

    public function mdmDevice(): HasOne
    {
        return $this->hasOne(MdmDevice::class, 'local_device_id');
    }

    public function isMdmInstalled(): bool
    {
        return $this->mdmDevice !== null;
    }

    public function mdmEnrollment(): HasOne
    {
        return $this->hasOne(DeviceMdmEnrollment::class)->latestOfMany();
    }

    public function mdmSyncLogs(): HasMany
    {
        return $this->hasMany(MdmSyncLog::class);
    }

    public function latestMdmSync(): HasOne
    {
        return $this->hasOne(MdmSyncLog::class)->latestOfMany('synced_at');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(DeviceAllocation::class);
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(DeviceHandover::class);
    }

    public function latestHandover(): HasOne
    {
        return $this->hasOne(DeviceHandover::class)->latestOfMany('handover_date');
    }

    public function ownershipHistory(): HasMany
    {
        return $this->hasMany(OwnershipHistory::class)->orderBy('from_date', 'desc');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function recoveryCases(): HasMany
    {
        return $this->hasMany(RecoveryCase::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(DeviceReturn::class);
    }

    public function insurancePolicies(): HasMany
    {
        return $this->hasMany(DeviceInsurance::class);
    }

    public function insuranceClaims(): HasMany
    {
        return $this->hasMany(InsuranceClaim::class);
    }

    public function repairOrders(): HasMany
    {
        return $this->hasMany(RepairOrder::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(DeviceEvent::class)->orderBy('event_at', 'desc');
    }

    public function depreciation(): HasMany
    {
        return $this->hasMany(DepreciationRecord::class);
    }

    public function disposal(): HasOne
    {
        return $this->hasOne(DisposalRecord::class);
    }

    public function linkRequests(): HasMany
    {
        return $this->hasMany(DeviceLinkRequest::class);
    }
}
