@extends('layouts.main')
@section('title','Repair — '.$repair->rma_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('repairs.index') }}">Repairs</a></li>
    <li class="breadcrumb-item active">{{ $repair->rma_number }}</li>
@endsection

@section('content')
@php
$rBadge = match($repair->status){'returned'=>'success','repaired'=>'info','replaced'=>'primary','unrepairable'=>'danger','under_repair'=>'warning text-dark','sent'=>'secondary',default=>'light text-dark'};
$tBadge = match($repair->repair_type){'warranty'=>'success','insurance'=>'primary','paid'=>'warning text-dark',default=>'secondary'};
$overdue = $repair->estimated_return_date && $repair->estimated_return_date->lt(today()) && !in_array($repair->status,['returned','unrepairable']);
@endphp

<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1">{{ $repair->rma_number }}
            <span class="badge ms-2 bg-{{ $rBadge }}">{{ ucwords(str_replace('_',' ',$repair->status)) }}</span>
            <span class="badge ms-1 bg-{{ $tBadge }}">{{ ucfirst($repair->repair_type) }}</span>
            @if($repair->under_warranty)<span class="badge ms-1 bg-success">Warranty</span>@endif
            @if($overdue)<span class="badge ms-1 bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Overdue</span>@endif
        </h5>
        <p class="text-muted small mb-0">Created by {{ $repair->createdBy?->name }} &mdash; {{ $repair->created_at->format('d M Y') }}</p>
    </div>
    @if(!in_array($repair->status,['returned','unrepairable']))
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateModal">
        <i class="bi bi-arrow-repeat me-1"></i>Update Status
    </button>
    @endif
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><strong>Repair Details</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Service Centre</td><td>{{ $repair->serviceCenter?->name }}</td></tr>
                    <tr><td class="text-muted">City</td><td>{{ $repair->serviceCenter?->city }}</td></tr>
                    <tr><td class="text-muted">Contact</td><td>{{ $repair->serviceCenter?->phone ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Sent Date</td><td>{{ $repair->sent_date?->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Est. Return</td><td class="{{ $overdue?'text-danger fw-bold':'' }}">{{ $repair->estimated_return_date?->format('d M Y') ?? '—' }}</td></tr>
                    @if($repair->actual_return_date)
                    <tr><td class="text-muted">Actual Return</td><td class="text-success">{{ $repair->actual_return_date->format('d M Y') }}</td></tr>
                    @endif
                    <tr><td class="text-muted">Est. Cost</td><td>{{ $repair->estimated_cost ? '₹'.number_format($repair->estimated_cost,2) : '—' }}</td></tr>
                    <tr><td class="text-muted">Actual Cost</td><td class="fw-bold">{{ $repair->actual_cost ? '₹'.number_format($repair->actual_cost,2) : '—' }}</td></tr>
                    @if($repair->outcome)
                    <tr><td class="text-muted">Outcome</td><td class="fw-bold">{{ ucfirst($repair->outcome) }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        @if($repair->device)
        <div class="card mb-3">
            <div class="card-header"><strong>Device</strong></div>
            <div class="card-body">
                <p class="fw-bold font-monospace mb-1"><a href="{{ route('devices.show',$repair->device) }}" class="text-decoration-none">{{ $repair->device->asset_tag }}</a></p>
                <p class="small mb-1">{{ $repair->device->model?->brand?->name }} {{ $repair->device->model?->model_name }}</p>
                <p class="small text-muted mb-0">S/N: {{ $repair->device->serial_number }}</p>
            </div>
        </div>
        @endif

        @if($repair->ticket || $repair->insuranceClaim)
        <div class="card">
            <div class="card-header"><strong>Linked Records</strong></div>
            <div class="list-group list-group-flush">
                @if($repair->ticket)
                <a href="{{ route('tickets.show',$repair->ticket) }}" class="list-group-item list-group-item-action small">
                    <i class="bi bi-ticket-detailed me-1 text-primary"></i>{{ $repair->ticket->ticket_number }}
                    <span class="text-muted d-block">{{ Str::limit($repair->ticket->subject,50) }}</span>
                </a>
                @endif
                @if($repair->insuranceClaim)
                <a href="{{ route('insurance.claims') }}" class="list-group-item list-group-item-action small">
                    <i class="bi bi-shield me-1 text-success"></i>{{ $repair->insuranceClaim->claim_number }}
                    <span class="text-muted d-block">{{ $repair->insuranceClaim->incident_type }}</span>
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><strong>Fault & Notes</strong></div>
            <div class="card-body">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Fault Description</h6>
                <p class="mb-3">{{ $repair->fault_description }}</p>
                @if($repair->detailed_notes)
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Detailed Notes</h6>
                <p class="mb-3" style="white-space:pre-wrap;">{{ $repair->detailed_notes }}</p>
                @endif
                @if($repair->repair_notes)
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Repair / Service Notes</h6>
                <p class="mb-0 text-success" style="white-space:pre-wrap;">{{ $repair->repair_notes }}</p>
                @endif
            </div>
        </div>

        @if($repair->replacementDevice)
        <div class="card border-success">
            <div class="card-header bg-success bg-opacity-10 text-success"><strong><i class="bi bi-arrow-left-right me-1"></i>Replacement Device</strong></div>
            <div class="card-body">
                <p class="fw-bold font-monospace mb-1"><a href="{{ route('devices.show',$repair->replacementDevice) }}" class="text-decoration-none">{{ $repair->replacementDevice->asset_tag }}</a></p>
                <p class="small mb-0">{{ $repair->replacementDevice->model?->brand?->name }} {{ $repair->replacementDevice->model?->model_name }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Update Status Modal --}}
@if(!in_array($repair->status,['returned','unrepairable']))
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('repairs.update',$repair) }}">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Update Repair Status</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Status *</label>
                            <select class="form-select" name="status" required>
                                @foreach(['sent','received_at_sc','under_repair','awaiting_parts','repaired','replaced','unrepairable','returned'] as $s)
                                <option value="{{ $s }}" {{ $repair->status===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Outcome</label>
                            <select class="form-select" name="outcome">
                                <option value="">— Select if resolved —</option>
                                @foreach(['repaired','replaced','unrepairable'] as $o)
                                <option value="{{ $o }}" {{ $repair->outcome===$o?'selected':'' }}>{{ ucfirst($o) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Actual Return Date</label><input type="date" class="form-control" name="actual_return_date" value="{{ $repair->actual_return_date?->format('Y-m-d') }}"></div>
                        <div class="col-md-4"><label class="form-label">Actual Cost (₹)</label><input type="number" class="form-control" name="actual_cost" value="{{ $repair->actual_cost }}" step="0.01" min="0"></div>
                        <div class="col-12"><label class="form-label">Repair Notes</label><textarea class="form-control" name="repair_notes" rows="3">{{ $repair->repair_notes }}</textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
