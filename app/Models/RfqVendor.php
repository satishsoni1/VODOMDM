<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqVendor extends Model
{
    protected $fillable = ['rfq_id', 'vendor_id', 'sent_at', 'status'];

    protected $casts = ['sent_at' => 'datetime'];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
