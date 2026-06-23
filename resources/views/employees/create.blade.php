@extends('layouts.main')
@section('title','Add Employee')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">Add Employee</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-10">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-person-plus me-2"></i>New Employee</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('employees.store') }}">
            @csrf
            <h6 class="text-muted fw-bold text-uppercase small mb-3">Basic Info</h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Employee Code *</label>
                    <input class="form-control @error('employee_code') is-invalid @enderror" name="employee_code" value="{{ old('employee_code') }}" required>
                    @error('employee_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">Full Name *</label>
                    <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone *</label>
                    <input class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" required>
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4"><label class="form-label">Alternate Phone</label><input class="form-control" name="alternate_phone" value="{{ old('alternate_phone') }}"></div>
                <div class="col-md-4"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email') }}"></div>
                <div class="col-md-4"><label class="form-label">Designation</label><input class="form-control" name="designation" value="{{ old('designation') }}"></div>
            </div>

            <h6 class="text-muted fw-bold text-uppercase small mb-3 mt-4">Client & Project</h6>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Client</label>
                    <select class="form-select" name="client_id" id="clientSelect" onchange="loadProjects(this.value)">
                        <option value="">— Select Client —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id',request('client_id'))==$c->id?'selected':'' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Project</label>
                    <select class="form-select" name="client_project_id" id="projectSelect">
                        <option value="">— Select Project —</option>
                        @foreach($clients as $c)
                            @foreach($c->projects as $proj)
                            <option value="{{ $proj->id }}" data-client="{{ $c->id }}" {{ old('client_project_id')==$proj->id?'selected':'' }}>{{ $proj->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Joining Date</label><input type="date" class="form-control" name="joining_date" value="{{ old('joining_date') }}"></div>
            </div>

            <h6 class="text-muted fw-bold text-uppercase small mb-3 mt-4">Reporting & Location</h6>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Region</label><input class="form-control" name="region" value="{{ old('region') }}"></div>
                <div class="col-md-3"><label class="form-label">HQ</label><input class="form-control" name="hq" value="{{ old('hq') }}"></div>
                <div class="col-md-3"><label class="form-label">ABM</label><input class="form-control" name="abm" value="{{ old('abm') }}"></div>
                <div class="col-md-3">
                    <label class="form-label">Location</label>
                    <select class="form-select" name="location_id">
                        <option value="">— Select —</option>
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ old('location_id')==$loc->id?'selected':'' }}>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Manager Name</label><input class="form-control" name="manager_name" value="{{ old('manager_name') }}"></div>
                <div class="col-md-4"><label class="form-label">Manager Phone</label><input class="form-control" name="manager_phone" value="{{ old('manager_phone') }}"></div>
                <div class="col-md-4"><label class="form-label">Manager Email</label><input class="form-control" type="email" name="manager_email" value="{{ old('manager_email') }}"></div>
            </div>

            <h6 class="text-muted fw-bold text-uppercase small mb-3 mt-4">Address</h6>
            <div class="row g-3">
                <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2">{{ old('address') }}</textarea></div>
                <div class="col-md-4"><label class="form-label">City</label><input class="form-control" name="city" value="{{ old('city') }}"></div>
                <div class="col-md-4"><label class="form-label">State</label><input class="form-control" name="state" value="{{ old('state') }}"></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea></div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Employee</button>
                <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection

@push('scripts')
<script>
function loadProjects(clientId) {
    const sel = document.getElementById('projectSelect');
    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return;
        opt.style.display = (!clientId || opt.dataset.client === clientId) ? '' : 'none';
    });
    sel.value = '';
}
// Run on page load if client is pre-selected
document.addEventListener('DOMContentLoaded', () => {
    const v = document.getElementById('clientSelect').value;
    if (v) loadProjects(v);
});
</script>
@endpush
