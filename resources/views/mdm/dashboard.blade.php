@extends('layouts.main')
@section('title','MDM Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">MDM Dashboard</li>
@endsection

@push('styles')
<style>
    .kpi { border-left: 4px solid; border-radius: 8px; }
    .kpi-blue   { border-color: #0d6efd; }
    .kpi-green  { border-color: #198754; }
    .kpi-red    { border-color: #dc3545; }
    .kpi-teal   { border-color: var(--gs-teal); }
    .kpi-orange { border-color: var(--gs-orange); }
    .kpi-purple { border-color: #6f42c1; }
    .bar-row { display:flex; align-items:center; gap:.5rem; margin-bottom:.45rem; font-size:.82rem; }
    .bar-bg  { flex:1; height:8px; background:#e9ecef; border-radius:4px; overflow:hidden; }
    .bar-fill{ height:100%; border-radius:4px; }
    .attn-badge { font-size:.68rem; padding:2px 6px; }
</style>
@endpush

@section('content')

{{-- ── KPI Row ─────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card kpi kpi-blue border-0 shadow-sm h-100 p-3">
            <div class="text-muted small mb-1"><i class="bi bi-phone me-1"></i>Total Devices</div>
            <div class="fs-2 fw-bold">{{ number_format($total) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card kpi kpi-green border-0 shadow-sm h-100 p-3">
            <div class="text-muted small mb-1"><i class="bi bi-wifi me-1"></i>Online</div>
            <div class="fs-2 fw-bold text-success">{{ number_format($online) }}</div>
            @if($total > 0)
            <div class="small text-muted">{{ round($online/$total*100) }}% of fleet</div>
            @endif
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card kpi kpi-red border-0 shadow-sm h-100 p-3">
            <div class="text-muted small mb-1"><i class="bi bi-wifi-off me-1"></i>Offline</div>
            <div class="fs-2 fw-bold text-danger">{{ number_format($offline) }}</div>
            @if($total > 0)
            <div class="small text-muted">{{ round($offline/$total*100) }}% of fleet</div>
            @endif
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card kpi kpi-teal border-0 shadow-sm h-100 p-3">
            <div class="text-muted small mb-1"><i class="bi bi-person-check me-1"></i>Linked</div>
            <div class="fs-2 fw-bold" style="color:var(--gs-teal)">{{ number_format($linked) }}</div>
            @if($total > 0)
            <div class="small text-muted">{{ round($linked/$total*100) }}% assigned</div>
            @endif
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card kpi kpi-orange border-0 shadow-sm h-100 p-3">
            <div class="text-muted small mb-1"><i class="bi bi-person-x me-1"></i>Unlinked</div>
            <div class="fs-2 fw-bold" style="color:var(--gs-orange)">{{ number_format($unlinked) }}</div>
            <div class="small text-muted">need assignment</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card kpi kpi-purple border-0 shadow-sm h-100 p-3">
            <div class="text-muted small mb-1"><i class="bi bi-geo-alt-fill me-1"></i>GPS Tracked</div>
            <div class="fs-2 fw-bold text-purple" style="color:#6f42c1">{{ number_format($withGps) }}</div>
            @if($total > 0)
            <div class="small text-muted">{{ round($withGps/$total*100) }}% with fix</div>
            @endif
        </div>
    </div>
</div>

{{-- ── Last sync banner ─────────────────────────────────────────────────────── --}}
@if($lastSync)
<div class="alert border-0 mb-4 py-2 d-flex align-items-center gap-3
            {{ $lastSync->status === 'completed' ? 'alert-success' : 'alert-danger' }}">
    <i class="bi bi-{{ $lastSync->status === 'completed' ? 'check-circle-fill' : 'x-circle-fill' }} fs-5"></i>
    <div class="flex-grow-1 small">
        <strong>Last sync:</strong> {{ $lastSync->created_at->diffForHumans() }}
        &mdash; {{ $lastSync->imported }} new &middot; {{ $lastSync->updated }} updated &middot;
        {{ $lastSync->auto_matched }} matched
    </div>
    <a href="{{ route('mdm.sync') }}" class="btn btn-sm btn-success ms-auto">
        <i class="bi bi-database-fill-up me-1"></i>Sync Now
    </a>
</div>
@else
<div class="alert alert-warning border-0 mb-4 py-2 d-flex align-items-center gap-3">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <span class="small flex-grow-1">No sync has been run yet. Connect to the MDM server to populate device data.</span>
    <a href="{{ route('mdm.sync') }}" class="btn btn-sm btn-warning ms-auto">
        <i class="bi bi-database-fill-up me-1"></i>Sync Now
    </a>
</div>
@endif

{{-- ── Main content row ─────────────────────────────────────────────────────── --}}
<div class="row g-4">

    {{-- Attention Devices ── --}}
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <strong><i class="bi bi-exclamation-circle text-warning me-2"></i>Devices Needing Attention</strong>
                <a href="{{ route('mdm.devices', ['status'=>'off']) }}" class="btn btn-sm btn-outline-secondary">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Device</th>
                            <th>Model</th>
                            <th>Status</th>
                            <th>GPS</th>
                            <th>Employee</th>
                            <th>Sync</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attention as $d)
                        <tr>
                            <td class="font-monospace fw-semibold">
                                <a href="{{ route('mdm.show', $d) }}" class="text-decoration-none">
                                    {{ $d->mdm_number }}
                                </a>
                            </td>
                            <td class="text-muted">{{ $d->model ?? '—' }}</td>
                            <td>
                                @if($d->isOnline())
                                    <span class="badge bg-success-subtle text-success border attn-badge">Online</span>
                                @elseif($d->device_status === 'off')
                                    <span class="badge bg-danger-subtle text-danger border attn-badge">Offline</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border attn-badge">Unknown</span>
                                @endif
                            </td>
                            <td>
                                @if($d->latitude)
                                    <span class="badge bg-success-subtle text-success border attn-badge">
                                        <i class="bi bi-geo-alt-fill"></i> Yes
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border attn-badge">
                                        <i class="bi bi-geo-alt"></i> No
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($d->employee)
                                    <span class="fw-semibold">{{ $d->employee->name }}</span>
                                    <div class="text-muted" style="font-size:.7rem">{{ $d->employee->client?->name }}</div>
                                @else
                                    <span class="text-muted fst-italic">Not linked</span>
                                @endif
                            </td>
                            <td class="text-{{ $d->syncFreshnessClass() }} small">{{ $d->syncAgeLabel() }}</td>
                            <td>
                                <a href="{{ route('mdm.show', $d) }}" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size:.72rem">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-check-circle text-success fs-4 d-block mb-1"></i>
                                All devices are in good shape!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right column ── --}}
    <div class="col-xl-4">

        {{-- Models Distribution --}}
        @if($models->count())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><strong><i class="bi bi-phone me-2 text-primary"></i>Top Models</strong></div>
            <div class="card-body pb-2">
                @php $maxM = $models->max(); @endphp
                @foreach($models->take(8) as $model => $cnt)
                <div class="bar-row">
                    <span class="text-truncate" style="min-width:0;max-width:140px;flex:0 0 140px" title="{{ $model }}">{{ $model }}</span>
                    <div class="bar-bg">
                        <div class="bar-fill bg-primary" style="width:{{ round($cnt/$maxM*100) }}%"></div>
                    </div>
                    <span class="fw-bold" style="min-width:30px;text-align:right">{{ $cnt }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Groups Distribution --}}
        @if($groups->count())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><strong><i class="bi bi-collection me-2 text-success"></i>Groups</strong></div>
            <div class="card-body pb-2">
                @php $maxG = $groups->max(); @endphp
                @foreach($groups->take(6) as $group => $cnt)
                <div class="bar-row">
                    <span class="text-truncate" style="min-width:0;max-width:140px;flex:0 0 140px" title="{{ $group }}">{{ $group }}</span>
                    <div class="bar-bg">
                        <div class="bar-fill bg-success" style="width:{{ round($cnt/$maxG*100) }}%"></div>
                    </div>
                    <span class="fw-bold" style="min-width:30px;text-align:right">{{ $cnt }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Android Versions --}}
        @if($androidVersions->count())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><strong><i class="bi bi-android2 me-2 text-success"></i>Android Versions</strong></div>
            <div class="card-body pb-2">
                @php $maxA = $androidVersions->max(); @endphp
                @foreach($androidVersions->take(6) as $ver => $cnt)
                <div class="bar-row">
                    <span style="min-width:60px;flex:0 0 60px">Android {{ $ver }}</span>
                    <div class="bar-bg">
                        <div class="bar-fill" style="width:{{ round($cnt/$maxA*100) }}%;background:#3ddc84"></div>
                    </div>
                    <span class="fw-bold" style="min-width:30px;text-align:right">{{ $cnt }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

{{-- ── Designation Coverage ────────────────────────────────────────────────── --}}
@if($designationStats->count())
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <strong><i class="bi bi-people me-2 text-info"></i>Employee Designation Coverage</strong>
        <a href="{{ route('mdm.link', ['filter'=>'unlinked']) }}" class="btn btn-sm btn-outline-secondary">
            Link Devices <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 small align-middle">
            <thead class="table-light">
                <tr>
                    <th>Designation</th>
                    <th class="text-center">Total Employees</th>
                    <th class="text-center">Has MDM Device</th>
                    <th>Coverage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($designationStats as $row)
                @php $pct = $row->emp_total > 0 ? round($row->device_count / $row->emp_total * 100) : 0; @endphp
                <tr>
                    <td class="fw-semibold">{{ $row->designation }}</td>
                    <td class="text-center">{{ $row->emp_total }}</td>
                    <td class="text-center">{{ $row->device_count }}</td>
                    <td style="min-width:120px">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bar-bg flex-grow-1">
                                <div class="bar-fill bg-{{ $pct >= 75 ? 'success' : ($pct >= 40 ? 'warning' : 'danger') }}"
                                     style="width:{{ $pct }}%"></div>
                            </div>
                            <span class="small fw-bold" style="min-width:35px">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
