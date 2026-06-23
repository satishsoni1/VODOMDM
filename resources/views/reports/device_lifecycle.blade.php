@extends('layouts.main')
@section('title','Device Lifecycle Report')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Device Lifecycle</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-recycle me-2"></i>Device Lifecycle Report</h5>
    <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Devices</a>
</div>

<div class="row g-3">
    {{-- Lifecycle Status --}}
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><strong>Lifecycle Status</strong></div>
            <div class="card-body p-0">
                @php $total = $lifecycleCount->sum('count'); @endphp
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Status</th><th class="text-end">Count</th><th class="text-end" style="width:80px">%</th></tr></thead>
                    <tbody>
                    @foreach($lifecycleCount as $row)
                    @php
                    $pct = $total ? round($row->count/$total*100,1) : 0;
                    $colors = ['active'=>'success','in_stock'=>'info','assigned'=>'primary','dispatched'=>'warning','under_repair'=>'danger','disposed'=>'dark','returned'=>'secondary'];
                    $c = $colors[$row->lifecycle_status] ?? 'secondary';
                    @endphp
                    <tr>
                        <td class="small"><span class="badge bg-{{ $c }} me-1">&nbsp;</span>{{ ucwords(str_replace('_',' ',$row->lifecycle_status)) }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->count) }}</td>
                        <td class="text-end text-muted small">{{ $pct }}%</td>
                    </tr>
                    @endforeach
                    <tr class="table-light fw-bold"><td class="small">Total</td><td class="text-end">{{ number_format($total) }}</td><td></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Condition --}}
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><strong>Condition Breakdown</strong></div>
            <div class="card-body p-0">
                @php $totalCond = $conditionCount->sum('count'); @endphp
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Condition</th><th class="text-end">Count</th><th class="text-end" style="width:80px">%</th></tr></thead>
                    <tbody>
                    @foreach($conditionCount as $row)
                    @php
                    $pct = $totalCond ? round($row->count/$totalCond*100,1) : 0;
                    $cond = ['new'=>'success','good'=>'info','fair'=>'warning text-dark','poor'=>'danger','damaged'=>'dark'];
                    $c = $cond[$row->condition ?? ''] ?? 'secondary';
                    @endphp
                    <tr>
                        <td class="small"><span class="badge bg-{{ $c }} me-1">&nbsp;</span>{{ ucfirst($row->condition ?? 'Unknown') }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->count) }}</td>
                        <td class="text-end text-muted small">{{ $pct }}%</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Age Groups --}}
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><strong>Device Age Distribution</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Age Group</th><th class="text-end">Count</th></tr></thead>
                    <tbody>
                    @forelse($ageGroups as $row)
                    <tr>
                        <td class="small">{{ $row->age_group }}</td>
                        <td class="text-end fw-bold">{{ $row->count }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center text-muted py-3">No purchase date data</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Warranty Expiry --}}
    <div class="col-12">
        <div class="card {{ $warrantyExpiring->isNotEmpty() ? 'border-warning' : '' }}">
            <div class="card-header {{ $warrantyExpiring->isNotEmpty() ? 'bg-warning bg-opacity-10' : '' }}">
                <strong><i class="bi bi-calendar-x me-1"></i>Warranties Expiring in Next 90 Days
                    @if($warrantyExpiring->isNotEmpty())
                    <span class="badge bg-warning text-dark ms-2">{{ $warrantyExpiring->count() }}</span>
                    @endif
                </strong>
            </div>
            @if($warrantyExpiring->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Asset Tag</th><th>Model</th><th>Serial #</th><th>Warranty Expiry</th><th>Days Left</th><th></th></tr></thead>
                    <tbody>
                    @foreach($warrantyExpiring as $dev)
                    @php
                    $daysLeft = now()->diffInDays($dev->warranty_expiry, false);
                    $urgency = $daysLeft <= 30 ? 'danger' : ($daysLeft <= 60 ? 'warning text-dark' : 'info');
                    @endphp
                    <tr>
                        <td class="font-monospace fw-bold"><a href="{{ route('devices.show',$dev) }}" class="text-decoration-none">{{ $dev->asset_tag }}</a></td>
                        <td class="small">{{ $dev->model?->brand?->name }} {{ $dev->model?->model_name }}</td>
                        <td class="font-monospace small text-muted">{{ $dev->serial_number }}</td>
                        <td class="small fw-bold">{{ $dev->warranty_expiry->format('d M Y') }}</td>
                        <td><span class="badge bg-{{ $urgency }}">{{ $daysLeft }}d</span></td>
                        <td><a href="{{ route('devices.show',$dev) }}" class="btn btn-sm btn-outline-secondary py-0 px-1"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body text-center text-muted py-4">
                <i class="bi bi-check-circle fs-1 d-block mb-2 text-success opacity-50"></i>
                No warranty expirations in the next 90 days.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
