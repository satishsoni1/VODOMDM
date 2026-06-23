@extends('layouts.main')
@section('title','DR — '.$demandRequest->request_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procurement.demand-requests') }}">Demand Requests</a></li>
    <li class="breadcrumb-item active">{{ $demandRequest->request_number }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold mb-0">{{ $demandRequest->request_number }}
            <span class="badge ms-2 bg-{{ match($demandRequest->status){ 'approved'=>'success','rejected'=>'danger','submitted'=>'warning text-dark','converted_to_po'=>'info',default=>'secondary' } }}">
                {{ ucwords(str_replace('_',' ',$demandRequest->status)) }}
            </span>
        </h5>
        <p class="text-muted mb-0 small">{{ $demandRequest->client?->name }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($demandRequest->status === 'submitted')
            <form method="POST" action="{{ route('procurement.demand-requests.approve',$demandRequest) }}" class="d-inline">
                @csrf
                <button name="action" value="approve" class="btn btn-sm btn-success" onclick="return confirm('Approve this demand request?')"><i class="bi bi-check-lg"></i> Approve</button>
                <button name="action" value="reject" class="btn btn-sm btn-danger" onclick="return confirm('Reject this demand request?')"><i class="bi bi-x-lg"></i> Reject</button>
            </form>
        @endif
        @if($demandRequest->status === 'approved')
            <a href="{{ route('procurement.rfqs.create', ['demand_request_id'=>$demandRequest->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-file-earmark-plus"></i> Create RFQ</a>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><strong>Details</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted w-40">Client</td><td>{{ $demandRequest->client?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Project</td><td>{{ $demandRequest->project?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Model</td><td>{{ $demandRequest->deviceModel?->model_name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Quantity</td><td class="fw-bold">{{ $demandRequest->quantity }}</td></tr>
                    <tr><td class="text-muted">Budget</td><td>{{ $demandRequest->budget_amount ? '₹'.number_format($demandRequest->budget_amount,2) : '—' }}</td></tr>
                    <tr><td class="text-muted">Division</td><td>{{ $demandRequest->division ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Region</td><td>{{ $demandRequest->region ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Required By</td><td class="{{ $demandRequest->required_date && \Carbon\Carbon::parse($demandRequest->required_date)->isPast() && !in_array($demandRequest->status,['approved','converted_to_po']) ? 'text-danger fw-bold' : '' }}">{{ $demandRequest->required_date ? \Carbon\Carbon::parse($demandRequest->required_date)->format('d M Y') : '—' }}</td></tr>
                    <tr><td class="text-muted">Requested By</td><td>{{ $demandRequest->requestedBy?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Created</td><td>{{ $demandRequest->created_at->format('d M Y') }}</td></tr>
                    @if($demandRequest->approved_by)
                    <tr><td class="text-muted">Approved By</td><td>{{ $demandRequest->approvedBy?->name }}</td></tr>
                    <tr><td class="text-muted">Approved At</td><td>{{ $demandRequest->approved_at ? \Carbon\Carbon::parse($demandRequest->approved_at)->format('d M Y H:i') : '—' }}</td></tr>
                    @endif
                    @if($demandRequest->rejection_reason)
                    <tr><td class="text-muted text-danger">Rejection Reason</td><td class="text-danger">{{ $demandRequest->rejection_reason }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><strong>Device Specification</strong></div>
            <div class="card-body"><pre class="mb-0 small" style="white-space:pre-wrap">{{ $demandRequest->device_specification }}</pre></div>
        </div>

        @if($demandRequest->justification)
        <div class="card mb-3">
            <div class="card-header"><strong>Justification</strong></div>
            <div class="card-body"><p class="mb-0">{{ $demandRequest->justification }}</p></div>
        </div>
        @endif

        {{-- Linked RFQs --}}
        @if($demandRequest->rfqs && $demandRequest->rfqs->isNotEmpty())
        <div class="card">
            <div class="card-header"><strong>Linked RFQs</strong></div>
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>RFQ Number</th><th>Status</th><th>Created</th><th></th></tr></thead>
                <tbody>
                    @foreach($demandRequest->rfqs as $rfq)
                    <tr>
                        <td><a href="{{ route('procurement.rfqs.show',$rfq) }}" class="fw-bold font-monospace">{{ $rfq->rfq_number }}</a></td>
                        <td><span class="badge bg-{{ match($rfq->status){ 'closed'=>'secondary','sent'=>'primary',default=>'secondary' } }}">{{ ucwords($rfq->status) }}</span></td>
                        <td class="small">{{ $rfq->created_at->format('d M Y') }}</td>
                        <td><a href="{{ route('procurement.rfqs.show',$rfq) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
