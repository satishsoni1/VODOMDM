<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppCampaignContact extends Model
{
    protected $table = 'whatsapp_campaign_contacts';

    public $timestamps = false;

    protected $fillable = [
        'campaign_id', 'phone', 'name', 'variables', 'status', 'error_message', 'sent_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'sent_at'   => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WhatsAppCampaign::class, 'campaign_id');
    }
}
