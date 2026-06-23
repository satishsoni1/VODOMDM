<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceModel extends Model
{
    protected $fillable = [
        'brand_id', 'category_id', 'model_name', 'model_number', 'os', 'os_version',
        'ram', 'storage', 'screen_size', 'battery', 'specifications', 'standard_cost', 'is_active',
    ];

    protected $casts = [
        'standard_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(DeviceBrand::class, 'brand_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DeviceCategory::class, 'category_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
