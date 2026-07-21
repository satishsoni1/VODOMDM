@extends('client-portal.layout')
@section('title','MDM Devices')
@section('page-title','MDM Devices')

@section('content')

@if(empty($configs))
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-gear fs-1 d-block mb-2 opacity-25"></i>
        No MDM configuration has been assigned to your account yet.<br>
        Please contact your account manager.
    </div>
</div>
@else

{{-- ── Filters ──────────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Search device #, IMEI, serial, model…"
                       value="{{ request('q') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="on"  {{ request('status')=='on'  ? 'selected':'' }}>Online</option>
                    <option value="off" {{ request('status')=='off' ? 'selected':'' }}>Offline</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="group" class="form-select form-select-sm">
                    <option value="">All Groups</option>
                    @foreach($groups as $g)
                    <option value="{{ $g }}" {{ request('group')===$g ? 'selected':'' }}>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="model" class="form-select form-select-sm">
                    <option value="">All Models</option>
                    @foreach($modelList as $m)
                    <option value="{{ $m }}" {{ request('model')===$m ? 'selected':'' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="configuration" class="form-select form-select-sm">
                    <option value="">All Configurations</option>
                    @foreach($configs as $c)
                    <option value="{{ $c }}" {{ request('configuration')===$c ? 'selected':'' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <select name="linked" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="yes" {{ request('linked')==='yes' ? 'selected':'' }}>Linked</option>
                    <option value="no"  {{ request('linked')==='no'  ? 'selected':'' }}>Unlinked</option>
                </select>
            </div>
            <div class="col-md-1">
                <select name="gps" class="form-select form-select-sm">
                    <option value="">Any GPS</option>
                    <option value="yes" {{ request('gps')==='yes' ? 'selected':'' }}>Has GPS</option>
                    <option value="no"  {{ request('gps')==='no'  ? 'selected':'' }}>No GPS</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm text-white flex-grow-1" style="background:var(--gs-teal)">
                    <i class="bi bi-search"></i>
                </button>
                @if(request()->hasAny(['q','status','group','model','configuration','linked','gps']))
                <a href="{{ route('client.mdm-devices') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Device Table ─────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
        <strong style="color:var(--gs-teal-dark)"><i class="bi bi-phone-fill me-2"></i>Devices ({{ $devices->total() }})</strong>
        <div class="d-flex gap-2">
            <a href="{{ route('client.mdm-devices.export', request()->query()) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-file-earmark-excel me-1"></i>Export
            </a>
            <a href="{{ route('client.mdm-map') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-geo-alt-fill me-1"></i>Map View
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        @if($devices->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-phone-fill fs-1 d-block mb-2 opacity-25"></i>No devices found.
        </div>
        @else
        <div class="table-responsive">
            <table class="table cp-table table-hover mb-0 small align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">Device #</th>
                        <th>IMEI / Serial</th>
                        <th>Model</th>
                        <th>Configuration</th>
                        <th class="text-center">Status</th>
                        <th>Employee</th>
                        <th>Last Sync</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devices as $d)
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('client.mdm-devices.show', $d) }}" class="fw-semibold font-monospace text-decoration-none" style="color:var(--gs-teal-dark)">
                                {{ $d->pg_number }}
                            </a>
                        </td>
                        <td>
                            <div class="font-monospace small">{{ $d->imei ?: '—' }}</div>
                            @if($d->serial_number)
                            <div class="font-monospace text-muted" style="font-size:.7rem">{{ $d->serial_number }}</div>
                            @endif
                        </td>
                        <td>{{ $d->model ?? '—' }}</td>
                        <td class="text-muted small">{{ $d->configuration ?? '—' }}</td>
                        <td class="text-center">
                            @if($d->isOnline())
                                <span class="badge rounded-pill badge-on"><i class="bi bi-wifi"></i> Online</span>
                            @else
                                <span class="badge rounded-pill badge-off"><i class="bi bi-wifi-off"></i> Offline</span>
                            @endif
                        </td>
                        <td>
                            @php $emp = $d->resolvedEmployee(); @endphp
                            @if($emp)
                                <div class="fw-semibold">{{ $emp->name }}</div>
                                <div class="text-muted" style="font-size:.7rem">{{ $emp->employee_code }}</div>
                            @else
                                <span class="text-muted fst-italic">Not linked</span>
                            @endif
                        </td>
                        <td class="small">
                            @if($d->sync_time)
                                <span class="badge bg-{{ $d->syncFreshnessClass() }}-subtle text-{{ $d->syncFreshnessClass() }} border">
                                    {{ $d->syncAgeLabel() }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="pe-3">
                            <a href="{{ route('client.mdm-devices.show', $d) }}" class="btn btn-sm btn-outline-secondary">
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
@endif
@endsection
