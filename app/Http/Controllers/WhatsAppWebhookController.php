<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /** Dovesoft webhook receiver — configure this URL in Dovesoft portal */
    public function receive(Request $request)
    {
        $payload = $request->all();
        Log::info('[WA-Webhook]', $payload);

        try {
            $svc = new WhatsAppService();
            $svc->handleWebhook($payload);
        } catch (\Throwable $e) {
            Log::error('[WA-Webhook] Error: ' . $e->getMessage());
        }

        // Always return 200 so Dovesoft stops retrying
        return response()->json(['status' => 'ok']);
    }

    /** Dovesoft may do a GET verification challenge */
    public function verify(Request $request)
    {
        $challenge = $request->query('hub_challenge');
        if ($challenge) return response($challenge, 200);
        return response()->json(['status' => 'ok']);
    }
}
