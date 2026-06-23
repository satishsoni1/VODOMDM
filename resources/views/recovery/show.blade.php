@extends('layouts.main')
@section('title','Recovery — '.$recovery->case_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('recovery.index') }}">Recovery</a></li>
    <li class="breadcrumb-item active">{{ $recovery->case_number }}</li>
@endsection

@section('content')
@php
$cBadge = match($recovery->status){'recovered'=>'success','closed'=>'dark','escalated'=>'danger','pickup_scheduled'=>'info','contacted'=>'primary','open'=>'warning text-dark',default=>'secondary'};
$overdue = $recovery->recovery_due_date && $recovery->recovery_due_date->lt(today()) && !in_array($recovery->status,['recovered','closed','written_off']);
@endphp

<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1">{{ $recovery->case_number }}
            <span class="badge ms-2 bg-{{ $cBadge }}">{{ ucwords(str_replace('_',' ',$recovery->status)) }}</span>
            @if($overdue)<span class="badge ms-1 bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Overdue</span>@endif
        </h5>
        <p class="text-muted small mb-0">{{ ucwords(str_replace('_',' ',$recovery->trigger_reason)) }} &mdash; opened {{ $recovery->created_at->format('d M Y') }}</p>
    </div>
    @if(!in_array($recovery->status,['recovered','closed','written_off']))
    <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#callLogModal"><i class="bi bi-telephone-plus me-1"></i>Log Call</button>
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#statusModal"><i class="bi bi-arrow-repeat me-1"></i>Update Status</button>
    </div>
    @endif
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><strong>Case Info</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Case #</td><td class="font-monospace">{{ $recovery->case_number }}</td></tr>
                    <tr><td class="text-muted">Reason</td><td>{{ ucwords(str_replace('_',' ',$recovery->trigger_reason)) }}</td></tr>
                    <tr><td class="text-muted">Exit Date</td><td>{{ $recovery->exit_date?->format('d M Y') ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Due Date</td><td class="{{ $overdue?'text-danger fw-bold':'' }}">{{ $recovery->recovery_due_date?->format('d M Y') ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Assigned To</td><td>{{ $recovery->assignedTo?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Follow-ups</td><td>{{ $recovery->follow_up_count }}</td></tr>
                    <tr><td class="text-muted">Last Contact</td><td>{{ $recovery->last_follow_up_at?->format('d M Y') ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Next Follow-up</td><td>{{ $recovery->next_follow_up_date?->format('d M Y') ?? '—' }}</td></tr>
                    @if($recovery->recovered_date)<tr><td class="text-muted">Recovered</td><td class="text-success fw-bold">{{ $recovery->recovered_date->format('d M Y') }}</td></tr>@endif
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Device</strong></div>
            <div class="card-body">
                @if($recovery->device)
                <p class="fw-bold font-monospace mb-1"><a href="{{ route('devices.show',$recovery->device) }}" class="text-decoration-none">{{ $recovery->device->asset_tag }}</a></p>
                <p class="small mb-0">{{ $recovery->device->model?->brand?->name }} {{ $recovery->device->model?->model_name }}</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Employee / Client</strong></div>
            <div class="card-body">
                @if($recovery->employee)
                <p class="fw-bold mb-0">{{ $recovery->employee->name }}</p>
                <p class="small text-muted mb-0">{{ $recovery->employee->phone }}</p>
                @if($recovery->pickup_address)<p class="small mt-2 text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $recovery->pickup_address }}</p>@endif
                @endif
                @if($recovery->client)<p class="mt-2 mb-0 small"><i class="bi bi-building me-1"></i>{{ $recovery->client->name }}</p>@endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Call Logs ({{ $recovery->callLogs->count() }})</strong>
            </div>
            @if($recovery->callLogs->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Date & Time</th><th>Phone</th><th>Outcome</th><th>Duration</th><th>Promise Date</th><th>Next F/U</th><th>By</th></tr>
                    </thead>
                    <tbody>
                        @foreach($recovery->callLogs->sortByDesc('call_datetime') as $log)
                        @php
                        $oBadge = match($log->outcome){
                            'agreed_to_return','promised'=>'success',
                            'refused'=>'danger',
                            'no_answer','switched_off'=>'warning text-dark',
                            default=>'secondary'
                        };
                        @endphp
                        <tr>
                            <td class="small">{{ \Carbon\Carbon::parse($log->call_datetime)->format('d M Y H:i') }}</td>
                            <td class="font-monospace small">{{ $log->phone_number }}</td>
                            <td><span class="badge bg-{{ $oBadge }} small">{{ ucwords(str_replace('_',' ',$log->outcome)) }}</span></td>
                            <td class="small">{{ $log->duration_seconds ? gmdate('i:s',$log->duration_seconds) : '—' }}</td>
                            <td class="small">{{ $log->promise_date?->format('d M Y') ?? '—' }}</td>
                            <td class="small">{{ $log->next_follow_up_date?->format('d M Y') ?? '—' }}</td>
                            <td class="small">{{ $log->calledBy?->name }}</td>
                        </tr>
                        @if($log->remarks)<tr class="table-light"><td colspan="7" class="small text-muted ps-4"><i class="bi bi-chat-left-text me-1"></i>{{ $log->remarks }}</td></tr>@endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body text-center text-muted py-4">No call logs yet. Use "Log Call" to record contact attempts.</div>
            @endif
        </div>

        @if($recovery->remarks)
        <div class="card mt-3">
            <div class="card-header"><strong>Remarks</strong></div>
            <div class="card-body"><p class="mb-0 small">{{ $recovery->remarks }}</p></div>
        </div>
        @endif
    </div>
</div>

{{-- Log Call Modal --}}
<div class="modal fade" id="callLogModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('recovery.call-log',$recovery) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="bi bi-telephone-plus me-2"></i>Log Call Attempt</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Phone Number *</label><input class="form-control" name="phone_number" value="{{ $recovery->employee?->phone }}" required></div>
                        <div class="col-md-4"><label class="form-label">Call Date & Time *</label><input type="datetime-local" class="form-control" name="call_datetime" value="{{ now()->format('Y-m-d\TH:i') }}" required></div>
                        <div class="col-md-4"><label class="form-label">Duration (seconds)</label><input type="number" class="form-control" name="duration_seconds" min="0"></div>
                        <div class="col-md-6">
                            <label class="form-label">Outcome *</label>
                            <select class="form-select" name="outcome" required>
                                @foreach(['connected','no_answer','switched_off','invalid_number','refused','agreed_to_return','promised','call_back_later'] as $o)
                                <option value="{{ $o }}">{{ ucwords(str_replace('_',' ',$o)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Promise Date</label><input type="date" class="form-control" name="promise_date"></div>
                        <div class="col-md-3"><label class="form-label">Next Follow-up</label><input type="date" class="form-control" name="next_follow_up_date"></div>
                        <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Log</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Status Update Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('recovery.update',$recovery) }}">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Update Case Status</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Status *</label>
                            <select class="form-select" name="status" required>
                                @foreach(['open','contacted','pickup_scheduled','recovered','escalated','closed','written_off'] as $s)
                                <option value="{{ $s }}" {{ $recovery->status===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12"><label class="form-label">Pickup Scheduled Date</label><input type="date" class="form-control" name="pickup_scheduled_date" value="{{ $recovery->pickup_scheduled_date?->format('Y-m-d') }}"></div>
                        <div class="col-12"><label class="form-label">Recovery Due Date</label><input type="date" class="form-control" name="recovery_due_date" value="{{ $recovery->recovery_due_date?->format('Y-m-d') }}"></div>
                        <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="2">{{ $recovery->remarks }}</textarea></div>
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
