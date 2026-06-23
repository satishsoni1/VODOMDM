@extends('layouts.main')
@section('title','Update Repair — '.$repair->rma_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('repairs.index') }}">Repairs</a></li>
    <li class="breadcrumb-item"><a href="{{ route('repairs.show',$repair) }}">{{ $repair->rma_number }}</a></li>
    <li class="breadcrumb-item active">Update</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-6">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-tools me-2"></i>Update Repair — {{ $repair->rma_number }}</strong></div>
    <div class="card-body">
        <div class="alert alert-light border mb-3 small">
            <strong>Device:</strong> {{ $repair->device?->asset_tag }} &mdash; {{ $repair->device?->model?->model_name }}<br>
            <strong>Service Centre:</strong> {{ $repair->serviceCenter?->name }}<br>
            <strong>Fault:</strong> {{ $repair->fault_description }}
        </div>
        <form method="POST" action="{{ route('repairs.update',$repair) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Status *</label>
                    <select class="form-select" name="status" required>
                        @foreach(['sent','received_at_sc','under_repair','awaiting_parts','repaired','replaced','unrepairable','returned'] as $s)
                        <option value="{{ $s }}" {{ $repair->status===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Outcome</label>
                    <select class="form-select" name="outcome">
                        <option value="">— Not yet determined —</option>
                        @foreach(['repaired','replaced','unrepairable'] as $o)
                        <option value="{{ $o }}" {{ $repair->outcome===$o?'selected':'' }}>{{ ucfirst($o) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Actual Return Date</label><input type="date" class="form-control" name="actual_return_date" value="{{ old('actual_return_date',$repair->actual_return_date?->format('Y-m-d')) }}"></div>
                <div class="col-md-6"><label class="form-label">Actual Cost (₹)</label><input type="number" class="form-control" name="actual_cost" value="{{ old('actual_cost',$repair->actual_cost) }}" step="0.01" min="0"></div>
                <div class="col-12"><label class="form-label">Repair Notes</label><textarea class="form-control" name="repair_notes" rows="4">{{ old('repair_notes',$repair->repair_notes) }}</textarea></div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update</button>
                <a href="{{ route('repairs.show',$repair) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
