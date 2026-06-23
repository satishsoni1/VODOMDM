<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grn extends Model
{
    protected $table = 'grns';

    protected $fillable = [
        'grn_number', 'purchase_order_id', 'vendor_id', 'received_by', 'location_id',
        'received_date', 'quantity_ordered', 'quantity_received', 'quantity_accepted',
        'quantity_rejected', 'delivery_challan_number', 'invoice_number', 'remarks', 'status',
    ];

    protected $casts = ['received_date' => 'date'];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
