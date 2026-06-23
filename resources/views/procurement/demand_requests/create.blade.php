@extends('layouts.main')
@section('title','New Demand Request')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procurement.demand-requests') }}">Demand Requests</a></li>
    <li class="breadcrumb-item active">New</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-9">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-file-earmark-plus me-2"></i>New Demand Request</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('procurement.demand-requests.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">For Client *</label>
                    <select class="form-select @error('client_id') is-invalid @enderror" name="client_id" required>
                        <option value="">— Select Client —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Device Model</label>
                    <select class="form-select" name="device_model_id">
                        <option value="">— Select Model —</option>
                        @foreach($deviceModels as $model)
                        <option value="{{ $model->id }}" {{ old('device_model_id')==$model->id?'selected':'' }}>{{ $model->brand?->name }} {{ $model->model_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Required By *</label><input type="date" class="form-control @error('required_date') is-invalid @enderror" name="required_date" value="{{ old('required_date') }}" required>@error('required_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-2"><label class="form-label">Quantity *</label><input type="number" class="form-control @error('quantity') is-invalid @enderror" name="quantity" value="{{ old('quantity',1) }}" min="1" required>@error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label">Budget Amount (₹)</label><input type="number" class="form-control" name="budget_amount" value="{{ old('budget_amount') }}" step="0.01" min="0"></div>
                <div class="col-md-3"><label class="form-label">Division</label><input class="form-control" name="division" value="{{ old('division') }}"></div>
                <div class="col-md-4"><label class="form-label">Region</label><input class="form-control" name="region" value="{{ old('region') }}"></div>
                <div class="col-12"><label class="form-label">Device Specification *</label><textarea class="form-control @error('device_specification') is-invalid @enderror" name="device_specification" rows="3" required placeholder="Detailed device requirements: brand preference, OS version, storage, features, accessories needed, etc.">{{ old('device_specification') }}</textarea>@error('device_specification')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label class="form-label">Justification</label><textarea class="form-control" name="justification" rows="2" placeholder="Business justification for this request">{{ old('justification') }}</textarea></div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" name="action" value="save" class="btn btn-outline-secondary"><i class="bi bi-save"></i> Save Draft</button>
                <button type="submit" name="action" value="submit" class="btn btn-primary"><i class="bi bi-send"></i> Submit for Approval</button>
                <a href="{{ route('procurement.demand-requests') }}" class="btn btn-link text-muted">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
