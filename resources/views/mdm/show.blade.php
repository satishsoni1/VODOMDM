@extends('layouts.main')
@section('title','MDM Device — '.$mdm->mdm_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mdm.index') }}">MDM</a></li>
    <li class="breadcrumb-item"><a href="{{ route('mdm.devices') }}">Devices</a></li>
    <li class="breadcrumb-item active">{{ $mdm->mdm_number }}</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- LEFT: Device Details --}}
    <div class="col-xl-8">

        {{-- Header card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0"
                         style="width:56px;height:56px;background:#0d6efd;font-size:1.4rem">
                        <i class="bi bi-phone-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="fw-bold mb-0">{{ $mdm->mdm_number }}</h4>
                        <div class="text-muted">{{ $mdm->model ?? 'Unknown Model' }} &middot; Android {{ $mdm->android_version ?? '?' }}</div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        @php $b = $mdm->device_status === 'on' ? 'success' : 'danger'; @endphp
                        <span class="badge bg-{{ $b }}-subtle text-{{ $b }} border px-3 py-2 fs-6">
                            <i class="bi bi-wifi{{ $mdm->device_status === 'on' ? '' : '-off' }} me-1"></i>
                            {{ strtoupper($mdm->device_status ?? 'UNKNOWN') }}
                        </span>
                        @if($mdm->mdm_mode)
                        <span class="badge bg-primary-subtle text-primary border px-3 py-2">MDM Mode</span>
                        @endif
                        @if($mdm->kiosk_mode)
                        <span class="badge bg-warning-subtle text-warning border px-3 py-2">Kiosk Mode</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Device Info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header"><strong><i class="bi bi-info-circle me-2"></i>Device Information</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    @php
                    $info = [
                        ['MDM Number',    $mdm->mdm_number],
                        ['Serial Number', $mdm->serial_number ?? '—'],
                        ['IMEI',          $mdm->imei ?? '—'],
                        ['Phone',         $mdm->phone ?? '—'],
                        ['Model',         $mdm->model ?? '—'],
                        ['Android',       $mdm->android_version ?? '—'],
                        ['Group',         $mdm->mdm_group ?? '—'],
                        ['Configuration', $mdm->configuration ?? '—'],
                        ['Launcher',      $mdm->default_launcher ?? '—'],
                        ['Launcher Ver',  $mdm->launcher_version ?? '—'],
                        ['IP Address',    $mdm->ip_address ?? '—'],
                        ['Public IP',     $mdm->public_ip ?? '—'],
                        ['Enrolled',      $mdm->enrollment_date?->format('d M Y H:i') ?? '—'],
                        ['Division',      $mdm->division ?? '—'],
                        ['Description',   $mdm->description ?? '—'],
                    ];
                    @endphp
                    @foreach($info as $row)
                    <div class="col-md-4">
                        <div class="text-muted small">{{ $row[0] }}</div>
                        <div class="fw-semibold font-monospace" style="font-size:.85rem">{{ $row[1] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Sync Status --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header"><strong><i class="bi bi-clock-history me-2 text-warning"></i>Sync & Connectivity</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Last Sync Time</div>
                        <div class="fw-bold">{{ $mdm->sync_time?->format('d M Y H:i:s') ?? '—' }}</div>
                        <div class="text-{{ $mdm->syncFreshnessClass() }} small">{{ $mdm->syncAgeLabel() }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Permission Status</div>
                        @if($mdm->isPermissionCompliant())
                            <span class="badge bg-success-subtle text-success border py-2 px-3">
                                <i class="bi bi-shield-check me-1"></i>All Permissions Granted
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border py-2 px-3">
                                <i class="bi bi-shield-x me-1"></i>Permission Issues
                            </span>
                            <div class="small text-muted mt-1">{{ $mdm->permission_status }}</div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">MDM Status</div>
                        <div class="fw-semibold">{{ $mdm->mdm_status ?? '—' }}</div>
                        <div class="text-muted small">Synced at: {{ $mdm->synced_at?->format('d M H:i') ?? '—' }}</div>
                    </div>
                </div>

                @if($mdm->latitude && $mdm->longitude)
                <div class="mt-3 p-3 bg-light rounded d-flex align-items-center gap-3">
                    <i class="bi bi-geo-alt-fill text-danger fs-4"></i>
                    <div>
                        <div class="fw-semibold">Last Known Location</div>
                        <div class="font-monospace small">Lat: {{ $mdm->latitude }} &nbsp; Lng: {{ $mdm->longitude }}</div>
                        @if($mdm->location_raw)
                        <div class="text-muted small">{{ $mdm->location_raw }}</div>
                        @endif
                        <a href="https://maps.google.com/?q={{ $mdm->latitude }},{{ $mdm->longitude }}" target="_blank" class="btn btn-outline-primary btn-sm mt-1 py-0">
                            <i class="bi bi-map me-1"></i>Open in Google Maps
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- App Installation Status --}}
        @if(count($apps) > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <strong><i class="bi bi-grid me-2 text-primary"></i>App Installation Status</strong>
                @if(collect($apps)->where('outdated', true)->count() > 0)
                <span class="badge bg-warning-subtle text-warning border">
                    {{ collect($apps)->where('outdated', true)->count() }} app(s) outdated
                </span>
                @else
                <span class="badge bg-success-subtle text-success border">All apps current</span>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light">
                        <tr><th>App</th><th>Installed</th><th>Available</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach($apps as $app)
                        <tr class="{{ $app['outdated'] ? 'table-warning' : '' }}">
                            <td class="fw-semibold">{{ $app['name'] }}</td>
                            <td class="font-monospace">{{ $app['installed'] }}</td>
                            <td class="font-monospace">{{ $app['available'] ?? '—' }}</td>
                            <td>
                                @if($app['outdated'])
                                    <span class="badge bg-warning-subtle text-warning border">
                                        <i class="bi bi-arrow-up-circle me-1"></i>Update Available
                                    </span>
                                @elseif($app['available'])
                                    <span class="badge bg-success-subtle text-success border">
                                        <i class="bi bi-check-circle me-1"></i>Up to date
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border">Installed</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- RIGHT: Links & Employee --}}
    <div class="col-xl-4">

        {{-- Employee Link --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><strong><i class="bi bi-person-check me-2 text-success"></i>Linked Employee</strong></div>
            <div class="card-body">
                @if($mdm->employee)
                <div class="d-flex align-items-start gap-2 mb-3">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;font-weight:700">
                        {{ strtoupper(substr($mdm->employee->name,0,1)) }}
                    </div>
                    <div>
                        <div class="fw-bold">{{ $mdm->employee->name }}</div>
                        <div class="text-muted small">{{ $mdm->employee->designation }}</div>
                        <div class="text-muted small">{{ $mdm->employee->client?->name }}</div>
                        <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i>{{ $mdm->employee->city }}, {{ $mdm->employee->region }}</div>
                        <div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $mdm->employee->phone ?? '—' }}</div>
                    </div>
                </div>
                @else
                <div class="text-center text-muted py-2 mb-3">
                    <i class="bi bi-person-x fs-2 d-block mb-1 opacity-50"></i>
                    Not linked to any employee
                </div>
                @endif

                <form method="POST" action="{{ route('mdm.link-employee', $mdm) }}">
                    @csrf
                    <label class="form-label small fw-semibold">{{ $mdm->employee ? 'Change' : 'Link' }} Employee</label>
                    <select class="form-select form-select-sm mb-2" name="employee_id">
                        <option value="">— Remove link —</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $mdm->employee_id == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} ({{ $emp->employee_code }}) — {{ $emp->designation }}
                        </option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-save me-1"></i>Save Link
                    </button>
                </form>
            </div>
        </div>

        {{-- Asset Link --}}
        @if($mdm->device)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><strong><i class="bi bi-link-45deg me-2 text-info"></i>Linked Asset</strong></div>
            <div class="card-body small">
                <div class="fw-bold">{{ $mdm->device->asset_tag }}</div>
                <div class="text-muted">{{ $mdm->device->model?->model_name }}</div>
                <div class="text-muted">Serial: {{ $mdm->device->serial_number }}</div>
                <div class="mt-2">
                    <a href="{{ route('devices.show', $mdm->device) }}" class="btn btn-outline-info btn-sm w-100">
                        <i class="bi bi-phone me-1"></i>View Asset Record
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="card border-0 shadow-sm mb-3 border-dashed">
            <div class="card-body text-center text-muted py-3 small">
                <i class="bi bi-link-slash fs-4 d-block mb-1 opacity-40"></i>
                Not matched to an asset record.<br>
                <span class="text-muted">Run <strong>Auto-Match by IMEI</strong> from the device list.</span>
            </div>
        </div>
        @endif

        {{-- Quick Stats --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header"><strong><i class="bi bi-activity me-2"></i>Quick Facts</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small">
                    <tbody>
                        <tr><td class="text-muted">Enrolled</td><td class="fw-semibold">{{ $mdm->enrollment_date?->format('d M Y') ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Days enrolled</td><td class="fw-semibold">{{ $mdm->enrollment_date ? $mdm->enrollment_date->diffInDays(now()).' days' : '—' }}</td></tr>
                        <tr><td class="text-muted">App updates</td><td class="fw-semibold {{ collect($apps)->where('outdated',true)->count() > 0 ? 'text-warning' : 'text-success' }}">{{ collect($apps)->where('outdated',true)->count() ?: 'None' }}</td></tr>
                        <tr><td class="text-muted">MDM Mode</td><td>@if($mdm->mdm_mode)<span class="badge bg-success-subtle text-success border">ON</span>@else<span class="badge bg-secondary-subtle text-secondary border">OFF</span>@endif</td></tr>
                        <tr><td class="text-muted">Kiosk Mode</td><td>@if($mdm->kiosk_mode)<span class="badge bg-warning-subtle text-warning border">ON</span>@else<span class="badge bg-secondary-subtle text-secondary border">OFF</span>@endif</td></tr>
                        <tr><td class="text-muted">Synced via import</td><td class="fw-semibold">{{ $mdm->synced_at?->format('d M Y H:i') ?? '—' }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
