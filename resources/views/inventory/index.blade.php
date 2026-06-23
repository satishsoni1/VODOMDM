@extends('layouts.main')
@section('title','Inventory')
@section('breadcrumb')<li class="breadcrumb-item active">Inventory</li>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-boxes me-2"></i>Inventory</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('inventory.grn.create') }}" class="btn btn-sm btn-outline-success"><i class="bi bi-box-seam"></i> New GRN</a>
        <a href="{{ route('devices.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Register Device</a>
    </div>
</div>

{{-- Stock Summary --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold">{{ $stats['total'] }}</div>
                <div class="small text-muted">Total Devices</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-success">{{ $stats['in_stock'] }}</div>
                <div class="small text-muted">In Stock</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-secondary">{{ $stats['received'] }}</div>
                <div class="small text-muted">Received</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-warning">{{ $stats['qc_pending'] }}</div>
                <div class="small text-muted">QC Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-info">{{ $stats['config_pending'] }}</div>
                <div class="small text-muted">Config Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card text-center h-100 border-primary">
            <div class="card-body py-3">
                <a href="{{ route('inventory.grn') }}" class="text-decoration-none">
                    <div class="fs-2 fw-bold text-primary">{{ $recentGrns->count() }}</div>
                    <div class="small text-muted">Recent GRNs</div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Recent GRNs --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Recent GRNs</strong>
                <a href="{{ route('inventory.grn') }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2">View All</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($recentGrns->take(8) as $grn)
                <a href="{{ route('inventory.grn.show',$grn) }}" class="list-group-item list-group-item-action py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold font-monospace small">{{ $grn->grn_number }}</span>
                        @php $grnBadge = match($grn->status){ 'accepted'=>'success','rejected'=>'danger','partially_accepted'=>'warning text-dark','qc_done'=>'info',default=>'secondary' }; @endphp
                        <span class="badge bg-{{ $grnBadge }}">{{ ucwords(str_replace('_',' ',$grn->status)) }}</span>
                    </div>
                    <div class="small text-muted">{{ $grn->vendor?->name }} &mdash; {{ $grn->quantity_accepted }}/{{ $grn->quantity_received }} accepted</div>
                </a>
                @empty
                <div class="list-group-item text-muted small py-3 text-center">No GRNs yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Location Stock Breakdown --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong>Stock by Location</strong></div>
            @if($locationStock->isNotEmpty())
            <div class="list-group list-group-flush">
                @foreach($locationStock as $ls)
                <div class="list-group-item py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small fw-medium">{{ $ls->currentLocation?->name ?? 'Unknown' }}</span>
                        <span class="badge bg-primary rounded-pill">{{ $ls->count }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="card-body text-center text-muted small py-3">No location data available.</div>
            @endif
        </div>
    </div>
</div>
@endsection
