@extends('client-portal.layout')
@section('title','Dashboard')
@section('page-title','Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- ── KPI Row ─────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon kpi-teal"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="kpi-val">{{ $totalEmployees }}</div>
                <div class="kpi-label">Field Staff</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon kpi-teal"><i class="bi bi-phone-fill"></i></div>
            <div>
                <div class="kpi-val">{{ $totalDevices }}</div>
                <div class="kpi-label">Total Devices</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon kpi-green"><i class="bi bi-wifi"></i></div>
            <div>
                <div class="kpi-val text-success">{{ $mdmOnline }}</div>
                <div class="kpi-label">MDM Online</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon kpi-orange"><i class="bi bi-ticket-perforated-fill"></i></div>
            <div>
                <div class="kpi-val" style="color:var(--gs-orange)">{{ $openTickets }}</div>
                <div class="kpi-label">Open Tickets</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- ── MDM Status Panel ─── --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-wifi me-2"></i>MDM Device Status</strong>
            </div>
            <div class="card-body">
                @if($mdmTotal > 0)
                <div class="d-flex justify-content-center mb-3" style="height:160px">
                    <canvas id="mdmDoughnut"></canvas>
                </div>
                <div class="row g-2 text-center small mt-2">
                    <div class="col-4">
                        <div class="fw-bold text-success fs-5">{{ $mdmOnline }}</div>
                        <div class="text-muted">Online</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-danger fs-5">{{ $mdmOffline }}</div>
                        <div class="text-muted">Offline</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5" style="color:var(--gs-teal)">{{ $mdmTotal }}</div>
                        <div class="text-muted">Enrolled</div>
                    </div>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-phone-vibrate fs-1 d-block mb-2 opacity-25"></i>
                    No MDM-enrolled devices yet.
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Sync Freshness ─── --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-clock-history me-2"></i>Last Sync Age</strong>
            </div>
            <div class="card-body">
                @php
                    $sfTotal = max(1, $mdmFresh + $mdmToday + $mdmStale);
                    $bars = [
                        ['label'=>'< 1 hour',  'val'=>$mdmFresh, 'color'=>'#1a8a7c'],
                        ['label'=>'1–24 hours','val'=>$mdmToday, 'color'=>'#f07030'],
                        ['label'=>'> 1 day',   'val'=>$mdmStale, 'color'=>'#dc3545'],
                    ];
                @endphp
                @foreach($bars as $b)
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold">{{ $b['label'] }}</span>
                        <span class="text-muted">{{ $b['val'] }} device{{ $b['val']!=1?'s':'' }}</span>
                    </div>
                    <div class="progress" style="height:10px;border-radius:6px">
                        <div class="progress-bar" style="width:{{ round($b['val']/$sfTotal*100) }}%;background:{{ $b['color'] }};border-radius:6px"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Device Status ─── --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-layers me-2"></i>Device Lifecycle</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small">
                    <tbody>
                        @forelse($lifecycleStats as $status => $cnt)
                        <tr>
                            <td class="ps-3">
                                <span class="badge rounded-pill bg-light text-secondary border" style="font-size:.72rem">
                                    {{ str_replace('_',' ', ucfirst($status)) }}
                                </span>
                            </td>
                            <td class="text-end pe-3 fw-semibold" style="color:var(--gs-teal-dark)">{{ $cnt }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">No devices assigned</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Attention Needed ─── --}}
    @if($attentionDevices->isNotEmpty())
    <div class="col-12">
        <div class="card border-0 shadow-sm border-start border-danger border-3">
            <div class="card-header d-flex align-items-center gap-2" style="background:#fce8e8">
                <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                <strong class="text-danger">Devices Needing Attention</strong>
                <span class="badge bg-danger ms-auto">{{ $attentionDevices->count() }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table cp-table table-hover mb-0 small align-middle">
                    <thead>
                        <tr>
                            <th class="ps-3">MDM #</th>
                            <th>Employee</th>
                            <th>Model</th>
                            <th>MDM Status</th>
                            <th>Permission</th>
                            <th>Last Sync</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attentionDevices as $m)
                        <tr>
                            <td class="ps-3 fw-semibold font-monospace">{{ $m->mdm_number }}</td>
                            <td>{{ $m->employee?->name ?? '—' }}</td>
                            <td>{{ $m->model ?? '—' }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $m->isOnline() ? 'badge-on' : 'badge-off' }}">
                                    <i class="bi bi-circle-fill me-1" style="font-size:.5rem"></i>
                                    {{ $m->isOnline() ? 'Online' : 'Offline' }}
                                </span>
                            </td>
                            <td>
                                @if($m->isPermissionCompliant())
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                    <span class="text-danger small">Non-compliant</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $m->syncAgeLabel() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Recent MDM Activity ─── --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-activity me-2"></i>Recent Sync Activity</strong>
            </div>
            <div class="card-body p-0">
                @if($recentMdm->isEmpty())
                <div class="text-center py-4 text-muted small">No sync data yet</div>
                @else
                <table class="table cp-table table-hover mb-0 small align-middle">
                    <thead>
                        <tr>
                            <th class="ps-3">MDM #</th>
                            <th>Employee</th>
                            <th>Model</th>
                            <th>Group</th>
                            <th>Status</th>
                            <th>IP</th>
                            <th>Last Sync</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentMdm as $m)
                        <tr>
                            <td class="ps-3 fw-semibold font-monospace">{{ $m->mdm_number }}</td>
                            <td>{{ $m->employee?->name ?? '—' }}</td>
                            <td>{{ $m->model ?? '—' }}</td>
                            <td><span class="badge bg-light text-secondary border">{{ $m->mdm_group ?? '—' }}</span></td>
                            <td>
                                <span class="badge rounded-pill {{ $m->isOnline() ? 'badge-on' : 'badge-off' }}">
                                    {{ $m->isOnline() ? 'Online' : 'Offline' }}
                                </span>
                            </td>
                            <td class="font-monospace text-muted" style="font-size:.78rem">{{ $m->ip_address ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $m->syncFreshnessClass() }}-subtle text-{{ $m->syncFreshnessClass() }} border">
                                    {{ $m->syncAgeLabel() }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            <div class="card-footer bg-white text-end">
                <a href="{{ route('client.devices') }}" class="btn btn-sm" style="color:var(--gs-teal)">
                    View all devices <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($mdmTotal > 0)
<script>
new Chart(document.getElementById('mdmDoughnut'), {
    type: 'doughnut',
    data: {
        labels: ['Online', 'Offline'],
        datasets: [{ data: [{{ $mdmOnline }}, {{ $mdmOffline }}], backgroundColor: ['#1a8a7c','#f07030'], borderWidth: 2 }]
    },
    options: { cutout: '70%', plugins: { legend: { display: false } } }
});
</script>
@endif
@endpush
