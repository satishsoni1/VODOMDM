<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsAppTemplateController extends Controller
{
    public function index()
    {
        $templates = WhatsAppTemplate::withCount('campaigns')
            ->orderBy('name')
            ->paginate(20);
        return view('whatsapp.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('whatsapp.templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:512|unique:whatsapp_templates,name',
            'category'    => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
            'language'    => 'required|string|max:10',
            'header_type' => 'nullable|in:TEXT,IMAGE,VIDEO,DOCUMENT',
            'header_text' => 'nullable|string|max:60',
            'body_text'   => 'required|string|max:1024',
            'footer_text' => 'nullable|string|max:60',
            'variables'   => 'nullable|array',
            'variables.*' => 'string|max:50',
        ]);

        $data['created_by'] = Auth::id();
        $template = WhatsAppTemplate::create($data);

        // Submit to Dovesoft
        $svc    = new WhatsAppService();
        $result = $svc->createTemplate($template);

        $msg = $result['success']
            ? 'Template submitted to Dovesoft — awaiting approval.'
            : 'Template saved locally. Dovesoft submission failed: ' . ($result['message'] ?? '');

        return redirect()->route('whatsapp.templates.index')
            ->with($result['success'] ? 'success' : 'error', $msg);
    }

    public function show(WhatsAppTemplate $whatsappTemplate)
    {
        $whatsappTemplate->load('campaigns');
        return view('whatsapp.templates.show', ['template' => $whatsappTemplate]);
    }

    public function sync()
    {
        $svc    = new WhatsAppService();
        $result = $svc->syncTemplates();

        return redirect()->route('whatsapp.templates.index')
            ->with($result['success'] ?? false ? 'success' : 'error',
                "Synced {$result['synced']} templates from Dovesoft." . ($result['message'] ?? ''));
    }

    public function destroy(WhatsAppTemplate $whatsappTemplate)
    {
        $whatsappTemplate->delete();
        return redirect()->route('whatsapp.templates.index')->with('success', 'Template deleted.');
    }
}
