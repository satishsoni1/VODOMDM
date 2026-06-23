@extends('layouts.main')
@section('title','Procurement Dashboard')
@section('breadcrumb')<li class="breadcrumb-item active">Procurement</li>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-cart3 me-2"></i>Procurement</h5>
    <a href="{{ route('procurement.demand-requests.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New Demand Request</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-warning">{{ $stats['pending_approval'] }}</div>
                <div class="small text-muted">Pending Approval</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-info">{{ $stats['open_rfqs'] }}</div>
                <div class="small text-muted">Open RFQs</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-primary">{{ $stats['pending_pos'] }}</div>
                <div class="small text-muted">Active Purchase Orders</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-success">₹{{ number_format($stats['total_po_value'],0) }}</div>
                <div class="small text-muted">Total PO Value</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Pending Approvals</strong>
                <a href="{{ route('procurement.demand-requests') }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2">View All</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($pendingApprovals as $dr)
                <a href="{{ route('procurement.demand-requests.show',$dr) }}" class="list-group-item list-group-item-action py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold small font-monospace">{{ $dr->request_number }}</span>
                        <span class="badge bg-warning text-dark">{{ ucfirst($dr->status) }}</span>
                    </div>
                    <div class="small text-muted">{{ Str::limit($dr->device_specification,40) }} &mdash; {{ $dr->client?->name }}</div>
                </a>
                @empty
                <div class="list-group-item text-muted small py-3 text-center">No pending approvals.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Recent RFQs</strong>
                <a href="{{ route('procurement.rfqs') }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2">View All</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($recentRfqs as $rfq)
                <a href="{{ route('procurement.rfqs.show',$rfq) }}" class="list-group-item list-group-item-action py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold small font-monospace">{{ $rfq->rfq_number }}</span>
                        <span class="badge bg-{{ match($rfq->status){ 'closed'=>'secondary','sent'=>'primary',default=>'secondary' } }}">{{ ucfirst($rfq->status) }}</span>
                    </div>
                    <div class="small text-muted">{{ Str::limit($rfq->device_specification,40) }} &mdash; {{ $rfq->created_at->format('d M Y') }}</div>
                </a>
                @empty
                <div class="list-group-item text-muted small py-3 text-center">No RFQs.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Recent Purchase Orders</strong>
                <a href="{{ route('procurement.purchase-orders') }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2">View All</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($recentPOs as $po)
                <a href="{{ route('procurement.purchase-orders.show',$po) }}" class="list-group-item list-group-item-action py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold small font-monospace">{{ $po->po_number }}</span>
                        <span class="badge bg-{{ match($po->status){ 'approved'=>'success','completed'=>'success','partial'=>'warning text-dark','cancelled'=>'danger',default=>'secondary' } }}">{{ ucwords(str_replace('_',' ',$po->status)) }}</span>
                    </div>
                    <div class="small text-muted">{{ $po->vendor?->name }} &mdash; ₹{{ number_format($po->grand_total,0) }}</div>
                </a>
                @empty
                <div class="list-group-item text-muted small py-3 text-center">No purchase orders.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
