@extends('layouts.main')
@section('title','Support Tickets')
@section('breadcrumb')
    <li class="breadcrumb-item active">Tickets</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-ticket-detailed me-2"></i>Support Tickets</h5>
    <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Ticket</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-warning border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-warning">{{ $stats['open'] }}</div><div class="text-muted small">Open</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-danger border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-danger">{{ $stats['sla_breached'] }}</div><div class="text-muted small">SLA Breached</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-success border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-success">{{ $stats['resolved'] }}</div><div class="text-muted small">Resolved Today</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-dark border-3"><div class="card-body py-3"><div class="fs-4 fw-bold">{{ $stats['critical'] }}</div><div class="text-muted small">Critical Open</div></div></div></div>
</div>

<div class="card">
    <div class="card-header">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <select name="client_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)<option value="{{ $c->id }}" {{ request('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(['open','assigned','in_progress','pending_user','resolved','closed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    @foreach(['low','medium','high','critical'] as $p)
                    <option value="{{ $p }}" {{ request('priority')===$p?'selected':'' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['status','priority','client_id']))
            <div class="col-auto"><a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr><th>Ticket #</th><th>Subject</th><th>Device</th><th>Client</th><th>Priority</th><th>Assigned To</th><th>SLA</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($tickets as $t)
                @php
                $sBadge = match($t->status){'resolved'=>'success','closed'=>'dark','open'=>'warning text-dark','assigned'=>'primary','in_progress'=>'info','cancelled'=>'secondary',default=>'light text-dark'};
                $pBadge = match($t->priority){'critical'=>'danger','high'=>'warning text-dark','medium'=>'info','low'=>'secondary',default=>'secondary'};
                $slaBreached = $t->sla_due_at && now()->gt($t->sla_due_at) && !in_array($t->status,['resolved','closed']);
                @endphp
                <tr class="{{ $slaBreached ? 'table-danger' : '' }}">
                    <td><a href="{{ route('tickets.show',$t) }}" class="fw-bold font-monospace text-decoration-none">{{ $t->ticket_number }}</a></td>
                    <td class="small">{{ Str::limit($t->subject,50) }}<br><span class="text-muted">{{ $t->category?->name }}</span></td>
                    <td class="small font-monospace">{{ $t->device?->asset_tag ?? '—' }}</td>
                    <td class="small">{{ $t->client?->name ?? '—' }}</td>
                    <td><span class="badge bg-{{ $pBadge }} small">{{ ucfirst($t->priority) }}</span></td>
                    <td class="small">{{ $t->assignedTo?->name ?? '<span class="text-muted">Unassigned</span>' }}</td>
                    <td class="small">
                        @if($t->sla_due_at)
                            @if($slaBreached)
                                <span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Breached</span>
                            @else
                                <span class="text-success">{{ $t->sla_due_at->format('d M H:i') }}</span>
                            @endif
                        @else —
                        @endif
                    </td>
                    <td><span class="badge bg-{{ $sBadge }}">{{ ucwords(str_replace('_',' ',$t->status)) }}</span></td>
                    <td><a href="{{ route('tickets.show',$t) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No tickets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tickets->hasPages())
    <div class="card-footer">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
