<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemandRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'request_number', 'client_id', 'client_project_id', 'requested_by', 'device_model_id',
        'device_specification', 'quantity', 'required_date', 'budget_amount',
        'division', 'region', 'justification', 'status',
        'approved_by', 'approved_at', 'rejection_reason',
    ];

    protected $casts = [
        'required_date' => 'date',
        'approved_at' => 'datetime',
        'budget_amount' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function deviceModel(): BelongsTo
    {
        return $this->belongsTo(DeviceModel::class);
    }

    public function rfqs(): HasMany
    {
        return $this->hasMany(Rfq::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
