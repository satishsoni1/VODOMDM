@extends('layouts.main')
@section('title','GRN — '.$grn->grn_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventory</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventory.grn') }}">GRNs</a></li>
    <li class="breadcrumb-item active">{{ $grn->grn_number }}</li>
@endsection

@section('content')
@php
    $registeredCount = $grn->devices->count();
    $pendingReg = max(0, $grn->quantity_accepted - $registeredCount);
    $grnBadge = match($grn->status){ 'accepted'=>'success','rejected'=>'danger','partially_accepted'=>'warning text-dark','qc_done'=>'info',default=>'secondary' };
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold mb-0">{{ $grn->grn_number }}
            <span class="badge ms-2 bg-{{ $grnBadge }}">{{ ucwords(str_replace('_',' ',$grn->status)) }}</span>
        </h5>
        <p class="text-muted mb-0 small">
            PO: <a href="{{ route('procurement.purchase-orders.show',$grn->purchaseOrder) }}" class="font-monospace">{{ $grn->purchaseOrder?->po_number }}</a>
            &mdash; {{ $grn->vendor?->name }}
        </p>
    </div>
    @if($pendingReg > 0)
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#singleRegModal"><i class="bi bi-plus-circle"></i> Register One</button>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#bulkRegModal"><i class="bi bi-grid-1x2"></i> Bulk Register ({{ $pendingReg }} left)</button>
    </div>
    @endif
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><strong>GRN Details</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted w-45">Received Date</td><td>{{ $grn->received_date ? \Carbon\Carbon::parse($grn->received_date)->format('d M Y') : '—' }}</td></tr>
                    <tr><td class="text-muted">Warehouse</td><td>{{ $grn->location?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Qty Ordered</td><td>{{ $grn->quantity_ordered }}</td></tr>
                    <tr><td class="text-muted">Qty Received</td><td>{{ $grn->quantity_received }}</td></tr>
                    <tr><td class="text-muted">Qty Accepted</td><td class="text-success fw-bold">{{ $grn->quantity_accepted }}</td></tr>
                    <tr><td class="text-muted">Qty Rejected</td><td class="{{ $grn->quantity_rejected ? 'text-danger fw-bold' : '' }}">{{ $grn->quantity_rejected }}</td></tr>
                    <tr><td class="text-muted">Devices Reg.</td><td class="{{ $pendingReg > 0 ? 'text-warning fw-bold' : 'text-success' }}">{{ $registeredCount }} / {{ $grn->quantity_accepted }}</td></tr>
                    @if($grn->invoice_number)<tr><td class="text-muted">Invoice #</td><td class="font-monospace small">{{ $grn->invoice_number }}</td></tr>@endif
                    @if($grn->delivery_challan_number)<tr><td class="text-muted">Challan #</td><td class="font-monospace small">{{ $grn->delivery_challan_number }}</td></tr>@endif
                    <tr><td class="text-muted">Received By</td><td>{{ $grn->receiver?->name ?? '—' }}</td></tr>
                </table>
            </div>
        </div>

        @if($grn->remarks)
        <div class="card">
            <div class="card-header"><strong>Inspection Remarks</strong></div>
            <div class="card-body"><p class="mb-0 small">{{ $grn->remarks }}</p></div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Registered Devices ({{ $registeredCount }})</strong>
                @if($pendingReg > 0)
                <span class="badge bg-warning text-dark">{{ $pendingReg }} pending</span>
                @else
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>All registered</span>
                @endif
            </div>
            @if($grn->devices->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Asset Tag</th><th>Serial Number</th><th>IMEI 1</th><th>Model</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($grn->devices as $i => $dev)
                        @php $devBadge = match($dev->lifecycle_status){ 'in_stock'=>'success','qc_pending'=>'warning text-dark','received'=>'secondary',default=>'info' }; @endphp
                        <tr>
                            <td class="text-muted small">{{ $i+1 }}</td>
                            <td><a href="{{ route('devices.show',$dev) }}" class="fw-bold font-monospace text-decoration-none">{{ $dev->asset_tag }}</a></td>
                            <td class="font-monospace small">{{ $dev->serial_number ?? '—' }}</td>
                            <td class="font-monospace small">{{ $dev->imei1 ?? '—' }}</td>
                            <td class="small">{{ $dev->model?->brand?->name }} {{ $dev->model?->model_name }}</td>
                            <td><span class="badge bg-{{ $devBadge }}">{{ str_replace('_',' ',$dev->lifecycle_status) }}</span></td>
                            <td><a href="{{ route('devices.show',$dev) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body text-center text-muted py-4">
                <i class="bi bi-box fs-1 d-block mb-2 opacity-25"></i>
                No devices registered yet. Use the buttons above to register devices.
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Single Device Registration Modal --}}
@if($pendingReg > 0)
<div class="modal fade" id="singleRegModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('inventory.grn.device.store', $grn) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Register Device</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Asset Tag *</label><input class="form-control font-monospace" name="asset_tag" required placeholder="AT-2026-00001"></div>
                        <div class="col-md-4"><label class="form-label">Serial Number *</label><input class="form-control font-monospace" name="serial_number" required></div>
                        <div class="col-md-4"><label class="form-label">IMEI 1</label><input class="form-control font-monospace" name="imei1" maxlength="15"></div>
                        <div class="col-md-4"><label class="form-label">IMEI 2</label><input class="form-control font-monospace" name="imei2" maxlength="15"></div>
                        <div class="col-md-5">
                            <label class="form-label">Device Model *</label>
                            <select class="form-select" name="device_model_id" required>
                                <option value="">— Select —</option>
                                @foreach($deviceModels as $model)
                                <option value="{{ $model->id }}">{{ $model->brand?->name }} {{ $model->model_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Color</label><input class="form-control" name="color" placeholder="e.g. Black"></div>
                        <div class="col-md-4"><label class="form-label">Box Number</label><input class="form-control" name="box_number" placeholder="Box / packing label"></div>
                        <div class="col-md-4"><label class="form-label">Warranty (months)</label><input type="number" class="form-control" name="warranty_months" min="0" value="{{ $grn->purchaseOrder?->warranty_months }}"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Register</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Bulk Registration Modal --}}
<div class="modal fade" id="bulkRegModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="{{ route('inventory.grn.device.bulk', $grn) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Device Registration ({{ $pendingReg }} devices)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3 pb-3 border-bottom align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Model for All *</label>
                            <select class="form-select" name="device_model_id" id="defaultModel" required>
                                <option value="">— Select —</option>
                                @foreach($deviceModels as $model)
                                <option value="{{ $model->id }}">{{ $model->brand?->name }} {{ $model->model_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2"><label class="form-label">Default Color</label><input class="form-control" id="defaultColor" placeholder="e.g. Black"></div>
                        <div class="col-md-2"><label class="form-label">Warranty (mo.)</label><input type="number" class="form-control" id="defaultWarranty" value="{{ $grn->purchaseOrder?->warranty_months }}"></div>
                        <div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-outline-secondary w-100" onclick="applyDefaults()"><i class="bi bi-arrow-down-circle"></i> Apply to All</button></div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm" id="bulkTable">
                            <thead class="table-light">
                                <tr><th>#</th><th>Asset Tag *</th><th>Serial Number *</th><th>IMEI 1</th><th>IMEI 2</th><th>Color</th><th>Warranty (mo.)</th></tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < $pendingReg; $i++)
                                <tr>
                                    <td class="text-muted align-middle small">{{ $registeredCount + $i + 1 }}</td>
                                    <td><input class="form-control form-control-sm font-monospace" name="devices[{{ $i }}][asset_tag]" required placeholder="AT-"></td>
                                    <td><input class="form-control form-control-sm font-monospace" name="devices[{{ $i }}][serial_number]" required></td>
                                    <td><input class="form-control form-control-sm font-monospace" name="devices[{{ $i }}][imei1]" maxlength="15"></td>
                                    <td><input class="form-control form-control-sm font-monospace" name="devices[{{ $i }}][imei2]" maxlength="15"></td>
                                    <td><input class="form-control form-control-sm color-field" name="devices[{{ $i }}][color]"></td>
                                    <td><input type="number" class="form-control form-control-sm warranty-field" name="devices[{{ $i }}][warranty_months]" min="0"></td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-all"></i> Register All</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function applyDefaults() {
    const color = document.getElementById('defaultColor').value;
    const warranty = document.getElementById('defaultWarranty').value;
    if (color) document.querySelectorAll('#bulkTable .color-field').forEach(el => el.value = color);
    if (warranty) document.querySelectorAll('#bulkTable .warranty-field').forEach(el => el.value = warranty);
}
</script>
@endpush
