@extends('layouts.main')
@section('title','Update Recovery — '.$recovery->case_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('recovery.index') }}">Recovery</a></li>
    <li class="breadcrumb-item"><a href="{{ route('recovery.show',$recovery) }}">{{ $recovery->case_number }}</a></li>
    <li class="breadcrumb-item active">Update</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-6">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-arrow-counterclockwise me-2"></i>Update Case — {{ $recovery->case_number }}</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('recovery.update',$recovery) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Status *</label>
                    <select class="form-select" name="status" required>
                        @foreach(['open','contacted','pickup_scheduled','recovered','escalated','closed','written_off'] as $s)
                        <option value="{{ $s }}" {{ $recovery->status===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Assign To</label>
                    <select class="form-select" name="assigned_to">
                        <option value="">— Unassigned —</option>
                        @foreach($agents as $a)
                        <option value="{{ $a->id }}" {{ $recovery->assigned_to===$a->id?'selected':'' }}>{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Recovery Due Date</label><input type="date" class="form-control" name="recovery_due_date" value="{{ $recovery->recovery_due_date?->format('Y-m-d') }}"></div>
                <div class="col-12"><label class="form-label">Pickup Scheduled Date</label><input type="date" class="form-control" name="pickup_scheduled_date" value="{{ $recovery->pickup_scheduled_date?->format('Y-m-d') }}"></div>
                <div class="col-12"><label class="form-label">Pickup Address</label><textarea class="form-control" name="pickup_address" rows="2">{{ old('pickup_address',$recovery->pickup_address) }}</textarea></div>
                <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="2">{{ old('remarks',$recovery->remarks) }}</textarea></div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update</button>
                <a href="{{ route('recovery.show',$recovery) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
