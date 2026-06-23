<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'entity_type', 'entity_id', 'entity_label',
        'old_values', 'new_values', 'ip_address', 'user_agent', 'performed_at',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
