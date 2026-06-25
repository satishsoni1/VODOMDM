@extends('layouts.main')
@section('title','MDM Devices')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mdm.index') }}">MDM</a></li>
    <li class="breadcrumb-item active">Devices</li>
@endsection

@section('content')

{{-- ── Stats ────────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 text-center">
            <div class="fs-3 fw-bold">{{ number_format($stats['total']) }}</div>
            <div class="text-muted small">Total</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ request()->fullUrlWithQuery(['status'=>'on','page'=>null]) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm p-3 text-center h-100">
                <div class="fs-3 fw-bold text-success">{{ number_format($stats['online']) }}</div>
                <div class="text-muted small">Online</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ request()->fullUrlWithQuery(['linked'=>'yes','page'=>null]) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm p-3 text-center h-100">
                <div class="fs-3 fw-bold" style="color:var(--gs-teal)">{{ number_format($stats['linked']) }}</div>
                <div class="text-muted small">Linked</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ request()->fullUrlWithQuery(['gps'=>'yes','page'=>null]) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm p-3 text-center h-100">
                <div class="fs-3 fw-bold text-info">{{ number_format($stats['gps']) }}</div>
                <div class="text-muted small">GPS Tracked</div>
            </div>
        </a>
    </div>
</div>

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
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('mdm.devices') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── Device Table ─────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between py-2">
        <div class="small text-muted">
            @if($devices->total())
            Showing {{ $devices->firstItem() }}–{{ $devices->lastItem() }} of {{ $devices->total() }} devices
            @else
            0 devices found
            @endif
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('mdm.map') }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-geo-alt-fill me-1"></i>Map View
            </a>
            <a href="{{ route('mdm.sync') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-database-fill-up me-1"></i>Sync
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 small align-middle">
            <thead class="table-light">
                <tr>
                    <th>Device #</th>
                    <th>Model</th>
                    <th>IMEI / Serial</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">GPS</th>
                    <th>Employee</th>
                    <th>Group</th>
                    <th>Android</th>
                    <th>Last Sync</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $d)
                <tr>
                    <td>
                        <a href="{{ route('mdm.show', $d) }}" class="fw-semibold font-monospace text-decoration-none text-dark">
                            {{ $d->pg_number }}
                        </a>
                    </td>
                    <td>{{ $d->model ?? '—' }}</td>
                    <td class="text-muted font-monospace" style="font-size:.7rem">
                        @if($d->imei)<div>{{ $d->imei }}</div>@endif
                        @if($d->serial_number)<div>{{ $d->serial_number }}</div>@endif
                        @if(!$d->imei && !$d->serial_number) — @endif
                    </td>
                    <td class="text-center">
                        @if($d->isOnline())
                            <span class="badge bg-success-subtle text-success border" style="font-size:.68rem">
                                <i class="bi bi-wifi"></i> Online
                            </span>
                        @elseif($d->device_status === 'off')
                            <span class="badge bg-danger-subtle text-danger border" style="font-size:.68rem">
                                <i class="bi bi-wifi-off"></i> Offline
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size:.68rem">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @php $hasGps = $d->latitude || $d->locationLatest?->latitude; @endphp
                        @if($hasGps)
                            @php $lat = $d->locationLatest?->latitude ?? $d->latitude; $lng = $d->locationLatest?->longitude ?? $d->longitude; @endphp
                            <span class="text-success" title="{{ $lat }},{{ $lng }}">
                                <i class="bi bi-geo-alt-fill"></i>
                            </span>
                        @else
                            <span class="text-muted opacity-30"><i class="bi bi-geo-alt"></i></span>
                        @endif
                    </td>
                    <td>
                        @if($d->employee)
                            <div class="fw-semibold">{{ $d->employee->name }}</div>
                            <div class="text-muted" style="font-size:.7rem">
                                {{ $d->employee->employee_code }}
                                @if($d->employee->client) &middot; {{ $d->employee->client->name }} @endif
                            </div>
                        @else
                            <span class="text-muted fst-italic">Not linked</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $d->mdm_group ?? '—' }}</td>
                    <td class="text-muted small">{{ $d->android_version ?? '—' }}</td>
                    <td class="text-{{ $d->syncFreshnessClass() }} small text-nowrap">{{ $d->syncAgeLabel() }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('mdm.show', $d) }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:.72rem">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('mdm.link', ['q'=>$d->pg_number]) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:.72rem" title="Link Employee">
                                <i class="bi bi-link-45deg"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                        <i class="bi bi-phone-fill fs-2 d-block mb-2 opacity-20"></i>
                        No devices found.
                        @if(request()->hasAny(['q','status','group','model','linked','gps']))
                            <a href="{{ route('mdm.devices') }}">Clear filters</a>
                        @else
                            <a href="{{ route('mdm.sync') }}">Run a sync</a> to import devices.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($devices->hasPages())
    <div class="card-footer bg-white">{{ $devices->links() }}</div>
    @endif
</div>

@endsection
