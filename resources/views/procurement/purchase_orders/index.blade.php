@extends('layouts.main')
@section('title','Purchase Orders')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item active">Purchase Orders</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i>Purchase Orders</h5>
    <a href="{{ route('procurement.purchase-orders.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New PO</a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><input class="form-control form-control-sm" name="q" placeholder="PO Number" value="{{ request('q') }}"></div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="vendor_id">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $v)
                    <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id?'selected':'' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    @foreach(['draft','approved','sent','acknowledged','partial','completed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('procurement.purchase-orders') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-dark">
                <tr><th>PO Number</th><th>Vendor</th><th>PO Date</th><th>Expected Delivery</th><th>Qty</th><th>Grand Total</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($orders as $po)
                <tr>
                    <td><a href="{{ route('procurement.purchase-orders.show',$po) }}" class="fw-bold font-monospace text-decoration-none">{{ $po->po_number }}</a></td>
                    <td class="small">{{ $po->vendor?->name ?? '—' }}</td>
                    <td class="small">{{ $po->po_date ? \Carbon\Carbon::parse($po->po_date)->format('d M Y') : '—' }}</td>
                    <td class="small {{ $po->expected_delivery_date && \Carbon\Carbon::parse($po->expected_delivery_date)->isPast() && !in_array($po->status,['completed','cancelled']) ? 'text-danger fw-bold' : '' }}">{{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('d M Y') : '—' }}</td>
                    <td>{{ $po->quantity }}</td>
                    <td class="font-monospace">₹{{ number_format($po->grand_total,0) }}</td>
                    <td>
                        <span class="badge bg-{{ match($po->status){ 'approved'=>'success','completed'=>'success','partial'=>'warning text-dark','cancelled'=>'danger','sent'=>'info','acknowledged'=>'info',default=>'secondary' } }}">
                            {{ ucwords(str_replace('_',' ',$po->status)) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('procurement.purchase-orders.show',$po) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">No purchase orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())<div class="card-footer">{{ $orders->links() }}</div>@endif
</div>
@endsection
