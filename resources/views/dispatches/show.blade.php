@extends('layouts.main')
@section('title','Dispatch — '.$dispatch->dispatch_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dispatches.index') }}">Dispatches</a></li>
    <li class="breadcrumb-item active">{{ $dispatch->dispatch_number }}</li>
@endsection

@section('content')
@php
$badge = match($dispatch->status){
    'delivered'=>'success','in_transit'=>'primary','ready'=>'warning text-dark',
    'returned'=>'info','lost'=>'danger',default=>'secondary'
};
@endphp

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h5 class="fw-bold mb-0">{{ $dispatch->dispatch_number }}
            <span class="badge ms-2 bg-{{ $badge }}">{{ ucwords(str_replace('_',' ',$dispatch->status)) }}</span>
        </h5>
        <p class="text-muted small mb-0">Dispatched by {{ $dispatch->dispatchedBy?->name }} on {{ $dispatch->dispatch_date?->format('d M Y') }}</p>
    </div>
    @if(!in_array($dispatch->status,['delivered','returned','lost']))
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateModal">
        <i class="bi bi-pencil me-1"></i>Update Status
    </button>
    @endif
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><strong>Dispatch Details</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Client</td><td>{{ $dispatch->client?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Project</td><td>{{ $dispatch->project?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Courier</td><td>{{ $dispatch->courierPartner?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">AWB #</td><td class="font-monospace small">{{ $dispatch->awb_number ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Tracking #</td><td class="font-monospace small">{{ $dispatch->tracking_number ?? '—' }}</td></tr>
                    <tr><td class="text-muted">From</td><td>{{ $dispatch->fromLocation?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Dispatch Date</td><td>{{ $dispatch->dispatch_date?->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Expected Del.</td><td>{{ $dispatch->expected_delivery_date?->format('d M Y') ?? '—' }}</td></tr>
                    @if($dispatch->actual_delivery_date)
                    <tr><td class="text-muted">Actual Del.</td><td class="text-success">{{ $dispatch->actual_delivery_date->format('d M Y') }}</td></tr>
                    @endif
                    <tr><td class="text-muted">Freight Cost</td><td>{{ $dispatch->freight_cost ? '₹'.number_format($dispatch->freight_cost,2) : '—' }}</td></tr>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><strong>Receiver</strong></div>
            <div class="card-body">
                <p class="mb-1 fw-bold">{{ $dispatch->receiver_name }}</p>
                @if($dispatch->receiver_phone)<p class="mb-1 small"><i class="bi bi-telephone me-1"></i>{{ $dispatch->receiver_phone }}</p>@endif
                @if($dispatch->destination_address)<p class="mb-1 small text-muted">{{ $dispatch->destination_address }}</p>@endif
                @if($dispatch->destination_city)<p class="mb-0 small text-muted">{{ $dispatch->destination_city }}{{ $dispatch->destination_state ? ', '.$dispatch->destination_state : '' }}</p>@endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><strong>Dispatched Devices ({{ $dispatch->items->count() }})</strong></div>
            @if($dispatch->items->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Asset Tag</th><th>Model</th><th>Serial</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($dispatch->items as $i => $item)
                        @php $iBadge = match($item->status){'delivered'=>'success','returned'=>'info',default=>'primary'}; @endphp
                        <tr>
                            <td class="text-muted small">{{ $i+1 }}</td>
                            <td><a href="{{ route('devices.show',$item->device) }}" class="fw-bold font-monospace text-decoration-none">{{ $item->device?->asset_tag }}</a></td>
                            <td class="small">{{ $item->device?->model?->brand?->name }} {{ $item->device?->model?->model_name }}</td>
                            <td class="font-monospace small text-muted">{{ $item->device?->serial_number }}</td>
                            <td><span class="badge bg-{{ $iBadge }}">{{ ucfirst($item->status) }}</span></td>
                            <td><a href="{{ route('devices.show',$item->device) }}" class="btn btn-sm btn-outline-secondary py-0 px-1"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body text-center text-muted py-4">No devices in this batch.</div>
            @endif
        </div>

        @if($dispatch->remarks)
        <div class="card mt-3">
            <div class="card-header"><strong>Remarks</strong></div>
            <div class="card-body"><p class="mb-0 small">{{ $dispatch->remarks }}</p></div>
        </div>
        @endif
    </div>
</div>

{{-- Update Status Modal --}}
@if(!in_array($dispatch->status,['delivered','returned','lost']))
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('dispatches.update',$dispatch) }}">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Update Dispatch Status</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Status *</label>
                            <select class="form-select" name="status" required>
                                @foreach(['ready','in_transit','delivered','returned','lost'] as $s)
                                <option value="{{ $s }}" {{ $dispatch->status===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12"><label class="form-label">AWB Number</label><input class="form-control font-monospace" name="awb_number" value="{{ $dispatch->awb_number }}"></div>
                        <div class="col-12"><label class="form-label">Tracking Number</label><input class="form-control font-monospace" name="tracking_number" value="{{ $dispatch->tracking_number }}"></div>
                        <div class="col-12"><label class="form-label">Actual Delivery Date</label><input type="date" class="form-control" name="actual_delivery_date" value="{{ $dispatch->actual_delivery_date?->format('Y-m-d') }}"></div>
                        <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="2">{{ $dispatch->remarks }}</textarea></div>
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
