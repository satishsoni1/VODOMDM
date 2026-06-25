<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppMessage extends Model
{
    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'to_phone', 'to_name', 'message_text', 'template_name', 'template_id',
        'variables', 'trigger_event', 'trigger_entity_type', 'trigger_entity_id',
        'campaign_id', 'direction', 'reply_to_message_id',
        'scheduled_at', 'sent_at', 'status', 'api_response', 'error_message', 'created_by',
    ];

    protected $casts = [
        'variables'    => 'array',
        'api_response' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WhatsAppCampaign::class, 'campaign_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'template_id');
    }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isSent(): bool      { return $this->status === 'sent'; }
    public function isFailed(): bool    { return $this->status === 'failed'; }
    public function isScheduled(): bool { return $this->scheduled_at && $this->scheduled_at->isFuture(); }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'sent'      => 'success',
            'failed'    => 'danger',
            'cancelled' => 'secondary',
            default     => 'warning',
        };
    }

    public static function triggerEventLabel(string $event): string
    {
        return match($event) {
            'manual'              => 'Manual',
            'device_offline'      => 'Device Offline',
            'handover_created'    => 'Device Handover',
            'ticket_opened'       => 'Ticket Opened',
            'ticket_resolved'     => 'Ticket Resolved',
            'recovery_initiated'  => 'Recovery Initiated',
            'enrollment'          => 'MDM Enrollment',
            'mdm_sync'            => 'MDM Sync',
            default               => ucfirst(str_replace('_', ' ', $event)),
        };
    }

    public static function allTriggerEvents(): array
    {
        return [
            'manual'             => 'Manual',
            'device_offline'     => 'Device Offline',
            'handover_created'   => 'Device Handover',
            'ticket_opened'      => 'Ticket Opened',
            'ticket_resolved'    => 'Ticket Resolved',
            'recovery_initiated' => 'Recovery Initiated',
            'enrollment'         => 'MDM Enrollment',
            'mdm_sync'           => 'MDM Sync',
        ];
    }
}
