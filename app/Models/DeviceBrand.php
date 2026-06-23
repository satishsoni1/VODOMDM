<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceBrand extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function models(): HasMany
    {
        return $this->hasMany(DeviceModel::class, 'brand_id');
    }
}
