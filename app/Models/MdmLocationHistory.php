<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MdmLocationHistory extends Model
{
    protected $table = 'mdm_location_history';

    protected $fillable = [
        'mdm_device_id', 'pg_id', 'recorded_at',
        'latitude', 'longitude', 'speed', 'accuracy',
        'raw_json', 'pg_synced_at',
    ];

    protected $casts = [
        'recorded_at'  => 'datetime',
        'raw_json'     => 'array',
        'pg_synced_at' => 'datetime',
        'latitude'     => 'float',
        'longitude'    => 'float',
        'speed'        => 'float',
        'accuracy'     => 'float',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(MdmDevice::class, 'mdm_device_id');
    }
}
