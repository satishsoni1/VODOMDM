@extends('layouts.main')
@section('title','Edit Employee')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employees.show',$employee) }}">{{ $employee->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-10">
<div class="card">
    <div class="card-header"><strong>Edit Employee — {{ $employee->name }}</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('employees.update',$employee) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Employee Code *</label><input class="form-control" name="employee_code" value="{{ old('employee_code',$employee->employee_code) }}" required></div>
                <div class="col-md-4"><label class="form-label">Full Name *</label><input class="form-control" name="name" value="{{ old('name',$employee->name) }}" required></div>
                <div class="col-md-3"><label class="form-label">Phone *</label><input class="form-control" name="phone" value="{{ old('phone',$employee->phone) }}" required></div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        @foreach(['active','inactive','resigned','terminated','on_leave'] as $s)
                        <option value="{{ $s }}" {{ old('status',$employee->status)===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Alternate Phone</label><input class="form-control" name="alternate_phone" value="{{ old('alternate_phone',$employee->alternate_phone) }}"></div>
                <div class="col-md-4"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email',$employee->email) }}"></div>
                <div class="col-md-4"><label class="form-label">Designation</label><input class="form-control" name="designation" value="{{ old('designation',$employee->designation) }}"></div>
                <div class="col-md-5">
                    <label class="form-label">Client</label>
                    <select class="form-select" name="client_id" id="clientSelect" onchange="loadProjects(this.value)">
                        <option value="">— None —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id',$employee->client_id)==$c->id?'selected':'' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Project</label>
                    <select class="form-select" name="client_project_id" id="projectSelect">
                        <option value="">— None —</option>
                        @foreach($clients as $c)
                            @foreach($c->projects as $proj)
                            <option value="{{ $proj->id }}" data-client="{{ $c->id }}" {{ old('client_project_id',$employee->client_project_id)==$proj->id?'selected':'' }}>{{ $proj->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Exit Date</label><input type="date" class="form-control" name="exit_date" value="{{ old('exit_date',$employee->exit_date?->format('Y-m-d')) }}"></div>
                <div class="col-md-3"><label class="form-label">Region</label><input class="form-control" name="region" value="{{ old('region',$employee->region) }}"></div>
                <div class="col-md-3"><label class="form-label">HQ</label><input class="form-control" name="hq" value="{{ old('hq',$employee->hq) }}"></div>
                <div class="col-md-3"><label class="form-label">ABM</label><input class="form-control" name="abm" value="{{ old('abm',$employee->abm) }}"></div>
                <div class="col-md-3">
                    <label class="form-label">Location</label>
                    <select class="form-select" name="location_id">
                        <option value="">— None —</option>
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ old('location_id',$employee->location_id)==$loc->id?'selected':'' }}>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Manager Name</label><input class="form-control" name="manager_name" value="{{ old('manager_name',$employee->manager_name) }}"></div>
                <div class="col-md-4"><label class="form-label">Manager Phone</label><input class="form-control" name="manager_phone" value="{{ old('manager_phone',$employee->manager_phone) }}"></div>
                <div class="col-md-4"><label class="form-label">Manager Email</label><input class="form-control" type="email" name="manager_email" value="{{ old('manager_email',$employee->manager_email) }}"></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2">{{ old('notes',$employee->notes) }}</textarea></div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                <a href="{{ route('employees.show',$employee) }}" class="btn btn-outline-secondary">Cancel</a>
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
}
document.addEventListener('DOMContentLoaded', () => {
    const v = document.getElementById('clientSelect').value;
    if (v) loadProjects(v);
});
</script>
@endpush
