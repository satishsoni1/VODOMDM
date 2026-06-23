<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfq extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rfq_number', 'demand_request_id', 'created_by', 'device_specification',
        'quantity', 'response_deadline', 'terms', 'status',
    ];

    protected $casts = ['response_deadline' => 'date'];

    public function demandRequest(): BelongsTo
    {
        return $this->belongsTo(DemandRequest::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(RfqVendor::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(VendorQuotation::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
