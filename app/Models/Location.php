<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name', 'code', 'type', 'address', 'city', 'state',
        'country', 'pincode', 'contact_person', 'contact_phone', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'current_location_id');
    }

    public function grns(): HasMany
    {
        return $this->hasMany(Grn::class);
    }
}
