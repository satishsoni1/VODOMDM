<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorQuotation extends Model
{
    protected $fillable = [
        'rfq_id', 'vendor_id', 'quotation_number', 'quotation_date', 'valid_until',
        'quantity', 'unit_price', 'total_amount', 'delivery_days', 'warranty_months',
        'terms', 'negotiation_notes', 'negotiated_price', 'is_selected',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'negotiated_price' => 'decimal:2',
        'is_selected' => 'boolean',
    ];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
