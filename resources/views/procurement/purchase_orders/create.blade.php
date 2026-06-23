@extends('layouts.main')
@section('title','New Purchase Order')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procurement.purchase-orders') }}">Purchase Orders</a></li>
    <li class="breadcrumb-item active">New PO</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-9">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-receipt me-2"></i>New Purchase Order</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('procurement.purchase-orders.store') }}">
            @csrf

            @if($selectedQuotation)
            <input type="hidden" name="vendor_quotation_id" value="{{ $selectedQuotation->id }}">
            <input type="hidden" name="rfq_id" value="{{ $selectedQuotation->rfq_id }}">
            <div class="alert alert-info d-flex align-items-center gap-2 py-2 mb-3">
                <i class="bi bi-link-45deg fs-5"></i>
                <div>Based on quotation from <strong>{{ $selectedQuotation->vendor?->name }}</strong> — ₹{{ number_format($selectedQuotation->unit_price,2) }}/unit</div>
            </div>
            @endif

            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Vendor *</label>
                    <select class="form-select @error('vendor_id') is-invalid @enderror" name="vendor_id" required>
                        <option value="">— Select Vendor —</option>
                        @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ old('vendor_id', $selectedQuotation?->vendor_id)==$v->id?'selected':'' }}>{{ $v->name }}</option>
                        @endforeach
                    </select>
                    @error('vendor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3"><label class="form-label">PO Date *</label><input type="date" class="form-control" name="po_date" value="{{ old('po_date', now()->format('Y-m-d')) }}" required></div>
                <div class="col-md-4"><label class="form-label">Expected Delivery Date</label><input type="date" class="form-control" name="expected_delivery_date" value="{{ old('expected_delivery_date') }}"></div>

                <div class="col-md-2"><label class="form-label">Quantity *</label><input type="number" class="form-control" name="quantity" id="qtyInput" value="{{ old('quantity', $selectedQuotation?->quantity ?? 1) }}" min="1" required oninput="calcTotal()"></div>
                <div class="col-md-3"><label class="form-label">Unit Price (₹) *</label><input type="number" class="form-control" name="unit_price" id="unitPriceInput" value="{{ old('unit_price', $selectedQuotation?->unit_price) }}" step="0.01" min="0" required oninput="calcTotal()"></div>
                <div class="col-md-3"><label class="form-label">Tax Amount (₹)</label><input type="number" class="form-control" name="tax_amount" id="taxAmtInput" value="{{ old('tax_amount',0) }}" step="0.01" min="0" oninput="calcTotal()"></div>
                <div class="col-md-4"><label class="form-label">Grand Total</label><input class="form-control bg-light fw-bold" id="totalDisplay" readonly value="0.00"><input type="hidden" name="grand_total" id="grandTotal"></div>

                <div class="col-md-3">
                    <label class="form-label">Demand Request</label>
                    <select class="form-select" name="demand_request_id">
                        <option value="">— None —</option>
                        @foreach($demands as $dr)
                        <option value="{{ $dr->id }}" {{ old('demand_request_id')==$dr->id?'selected':'' }}>{{ $dr->request_number }} — {{ $dr->client?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">RFQ</label>
                    <select class="form-select" name="rfq_id">
                        <option value="">— None —</option>
                        @foreach($rfqs as $rfq)
                        <option value="{{ $rfq->id }}" {{ old('rfq_id', $selectedQuotation?->rfq_id)==$rfq->id?'selected':'' }}>{{ $rfq->rfq_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Payment Terms</label><input class="form-control" name="payment_terms" value="{{ old('payment_terms','Net 30') }}"></div>
                <div class="col-md-4"><label class="form-label">Warranty</label><input class="form-control" name="warranty_months" placeholder="e.g. 12 months" value="{{ old('warranty_months') }}"></div>
                <div class="col-md-4"><label class="form-label">Delivery Address</label><input class="form-control" name="delivery_address" value="{{ old('delivery_address') }}"></div>
                <div class="col-12"><label class="form-label">Special Instructions</label><textarea class="form-control" name="special_instructions" rows="2">{{ old('special_instructions') }}</textarea></div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" name="action" value="save" class="btn btn-outline-secondary"><i class="bi bi-save"></i> Save Draft</button>
                <button type="submit" name="action" value="approve" class="btn btn-primary"><i class="bi bi-check-circle"></i> Create &amp; Approve</button>
                <a href="{{ route('procurement.purchase-orders') }}" class="btn btn-link text-muted">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection

@push('scripts')
<script>
function calcTotal() {
    const qty = parseFloat(document.getElementById('qtyInput').value) || 0;
    const unit = parseFloat(document.getElementById('unitPriceInput').value) || 0;
    const tax = parseFloat(document.getElementById('taxAmtInput').value) || 0;
    const total = (qty * unit) + tax;
    document.getElementById('totalDisplay').value = total.toFixed(2);
    document.getElementById('grandTotal').value = total.toFixed(2);
}
document.addEventListener('DOMContentLoaded', calcTotal);
</script>
@endpush
