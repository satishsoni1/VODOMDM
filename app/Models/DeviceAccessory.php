<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceAccessory extends Model
{
    protected $fillable = ['device_id', 'name', 'quantity', 'status'];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
