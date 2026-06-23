@extends('layouts.main')
@section('title','Ticket — '.$ticket->ticket_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
    <li class="breadcrumb-item active">{{ $ticket->ticket_number }}</li>
@endsection

@section('content')
@php
$sBadge = match($ticket->status){'resolved'=>'success','closed'=>'dark','open'=>'warning text-dark','assigned'=>'primary','in_progress'=>'info','cancelled'=>'secondary',default=>'light text-dark'};
$pBadge = match($ticket->priority){'critical'=>'danger','high'=>'warning text-dark','medium'=>'info','low'=>'secondary',default=>'secondary'};
$slaBreached = $ticket->sla_due_at && now()->gt($ticket->sla_due_at) && !in_array($ticket->status,['resolved','closed']);
@endphp

<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1">
            <span class="text-muted fw-normal">#</span>{{ $ticket->ticket_number }}
            <span class="badge ms-2 bg-{{ $sBadge }}">{{ ucwords(str_replace('_',' ',$ticket->status)) }}</span>
            <span class="badge ms-1 bg-{{ $pBadge }}">{{ ucfirst($ticket->priority) }}</span>
            @if($slaBreached)<span class="badge ms-1 bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>SLA Breached</span>@endif
        </h5>
        <p class="mb-0 small text-muted">Raised by {{ $ticket->raisedBy?->name }} &mdash; {{ $ticket->created_at->format('d M Y H:i') }}</p>
    </div>
    @if(!in_array($ticket->status,['resolved','closed','cancelled']))
    <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#statusModal"><i class="bi bi-arrow-repeat me-1"></i>Update Status</button>
        @if($ticket->repairOrders->isEmpty())
        <a href="{{ route('repairs.create', ['device_id'=>$ticket->device_id,'ticket_id'=>$ticket->id]) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-tools me-1"></i>Send for Repair</a>
        @endif
    </div>
    @endif
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><strong>Ticket Details</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Category</td><td>{{ $ticket->category?->name }}</td></tr>
                    <tr><td class="text-muted">Assigned To</td><td>{{ $ticket->assignedTo?->name ?? '<em class="text-muted">Unassigned</em>' }}</td></tr>
                    <tr><td class="text-muted">SLA Due</td>
                        <td>
                            @if($ticket->sla_due_at)
                                <span class="{{ $slaBreached ? 'text-danger fw-bold' : 'text-success' }}">{{ $ticket->sla_due_at->format('d M Y H:i') }}</span>
                            @else —
                            @endif
                        </td>
                    </tr>
                    @if($ticket->first_response_at)<tr><td class="text-muted">1st Response</td><td>{{ $ticket->first_response_at->format('d M Y H:i') }}</td></tr>@endif
                    @if($ticket->resolved_at)<tr><td class="text-muted">Resolved At</td><td class="text-success">{{ $ticket->resolved_at->format('d M Y H:i') }}</td></tr>@endif
                    @if($ticket->resolution_hours)<tr><td class="text-muted">Resolution Time</td><td>{{ $ticket->resolution_hours }}h</td></tr>@endif
                </table>
            </div>
        </div>

        @if($ticket->device)
        <div class="card mb-3">
            <div class="card-header"><strong>Device</strong></div>
            <div class="card-body">
                <p class="fw-bold font-monospace mb-1"><a href="{{ route('devices.show',$ticket->device) }}" class="text-decoration-none">{{ $ticket->device->asset_tag }}</a></p>
                <p class="small mb-1">{{ $ticket->device->model?->brand?->name }} {{ $ticket->device->model?->model_name }}</p>
                <p class="small text-muted mb-0">S/N: {{ $ticket->device->serial_number }}</p>
            </div>
        </div>
        @endif

        @if($ticket->employee || $ticket->client)
        <div class="card mb-3">
            <div class="card-header"><strong>Reported By / Client</strong></div>
            <div class="card-body">
                @if($ticket->employee)
                <p class="fw-bold mb-0">{{ $ticket->employee->name }}</p>
                <p class="small text-muted mb-2">{{ $ticket->employee->employee_code }} &bull; {{ $ticket->employee->designation }}</p>
                @endif
                @if($ticket->client)
                <p class="mb-0 small"><i class="bi bi-building me-1"></i>{{ $ticket->client->name }}</p>
                @endif
            </div>
        </div>
        @endif

        @if($ticket->repairOrders->isNotEmpty())
        <div class="card">
            <div class="card-header"><strong>Repair Orders</strong></div>
            <div class="list-group list-group-flush">
                @foreach($ticket->repairOrders as $r)
                <a href="{{ route('repairs.show',$r) }}" class="list-group-item list-group-item-action small">
                    <span class="font-monospace fw-bold">{{ $r->rma_number }}</span>
                    <span class="badge bg-secondary float-end">{{ str_replace('_',' ',$r->status) }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><strong>{{ $ticket->subject }}</strong></div>
            <div class="card-body">
                <p class="mb-0" style="white-space:pre-wrap;">{{ $ticket->description }}</p>
            </div>
        </div>

        @if($ticket->resolution_notes)
        <div class="card mb-3 border-success">
            <div class="card-header bg-success bg-opacity-10 text-success"><strong><i class="bi bi-check-circle me-1"></i>Resolution Notes</strong></div>
            <div class="card-body"><p class="mb-0" style="white-space:pre-wrap;">{{ $ticket->resolution_notes }}</p></div>
        </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Comments ({{ $ticket->comments->count() }})</strong>
            </div>
            <div class="card-body p-0">
                @forelse($ticket->comments as $comment)
                <div class="border-bottom px-3 py-3 {{ $comment->is_internal ? 'bg-warning bg-opacity-10' : '' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="fw-bold small">{{ $comment->user?->name }}
                            @if($comment->is_internal)<span class="badge bg-warning text-dark ms-1 small">Internal</span>@endif
                        </span>
                        <span class="text-muted small">{{ $comment->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <p class="mb-0 mt-1 small" style="white-space:pre-wrap;">{{ $comment->comment }}</p>
                </div>
                @empty
                <div class="text-center text-muted py-3 small">No comments yet.</div>
                @endforelse
            </div>
            @if(!in_array($ticket->status,['closed','cancelled']))
            <div class="card-footer">
                <form method="POST" action="{{ route('tickets.comment',$ticket) }}">
                    @csrf
                    <div class="mb-2">
                        <textarea class="form-control form-control-sm" name="comment" rows="3" placeholder="Add a comment…" required></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="form-check form-check-sm">
                            <input type="checkbox" name="is_internal" value="1" id="internalCheck" class="form-check-input">
                            <label class="form-check-label small" for="internalCheck">Internal note</label>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-send me-1"></i>Add Comment</button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Status Update Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('tickets.status',$ticket) }}">
            @csrf @method('PATCH')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Update Ticket Status</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Status *</label>
                        <select class="form-select" name="status" required>
                            @foreach(['open','assigned','in_progress','pending_user','resolved','closed','cancelled'] as $s)
                            <option value="{{ $s }}" {{ $ticket->status===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Resolution Notes</label>
                        <textarea class="form-control" name="resolution_notes" rows="3" placeholder="Required when resolving or closing…">{{ $ticket->resolution_notes }}</textarea>
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
@endsection
