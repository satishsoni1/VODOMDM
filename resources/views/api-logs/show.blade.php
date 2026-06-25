@extends('layouts.main')
@section('title','API Log Detail')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('api-logs.index') }}">API Logs</a></li>
    <li class="breadcrumb-item active">#{{ $apiLog->id }}</li>
@endsection

@section('content')
<div class="mb-3">
    <a href="{{ route('api-logs.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        {{-- Header --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                    style="width:52px;height:52px;background:var(--gs-teal-light)">
                    <i class="bi bi-journal-code" style="color:var(--gs-teal);font-size:1.4rem"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="mb-0 fw-bold" style="color:var(--gs-teal-dark)">
                        {{ ucfirst(str_replace('_',' ',$apiLog->type)) }}
                        <span class="text-muted fw-normal fs-6 ms-2">/ {{ $apiLog->action }}</span>
                    </h5>
                    <div class="text-muted small mt-1">{{ $apiLog->summary ?? 'No summary' }}</div>
                </div>
                <span class="badge bg-{{ $apiLog->statusBadgeClass() }}-subtle text-{{ $apiLog->statusBadgeClass() }} border fs-6 px-3 py-2">
                    {{ ucfirst($apiLog->status) }}
                </span>
            </div>
        </div>

        {{-- Step Timeline --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-list-check me-2"></i>Step Tracker ({{ $steps->count() }} steps)</strong>
            </div>
            <div class="card-body p-0">
                @if($steps->isEmpty())
                <div class="text-center py-4 text-muted small">No sub-steps recorded.</div>
                @else
                @foreach($steps as $i => $step)
                @php
                    $isLast = $loop->last;
                    $sc     = $step->statusBadgeClass();
                    $icon   = match($step->action) {
                        'authenticate'  => 'bi-key-fill',
                        'fetch_devices' => 'bi-cloud-download',
                        'upsert'        => 'bi-database-fill-up',
                        'auto_match'    => 'bi-link-45deg',
                        'send'          => 'bi-send-fill',
                        'error'         => 'bi-exclamation-triangle-fill',
                        default         => 'bi-circle-fill',
                    };
                @endphp
                <div class="d-flex gap-3 px-3 py-3 {{ !$isLast ? 'border-bottom' : '' }}">
                    <div class="d-flex flex-column align-items-center" style="width:28px;flex-shrink:0">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                            style="width:28px;height:28px;background:{{ $step->status==='failed' ? '#fce8e8' : 'var(--gs-teal-light)' }}">
                            <i class="bi {{ $icon }}" style="font-size:.75rem;color:{{ $step->status==='failed' ? '#dc3545' : 'var(--gs-teal)' }}"></i>
                        </div>
                        @if(!$isLast)
                        <div style="width:2px;flex:1;background:#e8f5f3;margin-top:4px"></div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-semibold small font-monospace">{{ $step->action }}</span>
                            <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} border" style="font-size:.68rem">{{ ucfirst($step->status) }}</span>
                            @if($step->duration_ms)
                            <span class="text-muted" style="font-size:.72rem">{{ number_format($step->duration_ms/1000,2) }}s</span>
                            @endif
                            @if($step->records_in || $step->records_out)
                            <span class="text-muted" style="font-size:.72rem">
                                @if($step->records_in) in:{{ $step->records_in }} @endif
                                @if($step->records_out) out:{{ $step->records_out }} @endif
                            </span>
                            @endif
                            <span class="ms-auto text-muted" style="font-size:.7rem">{{ $step->created_at->format('H:i:s') }}</span>
                        </div>
                        <div class="small text-muted">{{ $step->summary }}</div>
                        @if($step->error_message)
                        <div class="mt-1 small text-danger"><i class="bi bi-exclamation-circle me-1"></i>{{ $step->error_message }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>

        {{-- Request / Response --}}
        @if($apiLog->request_data || $apiLog->response_data)
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-braces me-2"></i>Payload</strong>
            </div>
            <div class="card-body">
                @if($apiLog->request_data)
                <div class="mb-3">
                    <div class="small fw-bold text-muted mb-1">REQUEST</div>
                    <pre class="bg-light rounded p-3 small mb-0" style="max-height:200px;overflow-y:auto">{{ json_encode($apiLog->request_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
                @endif
                @if($apiLog->response_data)
                <div>
                    <div class="small fw-bold text-muted mb-1">RESPONSE</div>
                    <pre class="bg-light rounded p-3 small mb-0" style="max-height:200px;overflow-y:auto">{{ json_encode($apiLog->response_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar meta --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)">Run Details</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm small mb-0">
                    <tr><td class="text-muted ps-3">Log ID</td><td class="fw-semibold">#{{ $apiLog->id }}</td></tr>
                    <tr><td class="text-muted ps-3">Type</td><td>{{ ucfirst(str_replace('_',' ',$apiLog->type)) }}</td></tr>
                    <tr><td class="text-muted ps-3">Action</td><td class="font-monospace">{{ $apiLog->action }}</td></tr>
                    <tr><td class="text-muted ps-3">Status</td>
                        <td><span class="badge bg-{{ $apiLog->statusBadgeClass() }}-subtle text-{{ $apiLog->statusBadgeClass() }} border">{{ ucfirst($apiLog->status) }}</span></td>
                    </tr>
                    <tr><td class="text-muted ps-3">Started</td><td>{{ $apiLog->created_at->format('d M Y H:i:s') }}</td></tr>
                    <tr><td class="text-muted ps-3">Duration</td><td>{{ $apiLog->duration_ms ? number_format($apiLog->duration_ms/1000,2).'s' : '—' }}</td></tr>
                    <tr><td class="text-muted ps-3">Records In</td><td>{{ $apiLog->records_in ?? '—' }}</td></tr>
                    <tr><td class="text-muted ps-3">Records Out</td><td>{{ $apiLog->records_out ?? '—' }}</td></tr>
                    <tr><td class="text-muted ps-3">Triggered By</td><td>{{ $apiLog->triggeredBy?->name ?? 'System' }}</td></tr>
                    <tr><td class="text-muted ps-3">IP Address</td><td class="font-monospace">{{ $apiLog->ip_address ?? '—' }}</td></tr>
                    @if($apiLog->error_message)
                    <tr><td class="text-muted ps-3" colspan="2">
                        <div class="text-danger small p-2"><i class="bi bi-exclamation-triangle me-1"></i>{{ $apiLog->error_message }}</div>
                    </td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
