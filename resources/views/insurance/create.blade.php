@extends('layouts.main')
@section('title','New Insurance Policy')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('insurance.index') }}">Insurance</a></li>
    <li class="breadcrumb-item active">New Policy</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-shield-plus me-2"></i>New Insurance Policy</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('insurance.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Insurance Provider *</label>
                    <select class="form-select @error('insurance_provider_id') is-invalid @enderror" name="insurance_provider_id" required>
                        <option value="">— Select Provider —</option>
                        @foreach($providers as $p)
                        <option value="{{ $p->id }}" {{ old('insurance_provider_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('insurance_provider_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Client</label>
                    <select class="form-select" name="client_id">
                        <option value="">— All Clients / General —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Coverage Type *</label>
                    <input class="form-control @error('coverage_type') is-invalid @enderror" name="coverage_type" value="{{ old('coverage_type') }}" required placeholder="e.g. Mobile Device Insurance, Theft & Damage">
                    @error('coverage_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Premium Amount (₹) *</label>
                    <input type="number" class="form-control @error('premium_amount') is-invalid @enderror" name="premium_amount" value="{{ old('premium_amount') }}" step="0.01" min="0" required>
                    @error('premium_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sum Insured (₹) *</label>
                    <input type="number" class="form-control @error('sum_insured') is-invalid @enderror" name="sum_insured" value="{{ old('sum_insured') }}" step="0.01" min="0" required>
                    @error('sum_insured')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4"><label class="form-label">Start Date *</label><input type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" required>@error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-4"><label class="form-label">Expiry Date *</label><input type="date" class="form-control @error('expiry_date') is-invalid @enderror" name="expiry_date" value="{{ old('expiry_date') }}" required>@error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label class="form-label">Coverage Details</label><textarea class="form-control" name="coverage_details" rows="3" placeholder="What is covered, exclusions, deductibles…">{{ old('coverage_details') }}</textarea></div>
                <div class="col-12"><label class="form-label">Terms & Conditions</label><textarea class="form-control" name="terms" rows="3">{{ old('terms') }}</textarea></div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Policy</button>
                <a href="{{ route('insurance.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
