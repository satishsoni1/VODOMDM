<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCenter extends Model
{
    protected $fillable = [
        'name', 'code', 'contact_person', 'phone', 'email', 'address', 'city', 'state', 'type', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function repairOrders(): HasMany
    {
        return $this->hasMany(RepairOrder::class);
    }
}
