@extends('layouts.main')
@section('title','New Recovery Case')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('recovery.index') }}">Recovery</a></li>
    <li class="breadcrumb-item active">New Case</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-arrow-counterclockwise me-2"></i>Open Recovery Case</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('recovery.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Employee *</label>
                    <select class="form-select @error('employee_id') is-invalid @enderror" name="employee_id" required>
                        <option value="">— Select Employee —</option>
                        @foreach($employees as $e)
                        <option value="{{ $e->id }}" data-client="{{ $e->client_id }}" {{ old('employee_id', $selectedEmployee?->id)==$e->id?'selected':'' }}>
                            {{ $e->name }} ({{ $e->employee_code }})
                        </option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Device *</label>
                    <select class="form-select @error('device_id') is-invalid @enderror" name="device_id" required>
                        <option value="">— Select Device —</option>
                        @foreach($devices as $dev)
                        <option value="{{ $dev->id }}" {{ old('device_id')==$dev->id?'selected':'' }}>
                            {{ $dev->asset_tag }} — {{ $dev->model?->model_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('device_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">Client *</label>
                    <select class="form-select @error('client_id') is-invalid @enderror" name="client_id" required>
                        <option value="">— Select Client —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Trigger Reason *</label>
                    <select class="form-select @error('trigger_reason') is-invalid @enderror" name="trigger_reason" required>
                        <option value="">— Select Reason —</option>
                        @foreach(['resignation','termination','transfer','long_leave','other'] as $r)
                        <option value="{{ $r }}" {{ old('trigger_reason')===$r?'selected':'' }}>{{ ucwords(str_replace('_',' ',$r)) }}</option>
                        @endforeach
                    </select>
                    @error('trigger_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Assign To</label>
                    <select class="form-select" name="assigned_to">
                        <option value="">— Unassigned —</option>
                        @foreach($agents as $a)
                        <option value="{{ $a->id }}" {{ old('assigned_to')==$a->id?'selected':'' }}>{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Employee Exit Date</label><input type="date" class="form-control" name="exit_date" value="{{ old('exit_date') }}"></div>
                <div class="col-md-4"><label class="form-label">Recovery Due Date</label><input type="date" class="form-control" name="recovery_due_date" value="{{ old('recovery_due_date') }}"></div>
                <div class="col-12"><label class="form-label">Pickup Address</label><textarea class="form-control" name="pickup_address" rows="2" placeholder="Employee's address for device pickup">{{ old('pickup_address') }}</textarea></div>
                <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="2">{{ old('remarks') }}</textarea></div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Open Case</button>
                <a href="{{ route('recovery.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
