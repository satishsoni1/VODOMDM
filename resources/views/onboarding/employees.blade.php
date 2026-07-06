@extends('layouts.main')
@section('title','Onboard — '.$client->name.' — Employees')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
    <li class="breadcrumb-item"><a href="{{ route('clients.show', $client) }}">{{ $client->name }}</a></li>
    <li class="breadcrumb-item active">Onboarding — Employees</li>
@endsection

@section('content')
@include('onboarding._steps', ['current' => 2])

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-3">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header fw-semibold"><i class="bi bi-person-plus me-2"></i>Add Employee to {{ $client->name }}</div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif
                <form method="POST" action="{{ route('onboarding.employees.store', $client) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Employee Code *</label>
                        <input class="form-control" name="employee_code" value="{{ old('employee_code') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input class="form-control" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone *</label>
                        <input class="form-control" name="phone" value="{{ old('phone') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Designation</label>
                        <input class="form-control" name="designation" value="{{ old('designation') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Project</label>
                        <select class="form-select" name="client_project_id">
                            <option value="">— None —</option>
                            @foreach($client->projects as $proj)
                                <option value="{{ $proj->id }}" {{ old('client_project_id')==$proj->id?'selected':'' }}>{{ $proj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Add Employee</button>
                </form>
                <hr>
                <p class="text-muted small mb-1">Onboarding many employees at once?</p>
                <a href="{{ route('employees.import.form') }}" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-cloud-upload"></i> Bulk Import (use company_code = {{ $client->code }})
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Employees Added ({{ $client->employees->count() }})</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Phone</th><th>Designation</th></tr></thead>
                    <tbody>
                        @forelse($client->employees as $emp)
                        <tr>
                            <td class="font-monospace fw-bold">{{ $emp->employee_code }}</td>
                            <td>{{ $emp->name }}</td>
                            <td class="font-monospace small">{{ $emp->phone ?? '—' }}</td>
                            <td>{{ $emp->designation ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No employees added yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Client</a>
            <a href="{{ route('onboarding.devices', $client) }}" class="btn btn-primary">
                Continue to Device Assignment <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
@endsection
