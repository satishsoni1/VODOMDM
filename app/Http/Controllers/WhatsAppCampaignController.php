<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignContact;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsAppCampaignController extends Controller
{
    public function index()
    {
        $campaigns = WhatsAppCampaign::with('template', 'createdBy')
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'total'     => WhatsAppCampaign::count(),
            'running'   => WhatsAppCampaign::where('status', 'running')->count(),
            'completed' => WhatsAppCampaign::where('status', 'completed')->count(),
            'scheduled' => WhatsAppCampaign::where('status', 'scheduled')->count(),
        ];

        return view('whatsapp.campaigns.index', compact('campaigns', 'stats'));
    }

    public function create()
    {
        $templates = WhatsAppTemplate::where('status', 'approved')->orderBy('name')->get();
        return view('whatsapp.campaigns.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'template_id'    => 'nullable|exists:whatsapp_templates,id',
            'custom_message' => 'required_without:template_id|nullable|string',
            'contacts_csv'   => 'required|file|mimes:csv,txt|max:5120',
            'scheduled_at'   => 'nullable|date|after:now',
        ]);

        $campaign = WhatsAppCampaign::create([
            'name'           => $request->name,
            'template_id'    => $request->template_id,
            'custom_message' => $request->custom_message,
            'status'         => $request->scheduled_at ? 'scheduled' : 'draft',
            'scheduled_at'   => $request->scheduled_at,
            'created_by'     => Auth::id(),
        ]);

        // Parse CSV and create contacts
        $file    = $request->file('contacts_csv');
        $handle  = fopen($file->getRealPath(), 'r');
        $header  = fgetcsv($handle);     // phone, name, var1, var2, ...
        $count   = 0;
        $batch   = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(trim($row[0] ?? ''))) continue;
            $vars = [];
            foreach (array_slice($header, 2) as $i => $col) {
                $vars[$col] = $row[$i + 2] ?? '';
            }
            $batch[] = [
                'campaign_id' => $campaign->id,
                'phone'       => trim($row[0]),
                'name'        => trim($row[1] ?? ''),
                'variables'   => $vars ? json_encode($vars) : null,
                'status'      => 'pending',
            ];
            $count++;

            if (count($batch) >= 200) {
                WhatsAppCampaignContact::insert($batch);
                $batch = [];
            }
        }
        if ($batch) WhatsAppCampaignContact::insert($batch);
        fclose($handle);

        $campaign->update(['total_contacts' => $count]);

        return redirect()->route('whatsapp.campaigns.show', $campaign)
            ->with('success', "Campaign created with {$count} contacts.");
    }

    public function show(WhatsAppCampaign $campaign)
    {
        $campaign->load('template', 'createdBy');
        $contacts = $campaign->contacts()->orderBy('id')->paginate(50);
        return view('whatsapp.campaigns.show', compact('campaign', 'contacts'));
    }

    public function launch(WhatsAppCampaign $campaign)
    {
        if (! in_array($campaign->status, ['draft', 'scheduled'])) {
            return back()->with('error', 'Campaign cannot be launched in current status.');
        }

        $svc = new WhatsAppService();
        $svc->launchCampaign($campaign);

        return redirect()->route('whatsapp.campaigns.show', $campaign)
            ->with('success', 'Campaign launched.');
    }

    public function cancel(WhatsAppCampaign $campaign)
    {
        $campaign->update(['status' => 'cancelled']);
        return back()->with('success', 'Campaign cancelled.');
    }
}
