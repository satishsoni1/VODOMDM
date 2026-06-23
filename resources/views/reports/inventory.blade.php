@extends('layouts.main')
@section('title','Inventory Report')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Inventory</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-box-seam me-2"></i>Inventory Report</h5>
    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Inventory</a>
</div>

<div class="row g-3">
    {{-- Status Breakdown --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><strong>Device Status Breakdown</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Lifecycle Status</th><th class="text-end">Count</th><th class="text-end">%</th></tr></thead>
                    <tbody>
                    @php $totalDevices = $byStatus->sum('count'); @endphp
                    @foreach($byStatus as $row)
                    @php
                    $pct = $totalDevices ? round($row->count / $totalDevices * 100, 1) : 0;
                    $colors = ['active'=>'success','in_stock'=>'info','assigned'=>'primary','dispatched'=>'warning','under_repair'=>'danger','disposed'=>'dark'];
                    $c = $colors[$row->lifecycle_status] ?? 'secondary';
                    @endphp
                    <tr>
                        <td><span class="badge bg-{{ $c }} me-1 small">&nbsp;</span>{{ ucwords(str_replace('_',' ',$row->lifecycle_status)) }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->count) }}</td>
                        <td class="text-end text-muted small">{{ $pct }}%</td>
                    </tr>
                    @endforeach
                    <tr class="table-light fw-bold"><td>Total</td><td class="text-end">{{ number_format($totalDevices) }}</td><td></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- By Client --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><strong>Top Clients by Device Count</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Client</th><th class="text-end">Devices</th></tr></thead>
                    <tbody>
                    @forelse($byClient as $row)
                    <tr>
                        <td>{{ $row->client?->name ?? 'Unassigned' }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->count) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-muted text-center py-3">No data</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- By Location --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong>Location-wise Stock</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Location</th><th class="text-end">Devices</th></tr></thead>
                    <tbody>
                    @forelse($byLocation as $row)
                    <tr>
                        <td>{{ $row->currentLocation?->name ?? 'No Location' }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->count) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-muted text-center py-3">No data</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent GRNs --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong>Recent GRNs</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>GRN #</th><th>Vendor</th><th>Received</th><th class="text-end">Qty</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($recentGrns as $grn)
                    @php $gBadge = match($grn->status){'accepted'=>'success','rejected'=>'danger','partially_accepted'=>'warning text-dark',default=>'secondary'}; @endphp
                    <tr>
                        <td><a href="{{ route('inventory.grn.show',$grn) }}" class="font-monospace text-decoration-none small">{{ $grn->grn_number }}</a></td>
                        <td class="small">{{ $grn->purchaseOrder?->vendor?->name }}</td>
                        <td class="small">{{ $grn->received_date ? \Carbon\Carbon::parse($grn->received_date)->format('d M Y') : '—' }}</td>
                        <td class="text-end small">{{ $grn->quantity_accepted }}</td>
                        <td><span class="badge bg-{{ $gBadge }} small">{{ ucwords(str_replace('_',' ',$grn->status)) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-muted text-center py-3">No GRNs</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
