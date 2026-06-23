@extends('layouts.main')
@section('title','Procurement Report')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Procurement</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-cart-check me-2"></i>Procurement Report</h5>
    <a href="{{ route('procurement.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Procurement</a>
</div>

<div class="row g-3 mb-3">
    @php
    $dr = $demandStats;
    $totalDr = array_sum($dr);
    @endphp
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm"><div class="card-body py-3"><div class="fs-3 fw-bold text-warning">{{ $dr['submitted'] }}</div><div class="text-muted small">Pending Approval</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm"><div class="card-body py-3"><div class="fs-3 fw-bold text-success">{{ $dr['approved'] }}</div><div class="text-muted small">DR Approved</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm"><div class="card-body py-3"><div class="fs-3 fw-bold text-primary">{{ $dr['converted_to_po'] }}</div><div class="text-muted small">Converted to PO</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm"><div class="card-body py-3"><div class="fs-3 fw-bold">{{ $totalDr }}</div><div class="text-muted small">Total DRs</div></div></div></div>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header"><strong>PO Status Summary</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Status</th><th class="text-end">Count</th><th class="text-end">Value (₹)</th></tr></thead>
                    <tbody>
                    @php $grandTotal = $poByStatus->sum('total'); @endphp
                    @foreach($poByStatus as $row)
                    @php $b = match($row->status){'completed'=>'success','cancelled'=>'danger','approved'=>'primary','draft'=>'secondary',default=>'warning text-dark'}; @endphp
                    <tr>
                        <td><span class="badge bg-{{ $b }} me-1 small">&nbsp;</span>{{ ucfirst($row->status) }}</td>
                        <td class="text-end">{{ $row->count }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->total,0) }}</td>
                    </tr>
                    @endforeach
                    <tr class="table-light fw-bold"><td>Total</td><td class="text-end">{{ $poByStatus->sum('count') }}</td><td class="text-end">{{ number_format($grandTotal,0) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-header"><strong>Top Vendors by Spend</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Vendor</th><th class="text-end">Orders</th><th class="text-end">Total (₹)</th></tr></thead>
                    <tbody>
                    @forelse($poByVendor as $row)
                    <tr>
                        <td>{{ $row->vendor?->name }}</td>
                        <td class="text-end">{{ $row->count }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->total,0) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header"><strong>Recent Purchase Orders</strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>PO #</th><th>Vendor</th><th>Date</th><th class="text-end">Qty</th><th class="text-end">Grand Total (₹)</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($recentPOs as $po)
                    @php $pBadge = match($po->status){'completed'=>'success','cancelled'=>'danger','approved'=>'primary','draft'=>'secondary',default=>'warning text-dark'}; @endphp
                    <tr>
                        <td><a href="{{ route('procurement.purchase-orders.show',$po) }}" class="font-monospace text-decoration-none">{{ $po->po_number }}</a></td>
                        <td class="small">{{ $po->vendor?->name }}</td>
                        <td class="small">{{ $po->po_date?->format('d M Y') }}</td>
                        <td class="text-end small">{{ $po->quantity }}</td>
                        <td class="text-end fw-bold">{{ number_format($po->grand_total,0) }}</td>
                        <td><span class="badge bg-{{ $pBadge }}">{{ ucfirst($po->status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No POs</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
