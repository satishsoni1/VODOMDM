<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DispatchBatch extends Model
{
    protected $fillable = [
        'dispatch_number', 'client_id', 'client_project_id', 'courier_partner_id',
        'from_location_id', 'dispatched_by', 'awb_number', 'tracking_number',
        'dispatch_date', 'expected_delivery_date', 'actual_delivery_date',
        'destination_address', 'destination_city', 'destination_state',
        'receiver_name', 'receiver_phone', 'freight_cost', 'status', 'remarks',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'freight_cost' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function courierPartner(): BelongsTo
    {
        return $this->belongsTo(CourierPartner::class);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DispatchItem::class);
    }
}
