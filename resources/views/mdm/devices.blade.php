@extends('layouts.main')
@section('title','MDM Devices')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mdm.index') }}">MDM</a></li>
    <li class="breadcrumb-item active">Devices</li>
@endsection

@section('content')
{{-- KPI Strip --}}
<div class="row g-3 mb-4">
    @php
    $strip = [
        ['Total', $stats['total'], '0d6efd'],
        ['Online', $stats['online'], '198754'],
        ['Offline', $stats['offline'], 'dc3545'],
        ['Linked to Employee', $stats['linked'], '6f42c1'],
    ];
    @endphp
    @foreach($strip as $s)
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-left:4px solid #{{ $s[2] }} !important">
            <div class="card-body py-2 px-3">
                <div class="fw-bold fs-4" style="color:#{{ $s[2] }}">{{ $s[0] === 'Online' ? number_format($s[1]) : number_format($s[1]) }}</div>
                <div class="text-muted small">{{ $s[0] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters + Actions --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="q" placeholder="MDM #, Serial, IMEI, Model…" value="{{ request('q') }}">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="on" {{ request('status')==='on'?'selected':'' }}>Online</option>
                    <option value="off" {{ request('status')==='off'?'selected':'' }}>Offline</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="group">
                    <option value="">All Groups</option>
                    @foreach($groups as $g)
                    <option value="{{ $g }}" {{ request('group')===$g?'selected':'' }}>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="model">
                    <option value="">All Models</option>
                    @foreach($modelList as $m)
                    <option value="{{ $m }}" {{ request('model')===$m?'selected':'' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <select class="form-select form-select-sm" name="linked">
                    <option value="">Linked?</option>
                    <option value="yes" {{ request('linked')==='yes'?'selected':'' }}>Linked</option>
                    <option value="no" {{ request('linked')==='no'?'selected':'' }}>Unlinked</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="{{ route('mdm.devices') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
        <strong><i class="bi bi-phone-fill me-2 text-primary"></i>MDM Portal Devices
            <span class="badge bg-secondary ms-2">{{ $devices->total() }}</span>
        </strong>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('mdm.auto-match') }}">@csrf
                <button class="btn btn-outline-info btn-sm"><i class="bi bi-link-45deg me-1"></i>Auto-Match by IMEI</button>
            </form>
            <a href="{{ route('mdm.import') }}" class="btn btn-success btn-sm"><i class="bi bi-upload me-1"></i>Import CSV</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 small align-middle">
                <thead class="table-light">
                    <tr>
                        <th>MDM #</th>
                        <th>Model / Serial</th>
                        <th>Employee</th>
                        <th>Group</th>
                        <th>Status</th>
                        <th>Permissions</th>
                        <th>MDM / Kiosk</th>
                        <th>Last Sync</th>
                        <th>Launcher</th>
                        <th>IP</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $d)
                    @php
                        $statusBadge = $d->device_status === 'on' ? 'success' : ($d->device_status === 'off' ? 'danger' : 'secondary');
                        $syncClass   = 'sync-age-' . $d->syncFreshnessClass();
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('mdm.show',$d) }}" class="fw-semibold text-decoration-none">{{ $d->mdm_number }}</a>
                            @if($d->hasOutdatedApps())
                            <span class="badge bg-warning-subtle text-warning border ms-1" title="Has outdated apps" style="font-size:.6rem">APP</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $d->model ?? '—' }}</div>
                            <div class="text-muted font-monospace" style="font-size:.72rem">{{ $d->serial_number ?? $d->imei ?? '—' }}</div>
                        </td>
                        <td>
                            @if($d->employee)
                                <div class="fw-semibold">{{ $d->employee->name }}</div>
                                <div class="text-muted" style="font-size:.72rem">{{ $d->employee->designation }}</div>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border" style="font-size:.7rem">Unlinked</span>
                            @endif
                        </td>
                        <td>{{ $d->mdm_group ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $statusBadge }}-subtle text-{{ $statusBadge }} border">
                                {{ strtoupper($d->device_status ?? 'unknown') }}
                            </span>
                        </td>
                        <td>
                            @if($d->isPermissionCompliant())
                                <i class="bi bi-check-circle-fill text-success" title="All permissions granted"></i>
                            @else
                                <i class="bi bi-exclamation-circle-fill text-danger" title="{{ $d->permission_status }}"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $d->mdm_mode ? 'bg-success-subtle text-success border' : 'bg-secondary-subtle text-secondary border' }} me-1">MDM</span>
                            <span class="badge {{ $d->kiosk_mode ? 'bg-warning-subtle text-warning border' : 'bg-secondary-subtle text-secondary border' }}">Kiosk</span>
                        </td>
                        <td class="{{ $syncClass }} font-monospace" style="font-size:.75rem">
                            {{ $d->syncAgeLabel() }}<br>
                            <span class="text-muted" style="font-size:.68rem">{{ $d->sync_time?->format('d M H:i') ?? '—' }}</span>
                        </td>
                        <td class="text-muted">{{ $d->launcher_version ?? '—' }}</td>
                        <td class="text-muted font-monospace" style="font-size:.72rem">{{ $d->ip_address ?? '—' }}</td>
                        <td>
                            <a href="{{ route('mdm.show',$d) }}" class="btn btn-outline-primary btn-sm py-0 px-2">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                            No MDM devices found. <a href="{{ route('mdm.import') }}">Import CSV</a> to get started.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($devices->hasPages())
    <div class="card-footer bg-white">{{ $devices->links() }}</div>
    @endif
</div>
@endsection
