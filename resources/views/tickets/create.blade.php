@extends('layouts.main')
@section('title','New Ticket')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
    <li class="breadcrumb-item active">New Ticket</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-ticket-detailed me-2"></i>Raise Support Ticket</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('tickets.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Device</label>
                    <select class="form-select" name="device_id" id="deviceSelect" onchange="autofillFromDevice(this)">
                        <option value="">— Select Device (optional) —</option>
                        @foreach($devices as $dev)
                        <option value="{{ $dev->id }}"
                            data-client="{{ $dev->client_id }}"
                            data-employee="{{ $dev->current_employee_id }}"
                            {{ old('device_id', $selectedDevice?->id)==$dev->id?'selected':'' }}>
                            {{ $dev->asset_tag }} — {{ $dev->model?->model_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Client</label>
                    <select class="form-select" name="client_id" id="clientSelect">
                        <option value="">— Select Client —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select class="form-select" name="employee_id" id="empSelect">
                        <option value="">— Select Employee —</option>
                        @foreach($employees as $e)
                        <option value="{{ $e->id }}" {{ old('employee_id')==$e->id?'selected':'' }}>{{ $e->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Category *</label>
                    <select class="form-select @error('ticket_category_id') is-invalid @enderror" name="ticket_category_id" required>
                        <option value="">— Select Category —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('ticket_category_id')==$cat->id?'selected':'' }}>
                            {{ $cat->name }}@if($cat->sla_hours) (SLA: {{ $cat->sla_hours }}h)@endif
                        </option>
                        @endforeach
                    </select>
                    @error('ticket_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Priority *</label>
                    <select class="form-select @error('priority') is-invalid @enderror" name="priority" required>
                        @foreach(['low','medium','high','critical'] as $p)
                        <option value="{{ $p }}" {{ old('priority','medium')===$p?'selected':'' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                    @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Assign To</label>
                    <select class="form-select" name="assigned_to">
                        <option value="">— Unassigned —</option>
                        @foreach($agents as $a)
                        <option value="{{ $a->id }}" {{ old('assigned_to')==$a->id?'selected':'' }}>{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Subject *</label>
                    <input class="form-control @error('subject') is-invalid @enderror" name="subject" value="{{ old('subject') }}" required placeholder="Brief description of the issue">
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Description *</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="5" required placeholder="Detailed description of the problem…">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Submit Ticket</button>
                <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection

@push('scripts')
<script>
function autofillFromDevice(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt.dataset.client) document.getElementById('clientSelect').value = opt.dataset.client;
    if (opt.dataset.employee) document.getElementById('empSelect').value = opt.dataset.employee;
}
</script>
@endpush
