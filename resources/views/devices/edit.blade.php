@extends('layouts.main')
@section('title','Edit Device — '.$device->asset_tag)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('devices.index') }}">Devices</a></li>
    <li class="breadcrumb-item"><a href="{{ route('devices.show',$device) }}">{{ $device->asset_tag }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-phone me-2"></i>Edit Device — {{ $device->asset_tag }}</strong>
        <span class="badge bg-info">{{ str_replace('_',' ',$device->lifecycle_status) }}</span>
    </div>
    <div class="card-body">
        <div class="alert alert-light border small mb-3">
            <strong>Serial:</strong> <span class="font-monospace">{{ $device->serial_number }}</span>
            @if($device->imei1) &nbsp;|&nbsp; <strong>IMEI 1:</strong> <span class="font-monospace">{{ $device->imei1 }}</span>@endif
            @if($device->imei2) &nbsp;|&nbsp; <strong>IMEI 2:</strong> <span class="font-monospace">{{ $device->imei2 }}</span>@endif
        </div>

        <form method="POST" action="{{ route('devices.update',$device) }}">
            @csrf @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Device Model *</label>
                    <select class="form-select @error('device_model_id') is-invalid @enderror" name="device_model_id" required>
                        <option value="">— Select Model —</option>
                        @foreach($models as $model)
                        <option value="{{ $model->id }}" {{ old('device_model_id',$device->device_model_id)==$model->id?'selected':'' }}>{{ $model->brand?->name }} {{ $model->model_name }}</option>
                        @endforeach
                    </select>
                    @error('device_model_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Condition *</label>
                    <select class="form-select @error('condition') is-invalid @enderror" name="condition" required>
                        @foreach(['new','good','fair','poor','damaged'] as $c)
                        <option value="{{ $c }}" {{ old('condition',$device->condition)===$c?'selected':'' }}>{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                    @error('condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3"><label class="form-label">Warranty (months)</label><input type="number" class="form-control @error('warranty_months') is-invalid @enderror" name="warranty_months" value="{{ old('warranty_months',$device->warranty_months) }}" min="0">@error('warranty_months')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-4"><label class="form-label">Purchase Price (₹)</label><input type="number" class="form-control @error('purchase_price') is-invalid @enderror" name="purchase_price" value="{{ old('purchase_price',$device->purchase_price) }}" step="0.01" min="0">@error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="3">{{ old('notes',$device->notes) }}</textarea>@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update Device</button>
                <a href="{{ route('devices.show',$device) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
