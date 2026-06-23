@extends('layouts.main')
@section('title','Recovery Cases')
@section('breadcrumb')
    <li class="breadcrumb-item active">Recovery</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-arrow-counterclockwise me-2"></i>Device Recovery</h5>
    <a href="{{ route('recovery.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Case</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-primary border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-primary">{{ $stats['open'] }}</div><div class="text-muted small">Active Cases</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-danger border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-danger">{{ $stats['overdue'] }}</div><div class="text-muted small">Overdue</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-success border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-success">{{ $stats['recovered'] }}</div><div class="text-muted small">Recovered</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-warning border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-warning">{{ $stats['escalated'] }}</div><div class="text-muted small">Escalated</div></div></div></div>
</div>

<div class="card">
    <div class="card-header">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4">
                <select name="client_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)<option value="{{ $c->id }}" {{ request('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(['open','contacted','pickup_scheduled','recovered','escalated','closed','written_off'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['status','client_id']))
            <div class="col-auto"><a href="{{ route('recovery.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr><th>Case #</th><th>Device</th><th>Employee</th><th>Client</th><th>Reason</th><th>Due Date</th><th>Follow-ups</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($cases as $case)
                @php
                $overdue = $case->recovery_due_date && $case->recovery_due_date->lt(today()) && !in_array($case->status,['recovered','closed','written_off']);
                $cBadge = match($case->status){'recovered'=>'success','closed'=>'dark','escalated'=>'danger','pickup_scheduled'=>'info','contacted'=>'primary','open'=>'warning text-dark',default=>'secondary'};
                @endphp
                <tr class="{{ $overdue ? 'table-danger' : '' }}">
                    <td><a href="{{ route('recovery.show',$case) }}" class="fw-bold font-monospace text-decoration-none">{{ $case->case_number }}</a></td>
                    <td class="small font-monospace">{{ $case->device?->asset_tag ?? '—' }}<br><span class="text-muted">{{ $case->device?->model?->model_name }}</span></td>
                    <td class="small">{{ $case->employee?->name }}<br><span class="text-muted">{{ $case->employee?->phone }}</span></td>
                    <td class="small">{{ $case->client?->name }}</td>
                    <td class="small">{{ ucwords(str_replace('_',' ',$case->trigger_reason)) }}</td>
                    <td class="small {{ $overdue ? 'text-danger fw-bold' : '' }}">
                        {{ $case->recovery_due_date?->format('d M Y') ?? '—' }}
                        @if($overdue)<br><span class="text-danger small"><i class="bi bi-exclamation-triangle-fill"></i> Overdue</span>@endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary">{{ $case->follow_up_count }}</span>
                        @if($case->next_follow_up_date)<br><span class="text-muted small">Next: {{ $case->next_follow_up_date->format('d M') }}</span>@endif
                    </td>
                    <td><span class="badge bg-{{ $cBadge }}">{{ ucwords(str_replace('_',' ',$case->status)) }}</span></td>
                    <td><a href="{{ route('recovery.show',$case) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No recovery cases found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($cases->hasPages())
    <div class="card-footer">{{ $cases->links() }}</div>
    @endif
</div>
@endsection
