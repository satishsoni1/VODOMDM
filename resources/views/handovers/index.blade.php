@extends('layouts.main')
@section('title','Device Handovers')
@section('breadcrumb')
    <li class="breadcrumb-item active">Handovers</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-person-check me-2"></i>Device Handovers</h5>
    <a href="{{ route('handovers.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Handover</a>
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
                    @foreach(['assigned','activated','returned'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['status','client_id']))
            <div class="col-auto"><a href="{{ route('handovers.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Handover #</th>
                    <th>Device</th>
                    <th>Employee</th>
                    <th>Client</th>
                    <th>Handover Date</th>
                    <th>Condition</th>
                    <th>Ack.</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($handovers as $h)
                @php
                $hBadge = match($h->status){'activated'=>'success','assigned'=>'primary','returned'=>'info',default=>'secondary'};
                $condBadge = match($h->condition_at_handover??''){'new'=>'success','good'=>'info','fair'=>'warning text-dark','poor'=>'danger',default=>'secondary'};
                @endphp
                <tr>
                    <td><a href="{{ route('handovers.show',$h) }}" class="fw-bold font-monospace text-decoration-none">{{ $h->handover_number }}</a></td>
                    <td class="small"><a href="{{ route('devices.show',$h->device) }}" class="text-decoration-none font-monospace">{{ $h->device?->asset_tag }}</a><br><span class="text-muted">{{ $h->device?->model?->model_name }}</span></td>
                    <td class="small">{{ $h->employee?->name }}<br><span class="text-muted">{{ $h->employee?->employee_code }}</span></td>
                    <td class="small">{{ $h->client?->name }}</td>
                    <td class="small">{{ $h->handover_date?->format('d M Y') }}</td>
                    <td><span class="badge bg-{{ $condBadge }} small">{{ ucfirst($h->condition_at_handover) }}</span></td>
                    <td>@if($h->acknowledgement_received)<i class="bi bi-check-circle-fill text-success"></i>@else<i class="bi bi-clock text-warning"></i>@endif</td>
                    <td><span class="badge bg-{{ $hBadge }}">{{ ucfirst($h->status) }}</span></td>
                    <td><a href="{{ route('handovers.show',$h) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No handovers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($handovers->hasPages())
    <div class="card-footer">{{ $handovers->links() }}</div>
    @endif
</div>
@endsection
