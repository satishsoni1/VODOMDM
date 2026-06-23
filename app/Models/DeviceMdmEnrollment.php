<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceMdmEnrollment extends Model
{
    protected $fillable = [
        'device_id', 'mdm_profile_id', 'knox_enrollment_id', 'status',
        'enrolled_at', 'unenrolled_at', 'failure_reason',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'unenrolled_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(MdmProfile::class, 'mdm_profile_id');
    }
}
