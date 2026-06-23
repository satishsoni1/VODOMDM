@extends('layouts.main')
@section('title','Client — '.$client->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
    <li class="breadcrumb-item active">{{ $client->name }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-briefcase me-2"></i>{{ $client->name }}
        <span class="badge bg-secondary ms-2">{{ $client->code }}</span>
        <span class="badge bg-{{ $client->status==='active'?'success':'secondary' }} ms-1">{{ ucfirst($client->status) }}</span>
    </h5>
    <div class="d-flex gap-2">
        <a href="{{ route('clients.edit',$client) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('employees.create', ['client_id'=>$client->id]) }}" class="btn btn-sm btn-success"><i class="bi bi-person-plus"></i> Add Employee</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><strong>Details</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-3">
                    <tr><td class="text-muted">Industry</td><td>{{ $client->industry ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Contact</td><td>{{ $client->contact_person ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Phone</td><td>{{ $client->phone ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Email</td><td>{{ $client->email ?? '—' }}</td></tr>
                    <tr><td class="text-muted">City</td><td>{{ $client->city }}, {{ $client->state }}</td></tr>
                    <tr><td class="text-muted">GSTIN</td><td>{{ $client->gstin ?? '—' }}</td></tr>
                </table>
                @if($client->notes)<div class="text-muted small">{{ $client->notes }}</div>@endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        {{-- Projects --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Projects ({{ $client->projects->count() }})</strong>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                    <i class="bi bi-plus"></i> Add Project
                </button>
            </div>
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Region</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($client->projects as $proj)
                    <tr>
                        <td class="font-monospace fw-bold">{{ $proj->code }}</td>
                        <td>{{ $proj->name }}</td>
                        <td>{{ $proj->region ?? '—' }}</td>
                        <td><span class="badge bg-{{ $proj->status==='active'?'success':'secondary' }}">{{ ucfirst($proj->status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-2">No projects yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Active Employees --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <strong>Active Employees ({{ $client->employees->count() }}+)</strong>
                <a href="{{ route('employees.index', ['client_id'=>$client->id]) }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Phone</th><th>Region</th></tr></thead>
                <tbody>
                    @forelse($client->employees as $emp)
                    <tr>
                        <td><a href="{{ route('employees.show',$emp) }}" class="fw-bold">{{ $emp->employee_code }}</a></td>
                        <td>{{ $emp->name }}</td>
                        <td class="font-monospace small">{{ $emp->phone }}</td>
                        <td>{{ $emp->region ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-2">No employees.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Devices --}}
        @if($client->devices->isNotEmpty())
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong>Devices ({{ $client->devices->count() }}+)</strong>
                <a href="{{ route('devices.index', ['client_id'=>$client->id]) }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light"><tr><th>Asset Tag</th><th>Model</th><th>Employee</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($client->devices as $dev)
                    <tr>
                        <td><a href="{{ route('devices.show',$dev) }}" class="font-monospace fw-bold">{{ $dev->asset_tag }}</a></td>
                        <td>{{ $dev->model?->model_name }}</td>
                        <td>{{ $dev->currentEmployee?->name ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ str_replace('_',' ',$dev->lifecycle_status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Add Project Modal --}}
<div class="modal fade" id="addProjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Project</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="{{ route('clients.projects.store',$client) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Project Name *</label><input class="form-control" name="name" required></div>
                    <div class="mb-3"><label class="form-label">Project Code *</label><input class="form-control" name="code" required placeholder="e.g. ACME-PRJ-002"></div>
                    <div class="mb-3"><label class="form-label">Region</label><input class="form-control" name="region"></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
                    <div class="row g-2">
                        <div class="col"><label class="form-label">Start Date</label><input type="date" class="form-control" name="start_date"></div>
                        <div class="col"><label class="form-label">End Date</label><input type="date" class="form-control" name="end_date"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Add Project</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
