@extends('layouts.main')
@section('title','Edit Dispatch — '.$dispatch->dispatch_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dispatches.index') }}">Dispatches</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dispatches.show',$dispatch) }}">{{ $dispatch->dispatch_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-6">
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-truck me-2"></i>Update Dispatch — {{ $dispatch->dispatch_number }}</strong>
        @php $badge = match($dispatch->status){'delivered'=>'success','in_transit'=>'primary','ready'=>'warning text-dark','returned'=>'info','lost'=>'danger',default=>'secondary'}; @endphp
        <span class="badge bg-{{ $badge }}">{{ ucwords(str_replace('_',' ',$dispatch->status)) }}</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('dispatches.update',$dispatch) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Status *</label>
                    <select class="form-select" name="status" required>
                        @foreach(['ready','in_transit','delivered','returned','lost'] as $s)
                        <option value="{{ $s }}" {{ $dispatch->status===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">AWB Number</label><input class="form-control font-monospace" name="awb_number" value="{{ old('awb_number',$dispatch->awb_number) }}"></div>
                <div class="col-md-6"><label class="form-label">Tracking Number</label><input class="form-control font-monospace" name="tracking_number" value="{{ old('tracking_number',$dispatch->tracking_number) }}"></div>
                <div class="col-12"><label class="form-label">Actual Delivery Date</label><input type="date" class="form-control" name="actual_delivery_date" value="{{ old('actual_delivery_date',$dispatch->actual_delivery_date?->format('Y-m-d')) }}"></div>
                <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="3">{{ old('remarks',$dispatch->remarks) }}</textarea></div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update</button>
                <a href="{{ route('dispatches.show',$dispatch) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
