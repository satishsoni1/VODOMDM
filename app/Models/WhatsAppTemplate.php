<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppTemplate extends Model
{
    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'name', 'dovesoft_id', 'category', 'language', 'status',
        'header_type', 'header_text', 'body_text', 'footer_text',
        'buttons', 'variables', 'raw_payload', 'reject_reason', 'created_by',
    ];

    protected $casts = [
        'buttons'     => 'array',
        'variables'   => 'array',
        'raw_payload' => 'array',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(WhatsAppCampaign::class, 'template_id');
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending'  => 'warning',
            default    => 'secondary',
        };
    }

    public function variableCount(): int
    {
        return count($this->variables ?? []);
    }

    /** Build the Dovesoft API payload for sending this template */
    public function buildSendPayload(string $to, array $variableValues = []): array
    {
        $components = [];
        if ($this->variables) {
            $params = [];
            foreach ($this->variables as $i => $varName) {
                $params[] = ['type' => 'text', 'text' => $variableValues[$i] ?? $variableValues[$varName] ?? ''];
            }
            $components[] = ['type' => 'body', 'parameters' => $params];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'template',
            'template'          => [
                'name'     => $this->name,
                'language' => ['code' => $this->language],
            ],
        ];
        if ($components) $payload['template']['components'] = $components;
        return $payload;
    }
}
