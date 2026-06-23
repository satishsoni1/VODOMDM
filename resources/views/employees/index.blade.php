@extends('layouts.main')
@section('title','Employees')
@section('breadcrumb')<li class="breadcrumb-item active">Employees</li>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>Employee Directory</h5>
    <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Employee</a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><input class="form-control form-control-sm" name="q" placeholder="Name / Code / Phone" value="{{ request('q') }}"></div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="client_id">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    @foreach(['active','inactive','resigned','terminated','on_leave'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input class="form-control form-control-sm" name="region" placeholder="Region" value="{{ request('region') }}"></div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-dark">
                <tr><th>Emp Code</th><th>Name</th><th>Phone</th><th>Client</th><th>Region</th><th>HQ</th><th>Manager</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td class="fw-bold font-monospace"><a href="{{ route('employees.show',$emp) }}" class="text-decoration-none">{{ $emp->employee_code }}</a></td>
                    <td><a href="{{ route('employees.show',$emp) }}" class="text-decoration-none">{{ $emp->name }}</a></td>
                    <td class="font-monospace small">{{ $emp->phone }}</td>
                    <td class="small">{{ $emp->client?->name ?? '—' }}</td>
                    <td class="small">{{ $emp->region ?? '—' }}</td>
                    <td class="small">{{ $emp->hq ?? '—' }}</td>
                    <td class="small">{{ $emp->manager_name ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ match($emp->status){ 'active'=>'success','resigned'=>'warning text-dark','terminated'=>'danger',default=>'secondary' } }}">
                            {{ ucwords(str_replace('_',' ',$emp->status)) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('employees.show',$emp) }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-1"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('employees.edit',$emp) }}" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-1"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">No employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())<div class="card-footer">{{ $employees->links() }}</div>@endif
</div>
@endsection
