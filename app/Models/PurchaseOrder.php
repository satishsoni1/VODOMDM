<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'po_number', 'vendor_id', 'rfq_id', 'demand_request_id', 'vendor_quotation_id',
        'created_by', 'approved_by', 'po_date', 'expected_delivery_date',
        'quantity', 'unit_price', 'total_amount', 'tax_amount', 'grand_total',
        'payment_terms', 'delivery_address', 'warranty_months', 'special_instructions',
        'status', 'approved_at',
    ];

    protected $casts = [
        'po_date' => 'date',
        'expected_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function demandRequest(): BelongsTo
    {
        return $this->belongsTo(DemandRequest::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function grns(): HasMany
    {
        return $this->hasMany(Grn::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(PoInvoice::class);
    }
}
