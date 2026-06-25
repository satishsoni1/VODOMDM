<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsAppController extends Controller
{
    public function index(Request $request)
    {
        $tab   = $request->input('tab', 'outbound');  // outbound | inbound
        $query = WhatsAppMessage::with('createdBy', 'campaign')
            ->where('direction', $tab === 'inbound' ? 'inbound' : 'outbound')
            ->orderByDesc('created_at');

        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('event'))   $query->where('trigger_event', $request->event);
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($sq) =>
                $sq->where('to_phone', 'LIKE', "%{$q}%")
                   ->orWhere('to_name', 'LIKE', "%{$q}%")
                   ->orWhere('message_text', 'LIKE', "%{$q}%")
            );
        }

        $messages = $query->paginate(25)->withQueryString();

        $stats = [
            'pending'   => WhatsAppMessage::where('status', 'pending')->where('direction', 'outbound')->count(),
            'sent'      => WhatsAppMessage::where('status', 'sent')->where('direction', 'outbound')->count(),
            'failed'    => WhatsAppMessage::where('status', 'failed')->where('direction', 'outbound')->count(),
            'inbound'   => WhatsAppMessage::where('direction', 'inbound')->count(),
        ];

        return view('whatsapp.index', compact('messages', 'stats', 'tab'));
    }

    public function create()
    {
        $events = WhatsAppMessage::allTriggerEvents();
        return view('whatsapp.create', compact('events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'to_phone'      => 'required|string|max:20',
            'to_name'       => 'nullable|string|max:150',
            'message_text'  => 'required|string|max:4096',
            'trigger_event' => 'required|string',
            'scheduled_at'  => 'nullable|date|after:now',
        ]);

        $msg = WhatsAppMessage::create(array_merge($validated, [
            'status'     => 'pending',
            'created_by' => Auth::id(),
        ]));

        // Send immediately if no schedule
        if (! $msg->scheduled_at) {
            $svc = new WhatsAppService();
            $svc->send($msg);
        }

        return redirect()->route('whatsapp.index')
            ->with('success', $msg->scheduled_at
                ? "Message scheduled for {$msg->scheduled_at->format('d M Y H:i')}."
                : "Message sent to {$msg->to_phone}.");
    }

    public function sendNow(WhatsAppMessage $whatsapp)
    {
        $svc = new WhatsAppService();
        $svc->send($whatsapp);
        return back()->with('success', "Message sent to {$whatsapp->to_phone}.");
    }

    public function cancel(WhatsAppMessage $whatsapp)
    {
        $whatsapp->update(['status' => 'cancelled']);
        return back()->with('success', 'Message cancelled.');
    }

    public function processDue()
    {
        $svc  = new WhatsAppService();
        $sent = $svc->processDue();
        return back()->with('success', "Processed {$sent} pending message(s).");
    }

    /** Settings page */
    public function settings()
    {
        $config = [
            'driver'  => config('whatsapp.driver'),
            'api_url' => config('whatsapp.api_url'),
            'sender'  => config('whatsapp.sender'),
            'has_key' => ! empty(config('whatsapp.api_key')),
        ];
        return view('whatsapp.settings', compact('config'));
    }
}
