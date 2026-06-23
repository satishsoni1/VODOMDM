@extends('client-portal.layout')
@section('title', $device->serial_number)
@section('page-title', 'Device Detail')

@section('content')
@php $mdm = $device->mdmPortalDevice; @endphp

<div class="mb-3">
    <a href="{{ route('client.devices') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Devices
    </a>
</div>

<div class="row g-4">
    {{-- ── Left: Device Info ──────────────────────────────────────────────── --}}
    <div class="col-xl-8">

        {{-- Header card --}}
        <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid var(--gs-teal) !important">
            <div class="card-body d-flex align-items-center gap-4">
                <div class="kpi-icon kpi-teal" style="width:56px;height:56px;border-radius:14px;font-size:1.6rem">
                    <i class="bi bi-phone-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="mb-0 fw-bold" style="color:var(--gs-teal-dark)">
                        {{ $device->model?->name ?? 'Device' }}
                        <small class="text-muted fw-normal ms-2">{{ $device->model?->brand?->name }}</small>
                    </h5>
                    <div class="text-muted small mt-1">
                        Serial: <span class="font-monospace fw-semibold">{{ $device->serial_number }}</span>
                        @if($device->imei1)
                        &nbsp;| IMEI: <span class="font-monospace">{{ $device->imei1 }}</span>
                        @endif
                    </div>
                </div>
                <div>
                    <span class="badge rounded-pill fs-6 px-3 py-2 bg-light text-secondary border">
                        {{ str_replace('_',' ', ucfirst($device->lifecycle_status)) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Device details --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-info-circle me-2"></i>Device Information</strong>
            </div>
            <div class="card-body">
                <div class="row g-3 small">
                    <div class="col-md-4">
                        <div class="text-muted">Asset Tag</div>
                        <div class="fw-semibold">{{ $device->asset_tag ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Color</div>
                        <div class="fw-semibold">{{ $device->color ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Condition</div>
                        <div class="fw-semibold">{{ ucfirst($device->condition ?? '—') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Purchase Date</div>
                        <div class="fw-semibold">{{ $device->purchase_date?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Warranty Expiry</div>
                        <div class="fw-semibold">
                            @if($device->warranty_expiry)
                                {{ $device->warranty_expiry->format('d M Y') }}
                                @if($device->warranty_expiry->isPast())
                                    <span class="badge bg-danger-subtle text-danger border ms-1">Expired</span>
                                @else
                                    <span class="badge bg-success-subtle text-success border ms-1">Active</span>
                                @endif
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">IMEI 2</div>
                        <div class="fw-semibold font-monospace">{{ $device->imei2 ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MDM Status --}}
        @if($mdm)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-phone-vibrate me-2"></i>MDM / Remote Status</strong>
                <span class="badge rounded-pill {{ $mdm->isOnline() ? 'badge-on' : 'badge-off' }} ms-auto">
                    <i class="bi bi-circle-fill me-1" style="font-size:.5rem"></i>
                    {{ $mdm->isOnline() ? 'Online' : 'Offline' }}
                </span>
            </div>
            <div class="card-body">
                <div class="row g-3 small">
                    <div class="col-md-4">
                        <div class="text-muted">MDM Number</div>
                        <div class="fw-semibold font-monospace">{{ $mdm->mdm_number }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Group</div>
                        <div class="fw-semibold">{{ $mdm->mdm_group ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Configuration</div>
                        <div class="fw-semibold">{{ $mdm->configuration ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Last Sync</div>
                        <div class="fw-semibold">
                            {{ $mdm->sync_time?->format('d M Y H:i') ?? '—' }}
                            <span class="badge bg-{{ $mdm->syncFreshnessClass() }}-subtle text-{{ $mdm->syncFreshnessClass() }} border ms-1">
                                {{ $mdm->syncAgeLabel() }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Android Version</div>
                        <div class="fw-semibold">{{ $mdm->android_version ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Launcher</div>
                        <div class="fw-semibold">v{{ $mdm->launcher_version ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">IP Address</div>
                        <div class="fw-semibold font-monospace">{{ $mdm->ip_address ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">MDM Mode</div>
                        <div>
                            @if($mdm->mdm_mode)
                                <span class="badge bg-success-subtle text-success border">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Permission Status</div>
                        <div>
                            @if($mdm->isPermissionCompliant())
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span class="text-success fw-semibold">All granted</span>
                            @else
                                <i class="bi bi-x-circle-fill text-danger"></i>
                                <span class="text-danger fw-semibold small">{{ $mdm->permission_status }}</span>
                            @endif
                        </div>
                    </div>
                    @if($mdm->latitude && $mdm->longitude)
                    <div class="col-12">
                        <div class="text-muted mb-1">Last Known Location</div>
                        <a href="https://maps.google.com/?q={{ $mdm->latitude }},{{ $mdm->longitude }}"
                            target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-geo-alt me-1"></i>
                            {{ $mdm->latitude }}, {{ $mdm->longitude }}
                            {{ $mdm->location_raw ? "— {$mdm->location_raw}" : '' }}
                            <i class="bi bi-box-arrow-up-right ms-1" style="font-size:.7rem"></i>
                        </a>
                    </div>
                    @endif
                </div>

                @php $apps = $mdm->parsedApps(); @endphp
                @if(!empty($apps))
                <hr class="my-3">
                <div class="section-header mb-2">App Installation Status</div>
                <table class="table table-sm small mb-0">
                    <thead class="table-light">
                        <tr><th>App</th><th>Installed</th><th>Available</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($apps as $app)
                        <tr>
                            <td class="fw-semibold">{{ $app['name'] }}</td>
                            <td class="font-monospace">{{ $app['installed'] }}</td>
                            <td class="font-monospace">{{ $app['available'] ?? '—' }}</td>
                            <td>
                                @if($app['outdated'] ?? false)
                                <span class="badge bg-warning-subtle text-warning border">Update available</span>
                                @else
                                <span class="badge bg-success-subtle text-success border">Up to date</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
        @endif

        {{-- Handover History --}}
        @if($device->handovers?->isNotEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-person-check me-2"></i>Assignment History</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm small mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-3">Employee</th><th>Assigned</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach($device->handovers->sortByDesc('created_at') as $h)
                        <tr>
                            <td class="ps-3">{{ $h->employee?->name ?? '—' }}</td>
                            <td>{{ $h->created_at?->format('d M Y') }}</td>
                            <td>
                                <span class="badge bg-light text-secondary border">{{ ucfirst($h->status) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Right: Employee + Tickets ──────────────────────────────────────── --}}
    <div class="col-xl-4">

        {{-- Assigned Employee --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-person me-2"></i>Assigned To</strong>
            </div>
            <div class="card-body">
                @if($device->currentEmployee)
                @php $emp = $device->currentEmployee; @endphp
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="kpi-icon kpi-teal" style="width:48px;height:48px;border-radius:12px;font-size:1.2rem">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="color:var(--gs-teal-dark)">{{ $emp->name }}</div>
                        <div class="text-muted small">{{ $emp->designation }}</div>
                    </div>
                </div>
                <table class="table table-sm small mb-0">
                    <tr><td class="text-muted">Code</td><td class="fw-semibold">{{ $emp->employee_code }}</td></tr>
                    <tr><td class="text-muted">Phone</td><td>{{ $emp->phone ?? '—' }}</td></tr>
                    <tr><td class="text-muted">City</td><td>{{ $emp->city ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Region</td><td>{{ $emp->region ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Dept</td><td>{{ $emp->department ?? '—' }}</td></tr>
                </table>
                @else
                <div class="text-muted small text-center py-3">No employee assigned</div>
                @endif
            </div>
        </div>

        {{-- Open Tickets --}}
        @if($device->tickets?->isNotEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-ticket-perforated me-2"></i>Support Tickets</strong>
            </div>
            <div class="card-body p-0">
                @foreach($device->tickets->sortByDesc('created_at') as $ticket)
                <div class="px-3 py-2 border-bottom small">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="fw-semibold">{{ $ticket->ticket_number }}</span>
                        @php $sc = match($ticket->status) { 'open'=>'danger','in_progress'=>'warning','resolved'=>'success','closed'=>'secondary', default=>'secondary' }; @endphp
                        <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} border">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                    </div>
                    <div class="text-muted mt-1">{{ Str::limit($ticket->subject, 60) }}</div>
                    <div class="text-muted" style="font-size:.72rem">{{ $ticket->created_at?->format('d M Y') }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
