@extends('layouts.main')
@section('title','New GRN')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventory</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventory.grn') }}">GRNs</a></li>
    <li class="breadcrumb-item active">New GRN</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-box-seam me-2"></i>New Goods Receipt Note</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('inventory.grn.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-7">
                    <label class="form-label">Purchase Order *</label>
                    <select class="form-select @error('purchase_order_id') is-invalid @enderror" name="purchase_order_id" id="poSelect" required onchange="loadPoDetails(this)">
                        <option value="">— Select PO —</option>
                        @foreach($purchaseOrders as $po)
                        <option value="{{ $po->id }}" data-qty="{{ $po->quantity }}" {{ old('purchase_order_id', $selectedPo?->id)==$po->id?'selected':'' }}>
                            {{ $po->po_number }} — {{ $po->vendor?->name }} ({{ $po->quantity }} units)
                        </option>
                        @endforeach
                    </select>
                    @error('purchase_order_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3"><label class="form-label">Received Date *</label><input type="date" class="form-control" name="received_date" value="{{ old('received_date', now()->format('Y-m-d')) }}" required></div>
                <div class="col-md-2">
                    <label class="form-label">Warehouse *</label>
                    <select class="form-select @error('location_id') is-invalid @enderror" name="location_id" required>
                        <option value="">— Select —</option>
                        @foreach($locations as $wh)
                        <option value="{{ $wh->id }}" {{ old('location_id')==$wh->id?'selected':'' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4"><label class="form-label">Quantity Received *</label><input type="number" class="form-control @error('quantity_received') is-invalid @enderror" name="quantity_received" id="qtyReceived" value="{{ old('quantity_received') }}" min="1" required oninput="calcRejected()">@error('quantity_received')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-4"><label class="form-label">Quantity Accepted *</label><input type="number" class="form-control @error('quantity_accepted') is-invalid @enderror" name="quantity_accepted" id="qtyAccepted" value="{{ old('quantity_accepted') }}" min="0" required oninput="calcRejected()">@error('quantity_accepted')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-4"><label class="form-label">Quantity Rejected</label><input type="number" class="form-control bg-light" name="quantity_rejected" id="qtyRejected" value="{{ old('quantity_rejected',0) }}" min="0" readonly></div>
                <div class="col-md-6"><label class="form-label">Invoice Number</label><input class="form-control" name="invoice_number" value="{{ old('invoice_number') }}"></div>
                <div class="col-md-6"><label class="form-label">Delivery Challan Number</label><input class="form-control" name="delivery_challan_number" value="{{ old('delivery_challan_number') }}"></div>
                <div class="col-12"><label class="form-label">Inspection Remarks</label><textarea class="form-control" name="remarks" rows="3" placeholder="QC observations, damages found, missing accessories, seal condition, etc.">{{ old('remarks') }}</textarea></div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Create GRN</button>
                <a href="{{ route('inventory.grn') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection

@push('scripts')
<script>
function loadPoDetails(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (sel.value && opt.dataset.qty) {
        document.getElementById('qtyReceived').value = opt.dataset.qty;
        document.getElementById('qtyAccepted').value = opt.dataset.qty;
        document.getElementById('qtyRejected').value = 0;
    }
}
function calcRejected() {
    const r = parseInt(document.getElementById('qtyReceived').value) || 0;
    const a = parseInt(document.getElementById('qtyAccepted').value) || 0;
    document.getElementById('qtyRejected').value = Math.max(0, r - a);
}
document.addEventListener('DOMContentLoaded', () => {
    const poSel = document.getElementById('poSelect');
    if (poSel.value) loadPoDetails(poSel);
});
</script>
@endpush
