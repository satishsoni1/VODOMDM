@extends('layouts.main')
@section('title','Register Device')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('devices.index') }}">Devices</a></li>
    <li class="breadcrumb-item active">Register</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-phone-plus me-2"></i>Register New Device</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('devices.store') }}">
            @csrf

            <h6 class="text-muted fw-bold text-uppercase small mb-3">Identification</h6>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Asset Tag *</label><input class="form-control font-monospace @error('asset_tag') is-invalid @enderror" name="asset_tag" value="{{ old('asset_tag') }}" required placeholder="AT-2026-00001">@error('asset_tag')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label">Serial Number *</label><input class="form-control font-monospace @error('serial_number') is-invalid @enderror" name="serial_number" value="{{ old('serial_number') }}" required>@error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label">IMEI 1</label><input class="form-control font-monospace" name="imei1" value="{{ old('imei1') }}" maxlength="15"></div>
                <div class="col-md-3"><label class="form-label">IMEI 2</label><input class="form-control font-monospace" name="imei2" value="{{ old('imei2') }}" maxlength="15"></div>
            </div>

            <h6 class="text-muted fw-bold text-uppercase small mb-3 mt-4">Device Model</h6>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Model *</label>
                    <select class="form-select @error('device_model_id') is-invalid @enderror" name="device_model_id" required>
                        <option value="">— Select Model —</option>
                        @foreach($models as $model)
                        <option value="{{ $model->id }}" {{ old('device_model_id')==$model->id?'selected':'' }}>{{ $model->brand?->name }} {{ $model->model_name }}</option>
                        @endforeach
                    </select>
                    @error('device_model_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3"><label class="form-label">Color</label><input class="form-control" name="color" value="{{ old('color') }}" placeholder="e.g. Black"></div>
                <div class="col-md-2"><label class="form-label">Box Number</label><input class="form-control" name="box_number" value="{{ old('box_number') }}"></div>
                <div class="col-md-2"><label class="form-label">Warranty (months)</label><input type="number" class="form-control" name="warranty_months" value="{{ old('warranty_months') }}" min="0"></div>
            </div>

            <h6 class="text-muted fw-bold text-uppercase small mb-3 mt-4">Procurement</h6>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Vendor</label>
                    <select class="form-select" name="vendor_id">
                        <option value="">— Select Vendor —</option>
                        @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ old('vendor_id')==$v->id?'selected':'' }}>{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Purchase Date</label><input type="date" class="form-control" name="purchase_date" value="{{ old('purchase_date') }}"></div>
                <div class="col-md-4"><label class="form-label">Purchase Price (₹)</label><input type="number" class="form-control" name="purchase_price" value="{{ old('purchase_price') }}" step="0.01" min="0"></div>
                <div class="col-md-3"><label class="form-label">Warranty Expiry</label><input type="date" class="form-control" name="warranty_expiry" value="{{ old('warranty_expiry') }}"></div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Register Device</button>
                <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
