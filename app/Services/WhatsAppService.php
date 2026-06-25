<?php

namespace App\Services;

use App\Models\ApiLog;
use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignContact;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $apiUrl;
    private string $apiKey;
    private string $sender;
    private string $driver;

    public function __construct()
    {
        $this->apiUrl  = rtrim(config('whatsapp.api_url', ''), '/');
        $this->apiKey  = config('whatsapp.api_key', '');
        $this->sender  = config('whatsapp.sender', '');
        $this->driver  = config('whatsapp.driver', 'dovesoft');
    }

    // ── Core send ─────────────────────────────────────────────────────────────

    /** Send a WhatsAppMessage record (text) */
    public function send(WhatsAppMessage $msg): bool
    {
        $log = ApiLog::create([
            'type'         => 'whatsapp',
            'action'       => 'send',
            'status'       => 'running',
            'summary'      => "WA to {$msg->to_phone}",
            'triggered_by' => $msg->created_by,
            'request_data' => ['to' => $msg->to_phone],
        ]);

        if ($this->driver === 'log') {
            Log::info('[WhatsApp-LOG]', ['to' => $msg->to_phone, 'message' => $msg->message_text]);
            $msg->update(['status' => 'sent', 'sent_at' => now(), 'api_response' => ['driver' => 'log']]);
            $log->finish('success', 'Log driver — not actually sent');
            return true;
        }

        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to'                => $this->normalizePhone($msg->to_phone),
                'type'              => 'text',
                'recipient_type'    => 'individual',
                'text'              => ['body' => $msg->message_text],
            ];

            $response     = $this->post($this->apiUrl, $payload);
            $responseData = $response->json() ?? ['raw' => $response->body()];
            $success      = $response->successful();

            $msg->update([
                'status'        => $success ? 'sent' : 'failed',
                'sent_at'       => $success ? now() : null,
                'api_response'  => $responseData,
                'error_message' => $success ? null : ($responseData['msg'] ?? $response->body()),
            ]);

            $log->finish($success ? 'success' : 'failed',
                $success ? "Sent to {$msg->to_phone}" : ($responseData['msg'] ?? 'failed'),
                ['response_data' => $responseData, 'records_out' => (int) $success]);

            return $success;

        } catch (\Throwable $e) {
            $msg->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            $log->finish('failed', null, ['error_message' => $e->getMessage()]);
            Log::error('WhatsApp send failed', ['error' => $e->getMessage(), 'msg_id' => $msg->id]);
            return false;
        }
    }

    /** Send a template message directly (used by campaign + manual) */
    public function sendTemplate(string $phone, WhatsAppTemplate $template, array $variableValues = [], ?int $createdBy = null, ?int $campaignId = null): bool
    {
        $phone = $this->normalizePhone($phone);

        if ($this->driver === 'log') {
            Log::info('[WhatsApp-LOG Template]', ['to' => $phone, 'template' => $template->name, 'vars' => $variableValues]);
            $this->recordMessage($phone, '', $template, $variableValues, 'sent', $createdBy, $campaignId);
            return true;
        }

        try {
            $payload  = $template->buildSendPayload($phone, $variableValues);
            $response = $this->post($this->apiUrl, $payload);
            $data     = $response->json() ?? ['raw' => $response->body()];
            $success  = $response->successful();

            $this->recordMessage($phone, '', $template, $variableValues,
                $success ? 'sent' : 'failed', $createdBy, $campaignId,
                $data, $success ? null : ($data['msg'] ?? $response->body()));

            return $success;

        } catch (\Throwable $e) {
            $this->recordMessage($phone, '', $template, $variableValues, 'failed', $createdBy, $campaignId, null, $e->getMessage());
            return false;
        }
    }

    // ── Campaign ──────────────────────────────────────────────────────────────

    /** Launch a campaign — sends to all pending contacts */
    public function launchCampaign(WhatsAppCampaign $campaign): void
    {
        $campaign->update(['status' => 'running', 'started_at' => now()]);

        $log = ApiLog::create([
            'type'         => 'whatsapp',
            'action'       => 'campaign_launch',
            'status'       => 'running',
            'summary'      => "Campaign: {$campaign->name}",
            'triggered_by' => $campaign->created_by,
            'records_in'   => $campaign->total_contacts,
        ]);

        $sent = $failed = 0;

        $campaign->contacts()->where('status', 'pending')->chunk(50, function ($contacts) use ($campaign, &$sent, &$failed) {
            foreach ($contacts as $contact) {
                $ok = false;

                if ($campaign->template_id && $campaign->template) {
                    $ok = $this->sendTemplate(
                        $contact->phone,
                        $campaign->template,
                        $contact->variables ?? [],
                        $campaign->created_by,
                        $campaign->id
                    );
                } else {
                    $msg = WhatsAppMessage::create([
                        'to_phone'    => $contact->phone,
                        'to_name'     => $contact->name,
                        'message_text'=> $campaign->custom_message,
                        'status'      => 'pending',
                        'campaign_id' => $campaign->id,
                        'created_by'  => $campaign->created_by,
                    ]);
                    $ok = $this->send($msg);
                }

                $contact->update([
                    'status'        => $ok ? 'sent' : 'failed',
                    'sent_at'       => $ok ? now() : null,
                    'error_message' => $ok ? null : 'send failed',
                ]);

                $ok ? $sent++ : $failed++;
            }

            $campaign->increment('sent', $sent);
            $campaign->increment('failed', $failed);
            $sent = $failed = 0;
        });

        $campaign->refresh();
        $allDone = ($campaign->sent + $campaign->failed) >= $campaign->total_contacts;
        $campaign->update([
            'status'       => $allDone ? 'completed' : 'failed',
            'completed_at' => $allDone ? now() : null,
        ]);

        $log->finish('success', "Sent:{$campaign->sent} Failed:{$campaign->failed}", [
            'records_out' => $campaign->sent,
        ]);
    }

    // ── Webhook (incoming messages from Dovesoft) ─────────────────────────────

    /** Process a Dovesoft webhook payload and store incoming message */
    public function handleWebhook(array $payload): void
    {
        // Dovesoft follows Meta Cloud API webhook structure
        $entries = $payload['entry'] ?? [];
        foreach ($entries as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];
                foreach ($value['messages'] ?? [] as $message) {
                    $from    = $message['from'] ?? '';
                    $type    = $message['type'] ?? 'text';
                    $body    = match($type) {
                        'text'     => $message['text']['body'] ?? '',
                        'image'    => '[Image]',
                        'video'    => '[Video]',
                        'document' => '[Document: ' . ($message['document']['filename'] ?? '') . ']',
                        'audio'    => '[Audio]',
                        default    => '[' . strtoupper($type) . ']',
                    };
                    $contact = collect($value['contacts'] ?? [])->firstWhere('wa_id', $from);

                    WhatsAppMessage::create([
                        'to_phone'             => $from,
                        'to_name'              => $contact['profile']['name'] ?? null,
                        'message_text'         => $body,
                        'status'               => 'sent',
                        'direction'            => 'inbound',
                        'reply_to_message_id'  => $message['context']['id'] ?? null,
                        'api_response'         => $message,
                        'sent_at'              => now(),
                    ]);
                }
            }
        }
    }

    // ── Template management ───────────────────────────────────────────────────

    /** Create a template via Dovesoft API */
    public function createTemplate(WhatsAppTemplate $template): array
    {
        if ($this->driver === 'log') {
            $template->update(['status' => 'pending', 'dovesoft_id' => 'LOG-' . $template->id]);
            return ['success' => true, 'message' => 'Log driver — template not sent to Dovesoft'];
        }

        $components = [];

        if ($template->header_type && $template->header_type !== 'NONE') {
            $components[] = [
                'type'   => 'HEADER',
                'format' => $template->header_type,
                'text'   => $template->header_text,
            ];
        }

        $bodyParams = [];
        foreach ($template->variables ?? [] as $varName) {
            $bodyParams[] = ['type' => 'text', 'text' => '{{' . $varName . '}}'];
        }
        $components[] = [
            'type' => 'BODY',
            'text' => $template->body_text,
            ...(count($bodyParams) ? ['example' => ['body_text' => [array_column($bodyParams, 'text')]]] : []),
        ];

        if ($template->footer_text) {
            $components[] = ['type' => 'FOOTER', 'text' => $template->footer_text];
        }

        foreach ($template->buttons ?? [] as $btn) {
            $components[] = ['type' => 'BUTTONS', 'buttons' => [$btn]];
        }

        $payload = [
            'name'       => $template->name,
            'language'   => $template->language,
            'category'   => $template->category,
            'components' => $components,
        ];

        try {
            $url      = str_replace('/directApi/message', '/template/create', $this->apiUrl);
            $response = $this->post($url, $payload);
            $data     = $response->json() ?? [];

            if ($response->successful()) {
                $template->update([
                    'status'      => 'pending',
                    'dovesoft_id' => $data['data']['id'] ?? $data['id'] ?? null,
                    'raw_payload' => $data,
                ]);
                return ['success' => true, 'data' => $data];
            }

            return ['success' => false, 'message' => $data['msg'] ?? $response->body()];

        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /** Fetch all templates from Dovesoft and sync locally */
    public function syncTemplates(): array
    {
        if ($this->driver === 'log') {
            return ['synced' => 0, 'message' => 'Log driver — no sync'];
        }

        try {
            $url      = str_replace('/directApi/message', '/template/list', $this->apiUrl);
            $response = Http::timeout(config('whatsapp.timeout', 15))
                ->withHeaders(['key' => $this->apiKey, 'wabaNumber' => $this->sender])
                ->get($url);
            $data     = $response->json() ?? [];
            $templates = $data['data'] ?? $data['templates'] ?? $data ?? [];

            $synced = 0;
            foreach ($templates as $t) {
                $name       = $t['name'] ?? null;
                $dovesoftId = (string) ($t['id'] ?? '');
                if (! $name) continue;

                $bodyComponent = collect($t['components'] ?? [])->firstWhere('type', 'BODY');
                $headComponent = collect($t['components'] ?? [])->firstWhere('type', 'HEADER');

                WhatsAppTemplate::updateOrCreate(
                    ['name' => $name],
                    [
                        'dovesoft_id'  => $dovesoftId,
                        'category'     => $t['category'] ?? 'MARKETING',
                        'language'     => $t['language'] ?? 'en',
                        'status'       => strtolower($t['status'] ?? 'pending'),
                        'header_type'  => $headComponent['format'] ?? null,
                        'header_text'  => $headComponent['text'] ?? null,
                        'body_text'    => $bodyComponent['text'] ?? '',
                        'raw_payload'  => $t,
                    ]
                );
                $synced++;
            }

            return ['success' => true, 'synced' => $synced];

        } catch (\Throwable $e) {
            return ['success' => false, 'synced' => 0, 'message' => $e->getMessage()];
        }
    }

    // ── Scheduled queue ───────────────────────────────────────────────────────

    public function processDue(): int
    {
        $due = WhatsAppMessage::where('status', 'pending')
            ->where('direction', 'outbound')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now());
            })
            ->get();

        $sent = 0;
        foreach ($due as $msg) {
            if ($this->send($msg)) $sent++;
        }
        return $sent;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Register a dispatched event message for triggering later */
    public static function dispatchForEvent(
        string $event, string $entityType, int $entityId,
        string $phone, string $name, string $message, ?int $createdBy = null
    ): WhatsAppMessage {
        return WhatsAppMessage::create([
            'to_phone'            => $phone,
            'to_name'             => $name,
            'message_text'        => $message,
            'trigger_event'       => $event,
            'trigger_entity_type' => $entityType,
            'trigger_entity_id'   => $entityId,
            'status'              => 'pending',
            'created_by'          => $createdBy,
        ]);
    }

    private function post(string $url, array $payload)
    {
        return Http::timeout(config('whatsapp.timeout', 15))
            ->withHeaders(['key' => $this->apiKey, 'wabaNumber' => $this->sender])
            ->asJson()
            ->post($url, $payload);
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 10) $digits = '91' . $digits;
        return $digits;
    }

    private function recordMessage(
        string $phone, string $body, WhatsAppTemplate $template,
        array $vars, string $status, ?int $createdBy, ?int $campaignId,
        ?array $apiResponse = null, ?string $error = null
    ): void {
        WhatsAppMessage::create([
            'to_phone'      => $phone,
            'message_text'  => $body ?: $template->body_text,
            'template_name' => $template->name,
            'template_id'   => $template->id,
            'campaign_id'   => $campaignId,
            'variables'     => $vars,
            'status'        => $status,
            'sent_at'       => $status === 'sent' ? now() : null,
            'api_response'  => $apiResponse,
            'error_message' => $error,
            'created_by'    => $createdBy,
        ]);
    }
}
