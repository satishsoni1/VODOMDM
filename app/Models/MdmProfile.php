<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MdmProfile extends Model
{
    protected $fillable = [
        'client_id', 'profile_name', 'knox_profile_id', 'mdm_config_id', 'configuration', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(DeviceMdmEnrollment::class);
    }
}
