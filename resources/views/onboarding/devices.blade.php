@extends('layouts.main')
@section('title','Onboard — '.$client->name.' — Devices')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
    <li class="breadcrumb-item"><a href="{{ route('clients.show', $client) }}">{{ $client->name }}</a></li>
    <li class="breadcrumb-item active">Onboarding — Devices</li>
@endsection

@section('content')
@include('onboarding._steps', ['current' => 3])

<div class="card mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-phone me-2"></i>Assign Devices to {{ $client->name }}'s Employees</div>
    <div class="card-body">
        @if($client->employees->isEmpty())
            <div class="alert alert-warning mb-0">
                No employees added yet. <a href="{{ route('onboarding.employees', $client) }}">Go back and add employees first</a>.
            </div>
        @elseif($availableDevices->isEmpty())
            <div class="alert alert-warning mb-0">
                No unassigned devices are available in stock right now. You can still
                <a href="{{ route('devices.import.form') }}">import devices</a> and come back to this step.
            </div>
        @else
        <p class="text-muted small mb-3">
            Pick a device for each employee who needs one (leave blank to skip). Optionally tag a <strong>group</strong>
            label (e.g. team/region) for tracking. For large batches, use the
            <a href="{{ route('employees.bulk-assign.form') }}">CSV bulk-assign tool</a> instead.
        </p>
        <form method="POST" action="{{ route('onboarding.devices.assign', $client) }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr><th>Employee</th><th>Device</th><th>Group</th></tr>
                    </thead>
                    <tbody>
                        @foreach($client->employees as $emp)
                        <tr>
                            <td>
                                <input type="hidden" name="employee_id[]" value="{{ $emp->id }}">
                                <span class="fw-bold">{{ $emp->employee_code }}</span> — {{ $emp->name }}
                                @if($emp->currentDevices->isNotEmpty())
                                    <span class="badge bg-success ms-1">Already has a device</span>
                                @endif
                            </td>
                            <td>
                                <select class="form-select form-select-sm" name="device_id[]">
                                    <option value="">— No device —</option>
                                    @foreach($availableDevices as $device)
                                        <option value="{{ $device->id }}">
                                            {{ $device->asset_tag }} — {{ $device->model?->brand?->name }} {{ $device->model?->model_name }}
                                            ({{ $device->serial_number ?: $device->imei1 }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="group[]" placeholder="e.g. Sales Team">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary mt-2"><i class="bi bi-check-lg"></i> Assign Devices & Continue</button>
        </form>
        @endif
    </div>
</div>

<div class="d-flex justify-content-between">
    <a href="{{ route('onboarding.employees', $client) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    <a href="{{ route('onboarding.finish', $client) }}" class="btn btn-outline-primary">Skip for now <i class="bi bi-arrow-right"></i></a>
</div>
@endsection
