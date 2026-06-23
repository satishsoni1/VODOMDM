@extends('layouts.main')
@section('title','Edit Vendor')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vendors.index') }}">Vendors</a></li>
    <li class="breadcrumb-item"><a href="{{ route('vendors.show',$vendor) }}">{{ $vendor->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-9">
<div class="card">
    <div class="card-header"><strong>Edit Vendor — {{ $vendor->name }}</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('vendors.update',$vendor) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Vendor Name *</label>
                    <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name',$vendor->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Code *</label>
                    <input class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code',$vendor->code) }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        @foreach(['active','inactive','blacklisted'] as $s)
                        <option value="{{ $s }}" {{ old('status',$vendor->status)===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Contact Person</label><input class="form-control" name="contact_person" value="{{ old('contact_person',$vendor->contact_person) }}"></div>
                <div class="col-md-4"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone',$vendor->phone) }}"></div>
                <div class="col-md-4"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email',$vendor->email) }}"></div>
                <div class="col-md-4"><label class="form-label">GSTIN</label><input class="form-control" name="gstin" value="{{ old('gstin',$vendor->gstin) }}"></div>
                <div class="col-md-4"><label class="form-label">PAN</label><input class="form-control" name="pan" value="{{ old('pan',$vendor->pan) }}"></div>
                <div class="col-md-4"><label class="form-label">Payment Terms</label><input class="form-control" name="payment_terms" value="{{ old('payment_terms',$vendor->payment_terms) }}"></div>
                <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2">{{ old('address',$vendor->address) }}</textarea></div>
                <div class="col-md-4"><label class="form-label">City</label><input class="form-control" name="city" value="{{ old('city',$vendor->city) }}"></div>
                <div class="col-md-4"><label class="form-label">State</label><input class="form-control" name="state" value="{{ old('state',$vendor->state) }}"></div>
                <div class="col-md-4"><label class="form-label">Pincode</label><input class="form-control" name="pincode" value="{{ old('pincode',$vendor->pincode) }}"></div>
                <div class="col-md-4"><label class="form-label">Credit Limit (₹)</label><input class="form-control" type="number" name="credit_limit" value="{{ old('credit_limit',$vendor->credit_limit) }}"></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2">{{ old('notes',$vendor->notes) }}</textarea></div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                <a href="{{ route('vendors.show',$vendor) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
