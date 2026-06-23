@extends('layouts.main')
@section('title','RFQs')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item active">RFQs</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-envelope-paper me-2"></i>Request for Quotations</h5>
    <a href="{{ route('procurement.rfqs.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New RFQ</a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><input class="form-control form-control-sm" name="q" placeholder="RFQ Number / Specification" value="{{ request('q') }}"></div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    @foreach(['draft','sent','closed'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('procurement.rfqs') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-dark">
                <tr><th>RFQ Number</th><th>Specification</th><th>Qty</th><th>Vendors</th><th>Quotations</th><th>Response Deadline</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($rfqs as $rfq)
                <tr>
                    <td><a href="{{ route('procurement.rfqs.show',$rfq) }}" class="fw-bold font-monospace text-decoration-none">{{ $rfq->rfq_number }}</a></td>
                    <td class="small">{{ Str::limit($rfq->device_specification, 50) }}</td>
                    <td>{{ $rfq->quantity }}</td>
                    <td class="text-center">{{ $rfq->vendors->count() }}</td>
                    <td class="text-center">{{ $rfq->quotations->count() }}</td>
                    <td class="small {{ $rfq->response_deadline && \Carbon\Carbon::parse($rfq->response_deadline)->isPast() && $rfq->status==='sent' ? 'text-danger fw-bold' : '' }}">{{ $rfq->response_deadline ? \Carbon\Carbon::parse($rfq->response_deadline)->format('d M Y') : '—' }}</td>
                    <td><span class="badge bg-{{ match($rfq->status){ 'closed'=>'secondary','sent'=>'primary',default=>'secondary' } }}">{{ ucfirst($rfq->status) }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('procurement.rfqs.show',$rfq) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">No RFQs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rfqs->hasPages())<div class="card-footer">{{ $rfqs->links() }}</div>@endif
</div>
@endsection
