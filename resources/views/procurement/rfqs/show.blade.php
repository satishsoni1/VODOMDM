@extends('layouts.main')
@section('title','RFQ — '.$rfq->rfq_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procurement.rfqs') }}">RFQs</a></li>
    <li class="breadcrumb-item active">{{ $rfq->rfq_number }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold mb-0">{{ $rfq->rfq_number }}
            <span class="badge ms-2 bg-{{ match($rfq->status){ 'closed'=>'secondary','sent'=>'primary',default=>'secondary' } }}">{{ ucfirst($rfq->status) }}</span>
        </h5>
        <p class="text-muted mb-0 small">{{ Str::limit($rfq->device_specification, 80) }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($rfq->status === 'sent')
            <a href="{{ route('procurement.purchase-orders.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-file-earmark-check"></i> Create PO</a>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><strong>RFQ Details</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    @if($rfq->demandRequest)
                    <tr><td class="text-muted">DR</td><td><a href="{{ route('procurement.demand-requests.show',$rfq->demandRequest) }}" class="font-monospace">{{ $rfq->demandRequest->request_number }}</a></td></tr>
                    @endif
                    <tr><td class="text-muted">Quantity</td><td class="fw-bold">{{ $rfq->quantity }}</td></tr>
                    <tr><td class="text-muted">Deadline</td><td class="{{ $rfq->response_deadline && \Carbon\Carbon::parse($rfq->response_deadline)->isPast() && $rfq->status==='sent' ? 'text-danger fw-bold' : '' }}">{{ $rfq->response_deadline ? \Carbon\Carbon::parse($rfq->response_deadline)->format('d M Y') : '—' }}</td></tr>
                    <tr><td class="text-muted">Created</td><td>{{ $rfq->created_at->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Vendors</td><td>{{ $rfq->vendors->count() }}</td></tr>
                    <tr><td class="text-muted">Quotations</td><td>{{ $rfq->quotations->count() }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Specification</strong></div>
            <div class="card-body"><pre class="small mb-0" style="white-space:pre-wrap">{{ $rfq->device_specification }}</pre></div>
        </div>

        @if($rfq->terms)
        <div class="card">
            <div class="card-header"><strong>Terms &amp; Conditions</strong></div>
            <div class="card-body"><pre class="small mb-0" style="white-space:pre-wrap">{{ $rfq->terms }}</pre></div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        {{-- Quotations Comparison --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Vendor Quotations</strong>
                @if($rfq->status === 'sent')
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addQuotationModal"><i class="bi bi-plus-lg"></i> Add Quotation</button>
                @endif
            </div>
            @if($rfq->quotations->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Vendor</th><th>Qty</th><th>Unit Price</th><th>Total</th><th>Delivery (days)</th><th>Valid Until</th><th>Selected</th></tr>
                    </thead>
                    <tbody>
                        @foreach($rfq->quotations->sortBy('unit_price') as $q)
                        <tr class="{{ $q->is_selected ? 'table-success' : '' }}">
                            <td>{{ $q->vendor?->name }}</td>
                            <td>{{ $q->quantity }}</td>
                            <td>₹{{ number_format($q->unit_price,2) }}</td>
                            <td>₹{{ number_format($q->total_amount,2) }}</td>
                            <td class="small">{{ $q->delivery_days ? $q->delivery_days.' days' : '—' }}</td>
                            <td class="small">{{ $q->valid_until ? \Carbon\Carbon::parse($q->valid_until)->format('d M Y') : '—' }}</td>
                            <td class="text-center">@if($q->is_selected)<i class="bi bi-check-circle-fill text-success"></i>@endif</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body text-center text-muted py-3">No quotations received yet.</div>
            @endif
        </div>

        {{-- Invited Vendors --}}
        <div class="card">
            <div class="card-header"><strong>Invited Vendors ({{ $rfq->vendors->count() }})</strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Vendor</th><th>Code</th><th>Status</th><th>Sent At</th><th>Quotation</th></tr></thead>
                    <tbody>
                        @foreach($rfq->vendors as $rv)
                        @php $quote = $rfq->quotations->where('vendor_id',$rv->vendor_id)->first(); @endphp
                        <tr>
                            <td><a href="{{ route('vendors.show',$rv->vendor) }}">{{ $rv->vendor?->name }}</a></td>
                            <td class="font-monospace small">{{ $rv->vendor?->code }}</td>
                            <td><span class="badge bg-{{ match($rv->status){ 'responded'=>'success','no_response'=>'danger',default=>'secondary' } }}">{{ ucwords(str_replace('_',' ',$rv->status)) }}</span></td>
                            <td class="small">{{ $rv->sent_at ? \Carbon\Carbon::parse($rv->sent_at)->format('d M Y') : '—' }}</td>
                            <td>
                                @if($quote)
                                <span class="badge bg-success">₹{{ number_format($quote->unit_price,2) }}</span>
                                @else
                                <span class="badge bg-secondary">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Quotation Modal --}}
@if($rfq->status === 'sent')
<div class="modal fade" id="addQuotationModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('procurement.rfqs.quotations.store',$rfq) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Vendor Quotation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Vendor *</label>
                        <select class="form-select" name="vendor_id" required>
                            <option value="">— Select Vendor —</option>
                            @foreach($rfq->vendors as $rv)
                            <option value="{{ $rv->vendor_id }}">{{ $rv->vendor?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label">Quotation Number</label><input class="form-control" name="quotation_number"></div>
                        <div class="col-6"><label class="form-label">Quotation Date *</label><input type="date" class="form-control" name="quotation_date" value="{{ now()->format('Y-m-d') }}" required></div>
                        <div class="col-6"><label class="form-label">Quantity *</label><input type="number" class="form-control" name="quantity" value="{{ $rfq->quantity }}" min="1" required></div>
                        <div class="col-6"><label class="form-label">Unit Price (₹) *</label><input type="number" class="form-control" name="unit_price" step="0.01" min="0" required></div>
                        <div class="col-6"><label class="form-label">Delivery (days)</label><input type="number" class="form-control" name="delivery_days" min="0"></div>
                        <div class="col-6"><label class="form-label">Valid Until</label><input type="date" class="form-control" name="valid_until"></div>
                        <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="negotiation_notes" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Quotation</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
