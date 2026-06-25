@extends('layouts.main')
@section('title','WhatsApp Settings')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('whatsapp.index') }}">WhatsApp</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8">

        {{-- Current Config --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <h5 class="mb-0 fw-bold" style="color:var(--gs-teal-dark)">
                    <i class="bi bi-gear me-2"></i>WhatsApp Configuration
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info border-0 small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Settings are loaded from your <code>.env</code> file. To change them, update the corresponding environment variables and restart the server.
                </div>

                <table class="table table-sm small">
                    <thead class="table-light">
                        <tr><th>Setting</th><th>.env Key</th><th>Current Value</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-semibold">Driver</td>
                            <td class="font-monospace text-muted">WHATSAPP_DRIVER</td>
                            <td>
                                @php $driver = config('whatsapp.driver','log'); @endphp
                                <span class="badge bg-{{ $driver==='dovesoft' ? 'success' : 'warning' }}-subtle text-{{ $driver==='dovesoft' ? 'success' : 'warning' }} border">
                                    {{ $driver }}
                                </span>
                                @if($driver==='log')
                                <span class="text-muted ms-1 small">(messages only logged, not sent)</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">API URL</td>
                            <td class="font-monospace text-muted">DOVESOFT_API_URL</td>
                            <td class="font-monospace">{{ config('whatsapp.api_url') ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">API Key</td>
                            <td class="font-monospace text-muted">DOVESOFT_API_KEY</td>
                            <td>
                                @php $key = config('whatsapp.api_key',''); @endphp
                                @if($key)
                                <span class="text-muted font-monospace">{{ substr($key,0,6) }}••••••</span>
                                <span class="badge bg-success-subtle text-success border ms-1">Configured</span>
                                @else
                                <span class="badge bg-danger-subtle text-danger border">Not set</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Sender</td>
                            <td class="font-monospace text-muted">DOVESOFT_SENDER</td>
                            <td class="font-monospace">{{ config('whatsapp.sender') ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Timeout</td>
                            <td class="font-monospace text-muted">—</td>
                            <td>{{ config('whatsapp.timeout',15) }}s</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- .env snippet --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-code-square me-2"></i>Required .env Variables</strong>
            </div>
            <div class="card-body">
                <pre class="bg-dark text-light rounded p-3 small mb-0" style="font-size:.82rem"># WhatsApp (Dovesoft)
WHATSAPP_DRIVER=dovesoft          # set to 'log' for testing
DOVESOFT_API_URL=https://app.dovesoft.in/api/send-message
DOVESOFT_API_KEY=your_api_key_here
DOVESOFT_SENDER=your_sender_id

# MDM Server (Headwind)
HEADWIND_MDM_URL=https://g-mdm.globalspace.in
HEADWIND_MDM_LOGIN=admin
HEADWIND_MDM_PASSWORD=your_password

# Employee / General API Token
APP_API_TOKEN=your_secure_random_token_here</pre>
            </div>
        </div>

        {{-- Trigger Events --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-lightning me-2"></i>Available Trigger Events</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm small mb-0">
                    <thead class="table-light"><tr><th class="ps-3">Event Key</th><th>Label</th><th>When to Use</th></tr></thead>
                    <tbody>
                        @foreach(\App\Models\WhatsAppMessage::allTriggerEvents() as $key => $label)
                        <tr>
                            <td class="ps-3 font-monospace text-muted">{{ $key }}</td>
                            <td class="fw-semibold">{{ $label }}</td>
                            <td class="text-muted">
                                @switch($key)
                                    @case('device_handover') Sent when a device is handed over to an employee @break
                                    @case('device_returned') Sent when a device is returned @break
                                    @case('ticket_created') Sent when a new ticket is raised @break
                                    @case('ticket_resolved') Sent when a ticket is closed/resolved @break
                                    @case('device_recovered') Sent when a recovery case is closed @break
                                    @case('employee_offboarded') Sent on employee departure @break
                                    @case('mdm_alert') Sent on MDM device offline / compliance alert @break
                                    @case('manual') Ad-hoc manual message @break
                                    @default —
                                @endswitch
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pending queue summary --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-list-check me-2"></i>Queue Status</strong>
            </div>
            <div class="card-body">
                @php
                $pending   = \App\Models\WhatsAppMessage::where('status','pending')->count();
                $scheduled = \App\Models\WhatsAppMessage::where('status','scheduled')->whereNotNull('scheduled_at')->count();
                $due       = \App\Models\WhatsAppMessage::where('status','scheduled')->where('scheduled_at','<=',now())->count()
                           + \App\Models\WhatsAppMessage::where('status','pending')->whereNull('scheduled_at')->count();
                @endphp
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="fw-bold fs-4 text-warning">{{ $pending }}</div>
                        <div class="text-muted small">Pending</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-4 text-info">{{ $scheduled }}</div>
                        <div class="text-muted small">Scheduled</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-4 text-danger">{{ $due }}</div>
                        <div class="text-muted small">Due Now</div>
                    </div>
                </div>
                @if($due > 0)
                <div class="mt-3 text-center">
                    <form method="POST" action="{{ route('whatsapp.process-due') }}" class="d-inline">
                        @csrf
                        <button class="btn btn-success btn-sm">
                            <i class="bi bi-send me-1"></i>Process {{ $due }} Due Messages
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
