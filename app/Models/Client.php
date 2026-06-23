<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'industry', 'contact_person', 'email', 'phone',
        'address', 'city', 'state', 'country', 'pincode', 'gstin', 'status', 'notes',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(ClientProject::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function demandRequests(): HasMany
    {
        return $this->hasMany(DemandRequest::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function recoveryCases(): HasMany
    {
        return $this->hasMany(RecoveryCase::class);
    }
}
