@extends('layouts.main')
@section('title','New Dispatch')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dispatches.index') }}">Dispatches</a></li>
    <li class="breadcrumb-item active">New Dispatch</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-10">
<form method="POST" action="{{ route('dispatches.store') }}">
@csrf

<div class="card mb-3">
    <div class="card-header"><strong><i class="bi bi-truck me-2"></i>New Dispatch Batch</strong></div>
    <div class="card-body">
        <h6 class="text-muted fw-bold text-uppercase small mb-3">Client & Delivery</h6>
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Client *</label>
                <select class="form-select @error('client_id') is-invalid @enderror" name="client_id" id="clientSelect" required onchange="filterProjects(this)">
                    <option value="">— Select Client —</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ old('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Project</label>
                <select class="form-select" name="client_project_id" id="projectSelect">
                    <option value="">— Select Project —</option>
                    @foreach($clients as $c)
                        @foreach($c->projects as $p)
                        <option value="{{ $p->id }}" data-client="{{ $c->id }}" {{ old('client_project_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Dispatch Date *</label>
                <input type="date" class="form-control @error('dispatch_date') is-invalid @enderror" name="dispatch_date" value="{{ old('dispatch_date', now()->format('Y-m-d')) }}" required>
                @error('dispatch_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Courier Partner</label>
                <select class="form-select" name="courier_partner_id">
                    <option value="">— Select Courier —</option>
                    @foreach($couriers as $c)
                    <option value="{{ $c->id }}" {{ old('courier_partner_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">AWB Number</label><input class="form-control font-monospace" name="awb_number" value="{{ old('awb_number') }}" placeholder="Airway Bill Number"></div>
            <div class="col-md-4"><label class="form-label">Tracking Number</label><input class="form-control font-monospace" name="tracking_number" value="{{ old('tracking_number') }}"></div>
            <div class="col-md-5">
                <label class="form-label">From Warehouse *</label>
                <select class="form-select @error('from_location_id') is-invalid @enderror" name="from_location_id" required>
                    <option value="">— Select Location —</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ old('from_location_id')==$loc->id?'selected':'' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
                @error('from_location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3"><label class="form-label">Expected Delivery</label><input type="date" class="form-control" name="expected_delivery_date" value="{{ old('expected_delivery_date') }}"></div>
            <div class="col-md-4"><label class="form-label">Freight Cost (₹)</label><input type="number" class="form-control" name="freight_cost" value="{{ old('freight_cost') }}" step="0.01" min="0"></div>
        </div>

        <h6 class="text-muted fw-bold text-uppercase small mb-3 mt-4">Receiver Details</h6>
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Receiver Name *</label><input class="form-control @error('receiver_name') is-invalid @enderror" name="receiver_name" value="{{ old('receiver_name') }}" required>@error('receiver_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-3"><label class="form-label">Receiver Phone</label><input class="form-control" name="receiver_phone" value="{{ old('receiver_phone') }}"></div>
            <div class="col-md-3"><label class="form-label">City</label><input class="form-control" name="destination_city" value="{{ old('destination_city') }}"></div>
            <div class="col-md-2"><label class="form-label">State</label><input class="form-control" name="destination_state" value="{{ old('destination_state') }}"></div>
            <div class="col-12"><label class="form-label">Destination Address</label><textarea class="form-control" name="destination_address" rows="2">{{ old('destination_address') }}</textarea></div>
            <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="2">{{ old('remarks') }}</textarea></div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Select Devices *</strong>
        <span class="badge bg-primary" id="selectedCount">0 selected</span>
    </div>
    <div class="card-body">
        <input type="text" class="form-control form-control-sm mb-3" id="deviceSearch" placeholder="Search by asset tag, serial, model…">
        @error('device_ids')<div class="alert alert-danger small py-2">{{ $message }}</div>@enderror
        <div class="table-responsive" style="max-height:400px;overflow-y:auto;">
            <table class="table table-sm table-hover" id="deviceTable">
                <thead class="table-light sticky-top">
                    <tr>
                        <th><input type="checkbox" id="checkAll" class="form-check-input"></th>
                        <th>Asset Tag</th>
                        <th>Model</th>
                        <th>Serial</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($availableDevices as $dev)
                    <tr class="device-row">
                        <td><input type="checkbox" name="device_ids[]" value="{{ $dev->id }}" class="form-check-input dev-check" {{ in_array($dev->id, old('device_ids',[])) ? 'checked' : '' }}></td>
                        <td class="font-monospace fw-bold small">{{ $dev->asset_tag }}</td>
                        <td class="small">{{ $dev->model?->brand?->name }} {{ $dev->model?->model_name }}</td>
                        <td class="font-monospace small text-muted">{{ $dev->serial_number }}</td>
                        <td><span class="badge bg-secondary small">{{ str_replace('_',' ',$dev->lifecycle_status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="bi bi-truck me-1"></i>Create Dispatch</button>
    <a href="{{ route('dispatches.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
</form>
</div></div>
@endsection

@push('scripts')
<script>
function filterProjects(sel) {
    const cid = sel.value;
    document.querySelectorAll('#projectSelect option[data-client]').forEach(o => {
        o.hidden = cid && o.dataset.client !== cid;
    });
    document.getElementById('projectSelect').value = '';
}

document.getElementById('checkAll').addEventListener('change', function() {
    document.querySelectorAll('.dev-check:not([hidden])').forEach(c => c.checked = this.checked);
    updateCount();
});

document.querySelectorAll('.dev-check').forEach(c => c.addEventListener('change', updateCount));

function updateCount() {
    const n = document.querySelectorAll('.dev-check:checked').length;
    document.getElementById('selectedCount').textContent = n + ' selected';
}

document.getElementById('deviceSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.device-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

document.addEventListener('DOMContentLoaded', updateCount);
</script>
@endpush
