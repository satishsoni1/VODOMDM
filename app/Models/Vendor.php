<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'contact_person', 'email', 'phone', 'alternate_phone',
        'address', 'city', 'state', 'country', 'pincode', 'gstin', 'pan',
        'payment_terms', 'credit_limit', 'performance_score', 'status', 'notes',
    ];

    protected $casts = ['credit_limit' => 'decimal:2'];

    public function contacts(): HasMany
    {
        return $this->hasMany(VendorContact::class);
    }

    public function rfqs(): HasMany
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

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
