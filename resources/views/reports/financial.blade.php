@extends('layouts.main')
@section('title','Financial Report')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Financial</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-currency-rupee me-2"></i>Financial Report</h5>
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Reports</a>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-4 fw-bold text-primary">₹{{ number_format($poSummary['total_value']/100000,2) }}L</div>
                <div class="text-muted small">Total Procurement</div>
                <div class="text-muted small">{{ $poSummary['total_orders'] }} Orders</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-4 fw-bold text-success">₹{{ number_format($poSummary['paid_value']/100000,2) }}L</div>
                <div class="text-muted small">Completed POs</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-4 fw-bold text-warning">₹{{ number_format($poSummary['pending_value']/100000,2) }}L</div>
                <div class="text-muted small">Pending PO Value</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-4 fw-bold text-danger">₹{{ number_format(($repairCosts->actual ?? 0)/100000,2) }}L</div>
                <div class="text-muted small">Repair Costs</div>
                <div class="text-muted small">{{ $repairCosts->count ?? 0 }} Orders</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Insurance Summary --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><strong><i class="bi bi-shield-check me-1"></i>Insurance Financials</strong></div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Total Premiums Paid</span>
                    <span class="fw-bold">₹{{ number_format($insuranceSummary['total_premiums'],0) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Total Amount Claimed</span>
                    <span class="fw-bold text-warning">₹{{ number_format($insuranceSummary['total_claimed'],0) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Total Amount Settled</span>
                    <span class="fw-bold text-success">₹{{ number_format($insuranceSummary['total_settled'],0) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Repair Cost Summary --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><strong><i class="bi bi-tools me-1"></i>Repair Cost Summary</strong></div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Total Repair Orders</span>
                    <span class="fw-bold">{{ $repairCosts->count ?? 0 }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Estimated Cost</span>
                    <span class="fw-bold">₹{{ number_format($repairCosts->estimated ?? 0,0) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Actual Cost</span>
                    <span class="fw-bold text-danger">₹{{ number_format($repairCosts->actual ?? 0,0) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly PO trend --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><strong><i class="bi bi-graph-up me-1"></i>Monthly PO Trend</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Month</th><th class="text-end">Orders</th><th class="text-end">Value (₹)</th></tr></thead>
                    <tbody>
                    @forelse($poByMonth as $row)
                    <tr>
                        <td class="small">{{ \Carbon\Carbon::createFromFormat('Y-m',$row->month)->format('M Y') }}</td>
                        <td class="text-end small">{{ $row->count }}</td>
                        <td class="text-end fw-bold small">{{ number_format($row->total,0) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
