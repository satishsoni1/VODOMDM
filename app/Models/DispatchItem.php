<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchItem extends Model
{
    protected $fillable = ['dispatch_batch_id', 'device_id', 'status', 'remarks'];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(DispatchBatch::class, 'dispatch_batch_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
