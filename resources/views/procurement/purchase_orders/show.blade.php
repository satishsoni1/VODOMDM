@extends('layouts.main')
@section('title','PO — '.$purchaseOrder->po_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procurement.purchase-orders') }}">Purchase Orders</a></li>
    <li class="breadcrumb-item active">{{ $purchaseOrder->po_number }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold mb-0">{{ $purchaseOrder->po_number }}
            <span class="badge ms-2 bg-{{ match($purchaseOrder->status){ 'approved'=>'success','completed'=>'success','partial'=>'warning text-dark','cancelled'=>'danger','sent'=>'info','acknowledged'=>'info',default=>'secondary' } }}">
                {{ ucwords(str_replace('_',' ',$purchaseOrder->status)) }}
            </span>
        </h5>
        <p class="text-muted mb-0 small">{{ $purchaseOrder->vendor?->name }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($purchaseOrder->status === 'draft')
        <form method="POST" action="{{ route('procurement.purchase-orders.approve',$purchaseOrder) }}" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-success" onclick="return confirm('Approve this PO?')"><i class="bi bi-check-lg"></i> Approve</button>
        </form>
        @endif
        @if(in_array($purchaseOrder->status, ['approved','sent','acknowledged','partial']))
        <a href="{{ route('inventory.grn.create', ['po_id'=>$purchaseOrder->id]) }}" class="btn btn-sm btn-success"><i class="bi bi-box-seam"></i> Create GRN</a>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><strong>Order Details</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted w-40">Vendor</td><td><a href="{{ route('vendors.show',$purchaseOrder->vendor) }}">{{ $purchaseOrder->vendor?->name }}</a></td></tr>
                    <tr><td class="text-muted">PO Date</td><td>{{ $purchaseOrder->po_date ? \Carbon\Carbon::parse($purchaseOrder->po_date)->format('d M Y') : '—' }}</td></tr>
                    <tr><td class="text-muted">Expected Del.</td><td class="{{ $purchaseOrder->expected_delivery_date && \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->isPast() && !in_array($purchaseOrder->status,['completed','cancelled']) ? 'text-danger fw-bold' : '' }}">{{ $purchaseOrder->expected_delivery_date ? \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('d M Y') : '—' }}</td></tr>
                    <tr><td class="text-muted">Payment Terms</td><td>{{ $purchaseOrder->payment_terms ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Warranty</td><td>{{ $purchaseOrder->warranty_months ?? '—' }}</td></tr>
                    @if($purchaseOrder->rfq)
                    <tr><td class="text-muted">RFQ</td><td><a href="{{ route('procurement.rfqs.show',$purchaseOrder->rfq) }}" class="font-monospace">{{ $purchaseOrder->rfq->rfq_number }}</a></td></tr>
                    @endif
                    @if($purchaseOrder->approved_by)
                    <tr><td class="text-muted">Approved By</td><td>{{ $purchaseOrder->approvedBy?->name }}</td></tr>
                    <tr><td class="text-muted">Approved At</td><td>{{ $purchaseOrder->approved_at ? \Carbon\Carbon::parse($purchaseOrder->approved_at)->format('d M Y H:i') : '—' }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Financials</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Qty Ordered</td><td class="fw-bold">{{ $purchaseOrder->quantity }}</td></tr>
                    <tr><td class="text-muted">Unit Price</td><td>₹{{ number_format($purchaseOrder->unit_price,2) }}</td></tr>
                    <tr><td class="text-muted">Subtotal</td><td>₹{{ number_format($purchaseOrder->total_amount,2) }}</td></tr>
                    <tr><td class="text-muted">Tax</td><td>₹{{ number_format($purchaseOrder->tax_amount,2) }}</td></tr>
                    <tr class="table-light fw-bold"><td class="text-muted">Grand Total</td><td class="fs-6">₹{{ number_format($purchaseOrder->grand_total,2) }}</td></tr>
                </table>
            </div>
        </div>

        {{-- GRN Progress --}}
        @if($purchaseOrder->grns && $purchaseOrder->grns->isNotEmpty())
        <div class="card">
            <div class="card-header"><strong>GRN Progress</strong></div>
            <div class="card-body">
                @php $totalReceived = $purchaseOrder->grns->sum('quantity_accepted'); $pct = $purchaseOrder->quantity ? min(100, round($totalReceived/$purchaseOrder->quantity*100)) : 0; @endphp
                <div class="d-flex justify-content-between small mb-1"><span>Received</span><span>{{ $totalReceived }} / {{ $purchaseOrder->quantity }}</span></div>
                <div class="progress" style="height:12px"><div class="progress-bar bg-success" @style(['width' => $pct.'%'])></div></div>
                <p class="text-muted small mt-1 mb-0">{{ $pct }}% complete</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        {{-- Invoices --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Invoices</strong>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addInvoiceModal"><i class="bi bi-plus-lg"></i> Add Invoice</button>
            </div>
            @if($purchaseOrder->invoices && $purchaseOrder->invoices->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Invoice #</th><th>Date</th><th>Amount</th><th>Due Date</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($purchaseOrder->invoices as $inv)
                        <tr>
                            <td class="font-monospace">{{ $inv->invoice_number }}</td>
                            <td class="small">{{ $inv->invoice_date ? \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') : '—' }}</td>
                            <td>₹{{ number_format($inv->invoice_amount,2) }}</td>
                            <td class="small {{ $inv->due_date && \Carbon\Carbon::parse($inv->due_date)->isPast() && $inv->payment_status!=='paid' ? 'text-danger fw-bold' : '' }}">{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}</td>
                            <td><span class="badge bg-{{ match($inv->payment_status){ 'paid'=>'success','partial'=>'warning text-dark',default=>'secondary' } }}">{{ ucfirst($inv->payment_status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body text-center text-muted small">No invoices added yet.</div>
            @endif
        </div>

        {{-- GRNs --}}
        @if($purchaseOrder->grns && $purchaseOrder->grns->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header"><strong>Goods Receipt Notes</strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>GRN Number</th><th>Received Date</th><th>Qty Received</th><th>Qty Accepted</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach($purchaseOrder->grns as $grn)
                        <tr>
                            <td><a href="{{ route('inventory.grn.show',$grn) }}" class="fw-bold font-monospace">{{ $grn->grn_number }}</a></td>
                            <td class="small">{{ $grn->received_date ? \Carbon\Carbon::parse($grn->received_date)->format('d M Y') : '—' }}</td>
                            <td>{{ $grn->quantity_received }}</td>
                            <td>{{ $grn->quantity_accepted }}</td>
                            <td><span class="badge bg-{{ match($grn->status){ 'accepted'=>'success','rejected'=>'danger','partially_accepted'=>'warning text-dark','qc_done'=>'info',default=>'secondary' } }}">{{ ucwords(str_replace('_',' ',$grn->status)) }}</span></td>
                            <td><a href="{{ route('inventory.grn.show',$grn) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($purchaseOrder->special_instructions)
        <div class="card">
            <div class="card-header"><strong>Special Instructions</strong></div>
            <div class="card-body"><p class="mb-0">{{ $purchaseOrder->special_instructions }}</p></div>
        </div>
        @endif
    </div>
</div>

{{-- Add Invoice Modal --}}
<div class="modal fade" id="addInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('procurement.purchase-orders.invoices.store',$purchaseOrder) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Invoice</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label">Invoice Number *</label><input class="form-control" name="invoice_number" required></div>
                        <div class="col-6"><label class="form-label">Invoice Date *</label><input type="date" class="form-control" name="invoice_date" value="{{ now()->format('Y-m-d') }}" required></div>
                        <div class="col-6"><label class="form-label">Amount (₹) *</label><input type="number" class="form-control" name="invoice_amount" step="0.01" min="0" required></div>
                        <div class="col-6"><label class="form-label">Due Date</label><input type="date" class="form-control" name="due_date"></div>
                        <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Invoice</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
