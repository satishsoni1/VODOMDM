@extends('layouts.main')
@section('title','Clients')
@section('breadcrumb')<li class="breadcrumb-item active">Clients</li>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-briefcase me-2"></i>Client Master</h5>
    <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Client</a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><input class="form-control form-control-sm" name="q" placeholder="Name / Code / Phone" value="{{ request('q') }}"></div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                    <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-dark">
                <tr><th>Code</th><th>Client Name</th><th>Industry</th><th>Contact</th><th>City</th><th>Employees</th><th>Devices</th><th>Projects</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($clients as $c)
                <tr>
                    <td class="fw-bold font-monospace"><a href="{{ route('clients.show',$c) }}" class="text-decoration-none">{{ $c->code }}</a></td>
                    <td><a href="{{ route('clients.show',$c) }}" class="text-decoration-none fw-semibold">{{ $c->name }}</a></td>
                    <td class="text-muted small">{{ $c->industry }}</td>
                    <td class="small">{{ $c->contact_person }}</td>
                    <td>{{ $c->city }}</td>
                    <td><span class="badge bg-info text-dark">{{ $c->employees_count }}</span></td>
                    <td><span class="badge bg-primary">{{ $c->devices_count }}</span></td>
                    <td><span class="badge bg-secondary">{{ $c->projects_count }}</span></td>
                    <td><span class="badge bg-{{ $c->status==='active'?'success':'secondary' }}">{{ ucfirst($c->status) }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('clients.show',$c) }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-1"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('clients.edit',$c) }}" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-1"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-4 text-muted">No clients found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($clients->hasPages())<div class="card-footer">{{ $clients->links() }}</div>@endif
</div>
@endsection
