@extends('layouts.main')
@section('title','Goods Receipt Notes')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventory</a></li>
    <li class="breadcrumb-item active">GRNs</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-box-seam me-2"></i>Goods Receipt Notes</h5>
    <a href="{{ route('inventory.grn.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New GRN</a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><input class="form-control form-control-sm" name="q" placeholder="GRN Number" value="{{ request('q') }}"></div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    @foreach(['pending_qc','qc_done','partially_accepted','accepted','rejected'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('inventory.grn') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-dark">
                <tr><th>GRN Number</th><th>PO Number</th><th>Vendor</th><th>Received Date</th><th>Qty Received</th><th>Qty Accepted</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($grns as $grn)
                @php $grnBadge = match($grn->status){ 'accepted'=>'success','rejected'=>'danger','partially_accepted'=>'warning text-dark','qc_done'=>'info',default=>'secondary' }; @endphp
                <tr>
                    <td><a href="{{ route('inventory.grn.show',$grn) }}" class="fw-bold font-monospace text-decoration-none">{{ $grn->grn_number }}</a></td>
                    <td><a href="{{ route('procurement.purchase-orders.show',$grn->purchaseOrder) }}" class="font-monospace small">{{ $grn->purchaseOrder?->po_number }}</a></td>
                    <td class="small">{{ $grn->vendor?->name ?? '—' }}</td>
                    <td class="small">{{ $grn->received_date ? \Carbon\Carbon::parse($grn->received_date)->format('d M Y') : '—' }}</td>
                    <td>{{ $grn->quantity_received }}</td>
                    <td>{{ $grn->quantity_accepted }}</td>
                    <td><span class="badge bg-{{ $grnBadge }}">{{ ucwords(str_replace('_',' ',$grn->status)) }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('inventory.grn.show',$grn) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">No GRNs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($grns->hasPages())<div class="card-footer">{{ $grns->links() }}</div>@endif
</div>
@endsection
