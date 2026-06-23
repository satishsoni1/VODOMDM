@extends('client-portal.layout')
@section('title','My Devices')
@section('page-title','My Devices')

@section('content')

{{-- ── Stats Strip ──────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @php
        $totalAll = $devices->total();
        $filterSummary = request()->hasAny(['q','status','mdm_status']);
    @endphp
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                            placeholder="Search serial, IMEI, employee…">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Lifecycle Status</option>
                            @foreach($statusList as $s)
                            <option value="{{ $s }}" @selected(request('status')===$s)>{{ str_replace('_',' ',ucfirst($s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="mdm_status" class="form-select form-select-sm">
                            <option value="">All MDM Status</option>
                            <option value="online"  @selected(request('mdm_status')==='online')>Online</option>
                            <option value="offline" @selected(request('mdm_status')==='offline')>Offline</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-sm text-white" style="background:var(--gs-teal)">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                        @if($filterSummary)
                        <a href="{{ route('client.devices') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── Device Table ─────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
        <strong style="color:var(--gs-teal-dark)"><i class="bi bi-phone me-2"></i>Devices ({{ $devices->total() }})</strong>
    </div>
    <div class="card-body p-0">
        @if($devices->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-phone fs-1 d-block mb-2 opacity-25"></i>No devices found.
        </div>
        @else
        <div class="table-responsive">
            <table class="table cp-table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">Device / Model</th>
                        <th>Serial / IMEI</th>
                        <th>Assigned To</th>
                        <th>Lifecycle</th>
                        <th>MDM Status</th>
                        <th>Last Sync</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devices as $device)
                    @php $mdm = $device->mdmPortalDevice; @endphp
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold" style="color:var(--gs-teal-dark)">{{ $device->model?->name ?? '—' }}</div>
                            <div class="text-muted small">{{ $device->model?->brand?->name }}</div>
                        </td>
                        <td>
                            <div class="font-monospace small">{{ $device->serial_number }}</div>
                            @if($device->imei1)
                            <div class="font-monospace text-muted" style="font-size:.72rem">{{ $device->imei1 }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $device->currentEmployee?->name ?? '—' }}</div>
                            <div class="text-muted small">{{ $device->currentEmployee?->designation }}</div>
                        </td>
                        <td>
                            <span class="badge rounded-pill bg-light text-secondary border" style="font-size:.72rem">
                                {{ str_replace('_',' ', ucfirst($device->lifecycle_status)) }}
                            </span>
                        </td>
                        <td>
                            @if($mdm)
                                <span class="badge rounded-pill {{ $mdm->isOnline() ? 'badge-on' : 'badge-off' }}">
                                    <i class="bi bi-circle-fill me-1" style="font-size:.5rem"></i>
                                    {{ $mdm->isOnline() ? 'Online' : 'Offline' }}
                                </span>
                                @if(!$mdm->isPermissionCompliant())
                                <span class="badge bg-warning-subtle text-warning border ms-1" title="{{ $mdm->permission_status }}">
                                    <i class="bi bi-shield-exclamation"></i>
                                </span>
                                @endif
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="small">
                            @if($mdm && $mdm->sync_time)
                                <span class="badge bg-{{ $mdm->syncFreshnessClass() }}-subtle text-{{ $mdm->syncFreshnessClass() }} border">
                                    {{ $mdm->syncAgeLabel() }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="pe-3">
                            <a href="{{ route('client.devices.show', $device) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @if($devices->hasPages())
    <div class="card-footer bg-white">{{ $devices->links() }}</div>
    @endif
</div>
@endsection
