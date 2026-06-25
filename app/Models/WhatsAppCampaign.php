<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppCampaign extends Model
{
    protected $table = 'whatsapp_campaigns';

    protected $fillable = [
        'name', 'template_id', 'custom_message', 'status',
        'total_contacts', 'sent', 'delivered', 'failed',
        'scheduled_at', 'started_at', 'completed_at', 'created_by',
    ];

    protected $casts = [
        'scheduled_at'  => 'datetime',
        'started_at'    => 'datetime',
        'completed_at'  => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'template_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(WhatsAppCampaignContact::class, 'campaign_id');
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'completed' => 'success',
            'running'   => 'primary',
            'scheduled' => 'info',
            'failed'    => 'danger',
            'cancelled' => 'secondary',
            default     => 'warning',
        };
    }

    public function progressPercent(): int
    {
        if (! $this->total_contacts) return 0;
        return (int) round(($this->sent + $this->failed) / $this->total_contacts * 100);
    }
}
