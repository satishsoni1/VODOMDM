@extends('layouts.main')
@section('title','New Handover')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('handovers.index') }}">Handovers</a></li>
    <li class="breadcrumb-item active">New Handover</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-person-check me-2"></i>New Device Handover</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('handovers.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Device *</label>
                    <select class="form-select @error('device_id') is-invalid @enderror" name="device_id" required>
                        <option value="">— Select Device —</option>
                        @foreach($devices as $dev)
                        <option value="{{ $dev->id }}" {{ old('device_id')==$dev->id?'selected':'' }}>
                            {{ $dev->asset_tag }} — {{ $dev->model?->brand?->name }} {{ $dev->model?->model_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('device_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Employee *</label>
                    <select class="form-select @error('employee_id') is-invalid @enderror" name="employee_id" required>
                        <option value="">— Select Employee —</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id')==$emp->id?'selected':'' }}>
                            {{ $emp->name }} ({{ $emp->employee_code }})
                        </option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">Client *</label>
                    <select class="form-select @error('client_id') is-invalid @enderror" name="client_id" required onchange="filterProjects(this)">
                        <option value="">— Select Client —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Project</label>
                    <select class="form-select" name="client_project_id" id="projectSelect">
                        <option value="">— Select Project —</option>
                        @foreach($clients as $c)
                            @foreach($c->projects as $p)
                            <option value="{{ $p->id }}" data-client="{{ $c->id }}" {{ old('client_project_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Handover Date *</label>
                    <input type="date" class="form-control @error('handover_date') is-invalid @enderror" name="handover_date" value="{{ old('handover_date', now()->format('Y-m-d')) }}" required>
                    @error('handover_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Dispatch Batch (optional)</label>
                    <select class="form-select" name="dispatch_batch_id">
                        <option value="">— None —</option>
                        @foreach($batches as $b)
                        <option value="{{ $b->id }}" {{ old('dispatch_batch_id')==$b->id?'selected':'' }}>{{ $b->dispatch_number }} — {{ $b->client?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Handover Location</label><input class="form-control" name="handover_location" value="{{ old('handover_location') }}" placeholder="Office / Site name"></div>
                <div class="col-md-4"><label class="form-label">City</label><input class="form-control" name="handover_city" value="{{ old('handover_city') }}"></div>
                <div class="col-md-4">
                    <label class="form-label">Device Condition *</label>
                    <select class="form-select @error('condition_at_handover') is-invalid @enderror" name="condition_at_handover" required>
                        @foreach(['new','good','fair','poor'] as $c)
                        <option value="{{ $c }}" {{ old('condition_at_handover')===$c?'selected':'' }}>{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                    @error('condition_at_handover')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12"><label class="form-label">Accessories Handed</label><input class="form-control" name="accessories_handed" value="{{ old('accessories_handed') }}" placeholder="Charger, Box, Manual, etc."></div>
                <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="2">{{ old('remarks') }}</textarea></div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Record Handover</button>
                <a href="{{ route('handovers.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection

@push('scripts')
<script>
function filterProjects(sel) {
    const cid = sel.value;
    document.querySelectorAll('#projectSelect option[data-client]').forEach(o => {
        o.hidden = cid && o.dataset.client !== cid;
    });
    document.getElementById('projectSelect').value = '';
}
</script>
@endpush
