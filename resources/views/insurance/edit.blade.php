@extends('layouts.main')
@section('title','Edit Policy — '.$insurance->policy_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('insurance.index') }}">Insurance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('insurance.show',$insurance) }}">{{ $insurance->policy_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-7">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-shield-check me-2"></i>Edit Policy — {{ $insurance->policy_number }}</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('insurance.update',$insurance) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Insurance Provider *</label>
                    <select class="form-select" name="insurance_provider_id" required>
                        @foreach($providers as $p)
                        <option value="{{ $p->id }}" {{ $insurance->insurance_provider_id===$p->id?'selected':'' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Client</label>
                    <select class="form-select" name="client_id">
                        <option value="">— All Clients —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ $insurance->client_id===$c->id?'selected':'' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status *</label>
                    <select class="form-select" name="status" required>
                        @foreach(['active','expiring','expired','cancelled'] as $s)
                        <option value="{{ $s }}" {{ $insurance->status===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Expiry Date</label><input type="date" class="form-control" name="expiry_date" value="{{ old('expiry_date',$insurance->expiry_date?->format('Y-m-d')) }}"></div>
                <div class="col-12"><label class="form-label">Terms & Conditions</label><textarea class="form-control" name="terms" rows="4">{{ old('terms',$insurance->terms) }}</textarea></div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update Policy</button>
                <a href="{{ route('insurance.show',$insurance) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
