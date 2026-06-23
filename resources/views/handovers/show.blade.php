@extends('layouts.main')
@section('title','Handover — '.$handover->handover_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('handovers.index') }}">Handovers</a></li>
    <li class="breadcrumb-item active">{{ $handover->handover_number }}</li>
@endsection

@section('content')
@php
$hBadge = match($handover->status){'activated'=>'success','assigned'=>'primary','returned'=>'info',default=>'secondary'};
@endphp

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h5 class="fw-bold mb-0">{{ $handover->handover_number }}
            <span class="badge ms-2 bg-{{ $hBadge }}">{{ ucfirst($handover->status) }}</span>
        </h5>
        <p class="text-muted small mb-0">Recorded by {{ $handover->handedOverBy?->name }} on {{ $handover->handover_date?->format('d M Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        @if(!$handover->acknowledgement_received)
        <form method="POST" action="{{ route('handovers.update',$handover) }}">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="{{ $handover->status }}">
            <input type="hidden" name="acknowledgement_received" value="1">
            <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check-circle me-1"></i>Mark Acknowledged</button>
        </form>
        @endif
        @if($handover->status === 'assigned')
        <form method="POST" action="{{ route('handovers.update',$handover) }}">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="activated">
            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-lightning me-1"></i>Activate</button>
        </form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header"><strong>Handover Info</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Handover #</td><td class="font-monospace">{{ $handover->handover_number }}</td></tr>
                    <tr><td class="text-muted">Date</td><td>{{ $handover->handover_date?->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Location</td><td>{{ $handover->handover_location ?? '—' }}</td></tr>
                    <tr><td class="text-muted">City</td><td>{{ $handover->handover_city ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Condition</td><td>{{ ucfirst($handover->condition_at_handover) }}</td></tr>
                    <tr><td class="text-muted">Accessories</td><td>{{ $handover->accessories_handed ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Acknowledged</td>
                        <td>
                            @if($handover->acknowledgement_received)
                                <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>{{ $handover->acknowledged_at?->format('d M Y H:i') }}</span>
                            @else
                                <span class="text-warning"><i class="bi bi-clock me-1"></i>Pending</span>
                            @endif
                        </td>
                    </tr>
                    @if($handover->dispatchBatch)
                    <tr><td class="text-muted">Dispatch Batch</td><td><a href="{{ route('dispatches.show',$handover->dispatchBatch) }}" class="font-monospace text-decoration-none">{{ $handover->dispatchBatch->dispatch_number }}</a></td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-header"><strong>Device</strong></div>
            <div class="card-body">
                @if($handover->device)
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="fw-bold font-monospace mb-1">{{ $handover->device->asset_tag }}</p>
                        <p class="mb-1 small">{{ $handover->device->model?->brand?->name }} {{ $handover->device->model?->model_name }}</p>
                        <p class="mb-0 small text-muted">S/N: {{ $handover->device->serial_number }}</p>
                    </div>
                    <a href="{{ route('devices.show',$handover->device) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View Device</a>
                </div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Employee</strong></div>
            <div class="card-body">
                @if($handover->employee)
                <p class="fw-bold mb-1">{{ $handover->employee->name }} <span class="text-muted fw-normal small">({{ $handover->employee->employee_code }})</span></p>
                <p class="mb-1 small">{{ $handover->employee->designation }} — {{ $handover->employee->department }}</p>
                @if($handover->employee->phone)<p class="mb-0 small text-muted"><i class="bi bi-telephone me-1"></i>{{ $handover->employee->phone }}</p>@endif
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Client</strong></div>
            <div class="card-body">
                <p class="fw-bold mb-1">{{ $handover->client?->name }}</p>
                @if($handover->project)<p class="mb-0 small text-muted">Project: {{ $handover->project->name }}</p>@endif
            </div>
        </div>
    </div>
</div>

@if($handover->remarks)
<div class="card mt-3">
    <div class="card-header"><strong>Remarks</strong></div>
    <div class="card-body"><p class="mb-0 small">{{ $handover->remarks }}</p></div>
</div>
@endif
@endsection
