@extends('layouts.main')
@section('title','MDM Analytics')
@section('breadcrumb')
    <li class="breadcrumb-item active">MDM Analytics</li>
@endsection

@push('styles')
<style>
    .kpi-card { border-left: 4px solid; border-radius: 8px; }
    .kpi-icon { width: 48px; height: 48px; border-radius: 12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0; }
    .status-dot { width: 10px; height: 10px; border-radius: 50%; display:inline-block; flex-shrink:0; }
    .freshness-bar { height: 8px; border-radius: 4px; }
    .chart-container { position: relative; height: 220px; }
    .attention-row:hover { background: #fff8f0; }
    .sync-age-fresh   { color: #198754; }
    .sync-age-warning { color: #fd7e14; }
    .sync-age-danger  { color: #dc3545; }
</style>
@endpush

@section('content')

{{-- ── TOP KPI ROW ─────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @php
    $kpis = [
        ['Total Devices',    $total,        'bi-phone-fill',         '0d6efd', 'blue'],
        ['Online Now',       $online,       'bi-wifi',               '198754', 'green'],
        ['Offline',          $offline,      'bi-wifi-off',           'dc3545', 'red'],
        ['Permission OK',    $compliant,    'bi-shield-check',       '20c997', 'green'],
        ['MDM Mode',         $mdmModeOn,    'bi-lock-fill',          '6f42c1', 'purple'],
        ['Kiosk Mode',       $kioskModeOn,  'bi-display',            'fd7e14', 'orange'],
        ['Linked to Emp.',   $linked,       'bi-person-check-fill',  '0dcaf0', 'blue'],
        ['Unlinked',         $unlinked,     'bi-person-x',           'adb5bd', 'secondary'],
    ];
    @endphp
    @foreach($kpis as $k)
    <div class="col-xl-3 col-md-4 col-6">
        <div class="card border-0 shadow-sm kpi-card" style="border-color:#{{ $k[3] }} !important">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="kpi-icon" style="background:#{{ $k[3] }}18">
                    <i class="bi {{ $k[2] }}" style="color:#{{ $k[3] }}"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 lh-1">{{ number_format($k[1]) }}</div>
                    <div class="text-muted small">{{ $k[0] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-4">

    {{-- ── DEVICE STATUS DOUGHNUT ─────────────────────────────────────────── --}}
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header"><strong><i class="bi bi-circle-half me-2 text-primary"></i>Device Status</strong></div>
            <div class="card-body d-flex flex-column align-items-center">
                <div class="chart-container w-100"><canvas id="chartStatus"></canvas></div>
                <div class="d-flex gap-3 mt-2 small">
                    <span><span class="status-dot me-1" style="background:#198754"></span>Online {{ $online }}</span>
                    <span><span class="status-dot me-1" style="background:#dc3545"></span>Offline {{ $offline }}</span>
                    <span><span class="status-dot me-1" style="background:#adb5bd"></span>Unknown {{ $total - $online - $offline }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SYNC FRESHNESS ──────────────────────────────────────────────────── --}}
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header"><strong><i class="bi bi-clock-history me-2 text-warning"></i>Last Sync Freshness</strong></div>
            <div class="card-body">
                @php
                $freshnessItems = [
                    ['Within 1 hour',  $syncFreshness['fresh'], '198754'],
                    ['1h – 24h',       $syncFreshness['today'], 'fd7e14'],
                    ['1 – 7 days',     $syncFreshness['week'],  'ffc107'],
                    ['Stale / Never',  $syncFreshness['stale'], 'dc3545'],
                ];
                $maxF = max(array_column($freshnessItems, 1)) ?: 1;
                @endphp
                @foreach($freshnessItems as $f)
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ $f[0] }}</span>
                        <strong style="color:#{{ $f[2] }}">{{ $f[1] }}</strong>
                    </div>
                    <div class="bg-light rounded" style="height:8px">
                        <div class="rounded freshness-bar" style="width:{{ $total ? round($f[1]/$total*100) : 0 }}%; background:#{{ $f[2] }}; height:8px"></div>
                    </div>
                </div>
                @endforeach
                <div class="text-center mt-2">
                    <div class="chart-container" style="height:130px"><canvas id="chartFreshness"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── ANDROID VERSION ─────────────────────────────────────────────────── --}}
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header"><strong><i class="bi bi-android2 me-2 text-success"></i>Android Version</strong></div>
            <div class="card-body">
                <div class="chart-container"><canvas id="chartAndroid"></canvas></div>
            </div>
        </div>
    </div>

    {{-- ── MODEL DISTRIBUTION ──────────────────────────────────────────────── --}}
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header"><strong><i class="bi bi-phone me-2 text-info"></i>Device Models</strong></div>
            <div class="card-body">
                <div class="chart-container"><canvas id="chartModels"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">

    {{-- ── DESIGNATION-WISE STATS ──────────────────────────────────────────── --}}
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <strong><i class="bi bi-person-badge me-2 text-primary"></i>Designation-wise MDM Status</strong>
                <a href="{{ route('mdm.employees') }}" class="btn btn-outline-primary btn-sm">View All</a>
            </div>
            <div class="card-body p-0">
                @if($designationStats->isEmpty())
                <div class="text-center text-muted py-4">No employee-device links yet. <a href="{{ route('mdm.import') }}">Import MDM data</a> first.</div>
                @else
                <table class="table table-hover mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Designation</th>
                            <th class="text-center">Devices</th>
                            <th class="text-center">Online</th>
                            <th class="text-center">Compliant</th>
                            <th class="text-center">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($designationStats as $ds)
                        @php $score = $ds->total ? round(($ds->compliant / $ds->total) * 100) : 0; @endphp
                        <tr>
                            <td class="fw-semibold">{{ $ds->designation }}</td>
                            <td class="text-center">{{ $ds->total }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $ds->online > 0 ? 'success' : 'secondary' }}-subtle text-{{ $ds->online > 0 ? 'success' : 'secondary' }} border">{{ $ds->online }}</span>
                            </td>
                            <td class="text-center">{{ $ds->compliant }}</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1 bg-light rounded" style="height:6px">
                                        <div class="rounded" style="width:{{ $score }}%; height:6px; background:{{ $score >= 80 ? '#198754' : ($score >= 50 ? '#fd7e14' : '#dc3545') }}"></div>
                                    </div>
                                    <span class="text-muted" style="width:32px; font-size:.75rem">{{ $score }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>

    {{-- ── ENROLLMENT TIMELINE ────────────────────────────────────────────── --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header"><strong><i class="bi bi-calendar-check me-2 text-success"></i>Enrollment Timeline</strong></div>
            <div class="card-body">
                <div class="chart-container" style="height:240px"><canvas id="chartEnrollment"></canvas></div>
            </div>
        </div>
    </div>

    {{-- ── GROUP DISTRIBUTION ──────────────────────────────────────────────── --}}
    <div class="col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header"><strong><i class="bi bi-diagram-3 me-2 text-purple"></i>MDM Groups</strong></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light"><tr><th>Group</th><th class="text-end">Devices</th></tr></thead>
                    <tbody>
                        @forelse($groups as $grp => $cnt)
                        <tr>
                            <td>{{ $grp ?: '—' }}</td>
                            <td class="text-end"><span class="badge bg-primary-subtle text-primary border">{{ $cnt }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">

    {{-- ── DEVICES NEEDING ATTENTION ──────────────────────────────────────── --}}
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <strong><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Devices Needing Attention</strong>
                <a href="{{ route('mdm.devices') }}?status=off" class="btn btn-outline-danger btn-sm">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light">
                        <tr><th>MDM #</th><th>Model</th><th>Employee</th><th>Status</th><th>Last Sync</th><th>Issue</th></tr>
                    </thead>
                    <tbody>
                        @forelse($attention as $d)
                        @php
                            $issues = [];
                            if ($d->device_status !== 'on') $issues[] = 'Offline';
                            if (!$d->isPermissionCompliant()) $issues[] = 'Perm.';
                            if (!$d->sync_time || $d->sync_time->lt(now()->subDay())) $issues[] = 'Stale';
                        @endphp
                        <tr class="attention-row">
                            <td><a href="{{ route('mdm.show',$d) }}" class="fw-semibold text-decoration-none">{{ $d->mdm_number }}</a></td>
                            <td>{{ $d->model ?? '—' }}</td>
                            <td>{{ $d->employee?->name ?? '<span class="text-muted">Unlinked</span>' }}</td>
                            <td>
                                @php $badge = $d->device_status === 'on' ? 'success' : 'danger'; @endphp
                                <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }} border">{{ strtoupper($d->device_status ?? 'unknown') }}</span>
                            </td>
                            <td class="sync-age-{{ $d->syncFreshnessClass() }}">{{ $d->syncAgeLabel() }}</td>
                            <td>
                                @foreach($issues as $issue)
                                <span class="badge bg-danger-subtle text-danger border me-1" style="font-size:.65rem">{{ $issue }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No issues detected.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── RECENT SYNCS ────────────────────────────────────────────────────── --}}
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header"><strong><i class="bi bi-activity me-2 text-success"></i>Recent Sync Activity</strong></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light"><tr><th>MDM #</th><th>Employee</th><th>Last Sync</th><th>IP</th></tr></thead>
                    <tbody>
                        @forelse($recentSyncs as $d)
                        <tr>
                            <td><a href="{{ route('mdm.show',$d) }}" class="text-decoration-none fw-semibold">{{ $d->mdm_number }}</a></td>
                            <td>{{ $d->employee?->name ?? '—' }}</td>
                            <td class="text-success">{{ $d->syncAgeLabel() }}</td>
                            <td class="text-muted font-monospace" style="font-size:.72rem">{{ $d->ip_address ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No sync data. <a href="{{ route('mdm.import') }}">Import CSV</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const PALETTE = ['#0d6efd','#198754','#dc3545','#fd7e14','#6f42c1','#0dcaf0','#ffc107','#20c997','#e83e8c','#adb5bd'];

// Status doughnut
new Chart(document.getElementById('chartStatus'), {
    type: 'doughnut',
    data: {
        labels: ['Online','Offline','Unknown'],
        datasets: [{ data: [{{ $online }}, {{ $offline }}, {{ $total - $online - $offline }}],
            backgroundColor: ['#198754','#dc3545','#adb5bd'], borderWidth: 0 }]
    },
    options: { cutout:'70%', plugins:{ legend:{ display:false } }, responsive:true, maintainAspectRatio:false }
});

// Freshness doughnut
new Chart(document.getElementById('chartFreshness'), {
    type: 'doughnut',
    data: {
        labels: ['<1h','1-24h','1-7d','Stale'],
        datasets: [{ data: [{{ $syncFreshness['fresh'] }}, {{ $syncFreshness['today'] }}, {{ $syncFreshness['week'] }}, {{ $syncFreshness['stale'] }}],
            backgroundColor: ['#198754','#fd7e14','#ffc107','#dc3545'], borderWidth: 0 }]
    },
    options: { cutout:'60%', plugins:{ legend:{ position:'bottom', labels:{ font:{size:10} } } }, responsive:true, maintainAspectRatio:false }
});

// Android version bar
new Chart(document.getElementById('chartAndroid'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($androidVersions->keys()) !!},
        datasets: [{ label:'Devices', data: {!! json_encode($androidVersions->values()) !!},
            backgroundColor: PALETTE, borderRadius: 4 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend:{ display:false } },
        scales: { y:{ beginAtZero:true, ticks:{ stepSize:1 } }, x:{ grid:{ display:false } } }
    }
});

// Model bar
new Chart(document.getElementById('chartModels'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($models->keys()) !!},
        datasets: [{ label:'Devices', data: {!! json_encode($models->values()) !!},
            backgroundColor: PALETTE.slice(2), borderRadius: 4 }]
    },
    options: {
        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
        plugins: { legend:{ display:false } },
        scales: { x:{ beginAtZero:true, ticks:{ stepSize:1 } }, y:{ grid:{ display:false } } }
    }
});

// Enrollment timeline
new Chart(document.getElementById('chartEnrollment'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($enrollmentByMonth->keys()) !!},
        datasets: [{ label:'Enrolled', data: {!! json_encode($enrollmentByMonth->values()) !!},
            backgroundColor: '#0d6efd55', borderColor:'#0d6efd', borderWidth:2, borderRadius:4 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend:{ display:false } },
        scales: { y:{ beginAtZero:true, ticks:{ stepSize:1 } } }
    }
});
</script>
@endpush
