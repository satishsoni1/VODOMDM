@extends('layouts.main')
@section('title','Employee — '.$employee->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">{{ $employee->name }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-person me-2"></i>{{ $employee->name }}
        <span class="badge bg-secondary ms-2">{{ $employee->employee_code }}</span>
        <span class="badge bg-{{ match($employee->status){ 'active'=>'success','resigned'=>'warning text-dark','terminated'=>'danger',default=>'secondary' } }} ms-1">
            {{ ucwords(str_replace('_',' ',$employee->status)) }}
        </span>
    </h5>
    <div class="d-flex gap-2">
        <a href="{{ route('employees.edit',$employee) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('recovery.create', ['employee_id'=>$employee->id]) }}" class="btn btn-sm btn-danger"><i class="bi bi-arrow-return-left"></i> Initiate Recovery</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><strong>Profile</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted w-45">Phone</td><td class="font-monospace">{{ $employee->phone }}</td></tr>
                    <tr><td class="text-muted">Alt Phone</td><td class="font-monospace">{{ $employee->alternate_phone ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Email</td><td>{{ $employee->email ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Client</td><td>{{ $employee->client?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Project</td><td>{{ $employee->project?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Designation</td><td>{{ $employee->designation ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Region</td><td>{{ $employee->region ?? '—' }}</td></tr>
                    <tr><td class="text-muted">HQ</td><td>{{ $employee->hq ?? '—' }}</td></tr>
                    <tr><td class="text-muted">ABM</td><td>{{ $employee->abm ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Location</td><td>{{ $employee->location?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Joining</td><td>{{ $employee->joining_date?->format('d M Y') ?? '—' }}</td></tr>
                    @if($employee->exit_date)
                    <tr><td class="text-muted">Exit</td><td class="text-danger">{{ $employee->exit_date->format('d M Y') }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><strong>Manager / Reporting</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted w-45">Manager</td><td>{{ $employee->manager_name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Mgr Phone</td><td class="font-monospace">{{ $employee->manager_phone ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Mgr Email</td><td>{{ $employee->manager_email ?? '—' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        {{-- Current Devices --}}
        <div class="card mb-3">
            <div class="card-header"><strong>Assigned Devices ({{ $employee->currentDevices->count() }})</strong></div>
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light"><tr><th>Asset Tag</th><th>Model</th><th>IMEI 1</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($employee->currentDevices as $dev)
                    <tr>
                        <td><a href="{{ route('devices.show',$dev) }}" class="font-monospace fw-bold">{{ $dev->asset_tag }}</a></td>
                        <td>{{ $dev->model?->brand?->name }} {{ $dev->model?->model_name }}</td>
                        <td class="font-monospace small">{{ $dev->imei1 ?? '—' }}</td>
                        <td><span class="badge bg-primary">{{ str_replace('_',' ',$dev->lifecycle_status) }}</span></td>
                        <td><a href="{{ route('devices.show',$dev) }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-1"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-2">No devices currently assigned.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Recovery Cases --}}
        @if($employee->recoveryCases->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header"><strong>Recovery Cases</strong></div>
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light"><tr><th>Case #</th><th>Device</th><th>Trigger</th><th>Status</th><th>Due Date</th></tr></thead>
                <tbody>
                    @foreach($employee->recoveryCases as $rc)
                    <tr>
                        <td><a href="{{ route('recovery.show',$rc) }}" class="fw-bold">{{ $rc->case_number }}</a></td>
                        <td class="font-monospace small">{{ $rc->device?->asset_tag }}</td>
                        <td class="small">{{ ucfirst($rc->trigger_reason) }}</td>
                        <td><span class="badge bg-{{ $rc->status==='recovered'?'success':($rc->status==='escalated'?'danger':'warning text-dark') }}">{{ ucfirst($rc->status) }}</span></td>
                        <td class="{{ $rc->recovery_due_date?->isPast() ? 'text-danger fw-bold' : '' }}">{{ $rc->recovery_due_date?->format('d M Y') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Recent Call Logs --}}
        @if($employee->callLogs->isNotEmpty())
        <div class="card">
            <div class="card-header"><strong>Recent Calls</strong></div>
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light"><tr><th>Date & Time</th><th>Called By</th><th>Outcome</th><th>Next Follow-up</th></tr></thead>
                <tbody>
                    @foreach($employee->callLogs as $call)
                    <tr>
                        <td class="small">{{ $call->call_datetime->format('d M Y H:i') }}</td>
                        <td class="small">{{ $call->calledBy?->name }}</td>
                        <td><span class="badge bg-{{ $call->outcome==='agreed_to_return'?'success':($call->outcome==='refused'?'danger':'secondary') }}">{{ ucwords(str_replace('_',' ',$call->outcome)) }}</span></td>
                        <td class="small">{{ $call->next_follow_up_date?->format('d M Y') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
