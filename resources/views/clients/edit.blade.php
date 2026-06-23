@extends('layouts.main')
@section('title','Edit Client')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
    <li class="breadcrumb-item"><a href="{{ route('clients.show',$client) }}">{{ $client->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
<div class="card">
    <div class="card-header"><strong>Edit Client — {{ $client->name }}</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('clients.update',$client) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-7"><label class="form-label">Name *</label><input class="form-control" name="name" value="{{ old('name',$client->name) }}" required></div>
                <div class="col-md-3"><label class="form-label">Code *</label><input class="form-control" name="code" value="{{ old('code',$client->code) }}" required></div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="active" {{ old('status',$client->status)==='active'?'selected':'' }}>Active</option>
                        <option value="inactive" {{ old('status',$client->status)==='inactive'?'selected':'' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Industry</label><input class="form-control" name="industry" value="{{ old('industry',$client->industry) }}"></div>
                <div class="col-md-4"><label class="form-label">Contact Person</label><input class="form-control" name="contact_person" value="{{ old('contact_person',$client->contact_person) }}"></div>
                <div class="col-md-4"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone',$client->phone) }}"></div>
                <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email',$client->email) }}"></div>
                <div class="col-md-6"><label class="form-label">GSTIN</label><input class="form-control" name="gstin" value="{{ old('gstin',$client->gstin) }}"></div>
                <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2">{{ old('address',$client->address) }}</textarea></div>
                <div class="col-md-4"><label class="form-label">City</label><input class="form-control" name="city" value="{{ old('city',$client->city) }}"></div>
                <div class="col-md-4"><label class="form-label">State</label><input class="form-control" name="state" value="{{ old('state',$client->state) }}"></div>
                <div class="col-md-4"><label class="form-label">Pincode</label><input class="form-control" name="pincode" value="{{ old('pincode',$client->pincode) }}"></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2">{{ old('notes',$client->notes) }}</textarea></div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                <a href="{{ route('clients.show',$client) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
