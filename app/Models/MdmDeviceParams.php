<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MdmDeviceParams extends Model
{
    protected $table = 'mdm_device_params';

    protected $fillable = [
        'mdm_device_id', 'recorded_at', 'raw_json', 'pg_synced_at',
    ];

    protected $casts = [
        'recorded_at'  => 'datetime',
        'raw_json'     => 'array',
        'pg_synced_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(MdmDevice::class, 'mdm_device_id');
    }
}
