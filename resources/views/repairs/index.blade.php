@extends('layouts.main')
@section('title','Repair Orders')
@section('breadcrumb')
    <li class="breadcrumb-item active">Repairs</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-tools me-2"></i>Repair Orders</h5>
    <a href="{{ route('repairs.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Repair Order</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-warning border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-warning">{{ $stats['active'] }}</div><div class="text-muted small">Active</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-info border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-info">{{ $stats['at_sc'] }}</div><div class="text-muted small">At Service Centre</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-primary border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-primary">{{ $stats['under_rep'] }}</div><div class="text-muted small">Under Repair</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-danger border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-danger">{{ $stats['overdue'] }}</div><div class="text-muted small">Overdue</div></div></div></div>
</div>

<div class="card">
    <div class="card-header">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(['sent','received_at_sc','under_repair','awaiting_parts','repaired','replaced','unrepairable','returned'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="repair_type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    @foreach(['warranty','paid','insurance'] as $t)
                    <option value="{{ $t }}" {{ request('repair_type')===$t?'selected':'' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['status','repair_type']))
            <div class="col-auto"><a href="{{ route('repairs.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr><th>RMA #</th><th>Device</th><th>Service Centre</th><th>Type</th><th>Fault</th><th>Sent Date</th><th>Est. Return</th><th>Cost</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($repairs as $r)
                @php
                $rBadge = match($r->status){'returned'=>'success','repaired'=>'info','replaced'=>'primary','unrepairable'=>'danger','under_repair'=>'warning text-dark','sent'=>'secondary',default=>'light text-dark'};
                $overdue = $r->estimated_return_date && $r->estimated_return_date->lt(today()) && !in_array($r->status,['returned','unrepairable']);
                $tBadge = match($r->repair_type){'warranty'=>'success','insurance'=>'primary','paid'=>'warning text-dark',default=>'secondary'};
                @endphp
                <tr class="{{ $overdue ? 'table-warning' : '' }}">
                    <td><a href="{{ route('repairs.show',$r) }}" class="fw-bold font-monospace text-decoration-none">{{ $r->rma_number }}</a></td>
                    <td class="small font-monospace">{{ $r->device?->asset_tag }}<br><span class="text-muted">{{ $r->device?->model?->model_name }}</span></td>
                    <td class="small">{{ $r->serviceCenter?->name }}</td>
                    <td><span class="badge bg-{{ $tBadge }} small">{{ ucfirst($r->repair_type) }}</span>@if($r->under_warranty)<br><span class="badge bg-success small">Warranty</span>@endif</td>
                    <td class="small">{{ Str::limit($r->fault_description,40) }}</td>
                    <td class="small">{{ $r->sent_date?->format('d M Y') }}</td>
                    <td class="small {{ $overdue?'text-danger fw-bold':'' }}">{{ $r->estimated_return_date?->format('d M Y') ?? '—' }}</td>
                    <td class="small">{{ $r->actual_cost ? '₹'.number_format($r->actual_cost,0) : ($r->estimated_cost?'~₹'.number_format($r->estimated_cost,0):'—') }}</td>
                    <td><span class="badge bg-{{ $rBadge }}">{{ ucwords(str_replace('_',' ',$r->status)) }}</span></td>
                    <td><a href="{{ route('repairs.show',$r) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-4">No repair orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($repairs->hasPages())
    <div class="card-footer">{{ $repairs->links() }}</div>
    @endif
</div>
@endsection
