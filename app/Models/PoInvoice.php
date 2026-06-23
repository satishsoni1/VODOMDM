<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoInvoice extends Model
{
    protected $fillable = [
        'purchase_order_id', 'invoice_number', 'invoice_date', 'invoice_amount',
        'due_date', 'payment_status', 'paid_amount', 'payment_date', 'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'invoice_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
