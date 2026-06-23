@extends('layouts.main')
@section('title','Update Handover — '.$handover->handover_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('handovers.index') }}">Handovers</a></li>
    <li class="breadcrumb-item"><a href="{{ route('handovers.show',$handover) }}">{{ $handover->handover_number }}</a></li>
    <li class="breadcrumb-item active">Update</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-6">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-person-check me-2"></i>Update Handover — {{ $handover->handover_number }}</strong></div>
    <div class="card-body">
        <div class="alert alert-light border mb-3 small">
            <strong>Device:</strong> {{ $handover->device?->asset_tag }} &nbsp;|&nbsp;
            <strong>Employee:</strong> {{ $handover->employee?->name }} &nbsp;|&nbsp;
            <strong>Date:</strong> {{ $handover->handover_date?->format('d M Y') }}
        </div>
        <form method="POST" action="{{ route('handovers.update',$handover) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Status *</label>
                    <select class="form-select" name="status" required>
                        @foreach(['assigned','activated','returned'] as $s)
                        <option value="{{ $s }}" {{ $handover->status===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="acknowledgement_received" value="1" id="ackCheck" class="form-check-input" {{ $handover->acknowledgement_received?'checked':'' }}>
                        <label class="form-check-label" for="ackCheck">Acknowledgement Received</label>
                    </div>
                </div>
                <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="3">{{ old('remarks',$handover->remarks) }}</textarea></div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update</button>
                <a href="{{ route('handovers.show',$handover) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
