@extends('layouts.main')

@section('title', 'Device — ' . $device->asset_tag)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('devices.index') }}">Devices</a></li>
    <li class="breadcrumb-item active">{{ $device->asset_tag }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-phone me-2"></i>{{ $device->model?->brand?->name }} {{ $device->model?->model_name }}
        <span class="badge bg-secondary ms-2">{{ $device->asset_tag }}</span>
    </h5>
    <div class="d-flex gap-2">
        <a href="{{ route('devices.edit', $device) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('handovers.create', ['device_id' => $device->id]) }}" class="btn btn-sm btn-success"><i class="bi bi-person-check"></i> Handover</a>
        <a href="{{ route('tickets.create', ['device_id' => $device->id]) }}" class="btn btn-sm btn-warning"><i class="bi bi-ticket-perforated"></i> Raise Ticket</a>
    </div>
</div>

<div class="row g-3">
    {{-- Identity Card --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><strong>Device Identity</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted w-50">Asset Tag</td><td class="font-monospace fw-bold">{{ $device->asset_tag }}</td></tr>
                    <tr><td class="text-muted">Serial Number</td><td class="font-monospace">{{ $device->serial_number }}</td></tr>
                    <tr><td class="text-muted">IMEI 1</td><td class="font-monospace">{{ $device->imei1 ?? '—' }}</td></tr>
                    <tr><td class="text-muted">IMEI 2</td><td class="font-monospace">{{ $device->imei2 ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Model</td><td>{{ $device->model?->model_name }}</td></tr>
                    <tr><td class="text-muted">Brand</td><td>{{ $device->model?->brand?->name }}</td></tr>
                    <tr><td class="text-muted">Color</td><td>{{ $device->color ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Box #</td><td>{{ $device->box_number ?? '—' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Status / Current Owner --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><strong>Current Status</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="badge fs-6 bg-primary">{{ ucwords(str_replace('_', ' ', $device->lifecycle_status)) }}</span>
                </div>
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted w-50">Employee</td><td>{{ $device->currentEmployee?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Emp Code</td><td>{{ $device->currentEmployee?->employee_code ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Client</td><td>{{ $device->client?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Location</td><td>{{ $device->currentLocation?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Condition</td><td>{{ ucfirst($device->condition) }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Financial --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><strong>Financial & Warranty</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted w-50">Purchase Price</td><td>₹{{ number_format($device->purchase_price ?? 0) }}</td></tr>
                    <tr><td class="text-muted">Purchase Date</td><td>{{ $device->purchase_date?->format('d M Y') ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Warranty</td><td>{{ $device->warranty_months ? $device->warranty_months.' months' : '—' }}</td></tr>
                    <tr><td class="text-muted">Warranty Expiry</td><td>{{ $device->warranty_expiry?->format('d M Y') ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Vendor</td><td>{{ $device->vendor?->name ?? '—' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- MDM Status --}}
    @if($device->latestMdmSync)
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong><i class="bi bi-wifi text-primary"></i> MDM Status</strong></div>
            <div class="card-body">
                @php $mdm = $device->latestMdmSync; @endphp
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Last Sync</small>
                        <div>{{ $mdm->synced_at?->format('d M Y H:i') }}</div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Battery</small>
                        <div>{{ $mdm->battery_level ?? '—' }}</div>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted">OS Version</small>
                        <div>{{ $mdm->os_version ?? '—' }}</div>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted">SIM</small>
                        <div>{{ $mdm->sim_operator ?? '—' }}</div>
                    </div>
                    @if($mdm->is_rooted)
                        <div class="col-12 mt-2"><span class="badge bg-danger">⚠ ROOTED DEVICE</span></div>
                    @endif
                    @if($mdm->sim_changed)
                        <div class="col-12 mt-2"><span class="badge bg-warning text-dark">⚠ SIM CHANGED</span></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Device Timeline --}}
    <div class="col-md-{{ $device->latestMdmSync ? '6' : '12' }}">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong><i class="bi bi-clock-history me-1"></i>Device Timeline</strong>
                <span class="badge bg-secondary">{{ $device->events->count() }} events</span>
            </div>
            <div class="card-body" style="max-height:320px;overflow-y:auto">
                @forelse($device->events->take(20) as $event)
                <div class="d-flex gap-2 mb-2">
                    <div class="text-muted" style="min-width:120px;font-size:.75rem">{{ $event->event_at?->format('d M Y H:i') }}</div>
                    <div>
                        <span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $event->event_type) }}</span>
                        <span class="small ms-1">{{ $event->description }}</span>
                    </div>
                </div>
                @empty
                <div class="text-muted text-center py-2">No events recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Tickets --}}
    @if($device->tickets->isNotEmpty())
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong>Support Tickets</strong></div>
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light"><tr><th>#</th><th>Subject</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @foreach($device->tickets->take(5) as $ticket)
                    <tr>
                        <td><a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none">{{ $ticket->ticket_number }}</a></td>
                        <td class="text-truncate" style="max-width:150px">{{ $ticket->subject }}</td>
                        <td><span class="badge bg-secondary">{{ $ticket->status }}</span></td>
                        <td class="small">{{ $ticket->created_at->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Handover History --}}
    @if($device->handovers->isNotEmpty())
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong>Handover History</strong></div>
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light"><tr><th>Date</th><th>Employee</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($device->handovers as $ho)
                    <tr>
                        <td>{{ $ho->handover_date?->format('d M Y') }}</td>
                        <td>{{ $ho->employee?->name }}</td>
                        <td><span class="badge bg-secondary">{{ $ho->status }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
