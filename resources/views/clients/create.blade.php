@extends('layouts.main')
@section('title','Add Client')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
    <li class="breadcrumb-item active">Add Client</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-briefcase me-2"></i>New Client</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('clients.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-7">
                    <label class="form-label">Client Name *</label>
                    <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Client Code *</label>
                    <input class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" placeholder="e.g. CLI-002" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Industry</label>
                    <input class="form-control" name="industry" value="{{ old('industry') }}">
                </div>
                <div class="col-md-4"><label class="form-label">Contact Person</label><input class="form-control" name="contact_person" value="{{ old('contact_person') }}"></div>
                <div class="col-md-4"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone') }}"></div>
                <div class="col-md-4"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email') }}"></div>
                <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2">{{ old('address') }}</textarea></div>
                <div class="col-md-4"><label class="form-label">City</label><input class="form-control" name="city" value="{{ old('city') }}"></div>
                <div class="col-md-4"><label class="form-label">State</label><input class="form-control" name="state" value="{{ old('state') }}"></div>
                <div class="col-md-2"><label class="form-label">Pincode</label><input class="form-control" name="pincode" value="{{ old('pincode') }}"></div>
                <div class="col-md-2"><label class="form-label">GSTIN</label><input class="form-control" name="gstin" value="{{ old('gstin') }}"></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea></div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Client</button>
                <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
