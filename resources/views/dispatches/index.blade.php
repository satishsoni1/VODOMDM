@extends('layouts.main')
@section('title','Dispatch Batches')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dispatches</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-truck me-2"></i>Dispatch Batches</h5>
    <a href="{{ route('dispatches.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Dispatch</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm"><div class="card-body py-3"><div class="fs-4 fw-bold">{{ $stats['total'] }}</div><div class="text-muted small">Total</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-warning border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-warning">{{ $stats['ready'] }}</div><div class="text-muted small">Ready</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-primary border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-primary">{{ $stats['in_transit'] }}</div><div class="text-muted small">In Transit</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-success border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-success">{{ $stats['delivered'] }}</div><div class="text-muted small">Delivered</div></div></div></div>
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
                    @foreach(['ready','in_transit','delivered','returned','lost'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['status','client_id']))
            <div class="col-auto"><a href="{{ route('dispatches.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Dispatch #</th>
                    <th>Client</th>
                    <th>Courier</th>
                    <th>Devices</th>
                    <th>Dispatch Date</th>
                    <th>Receiver</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $batch)
                @php
                $badge = match($batch->status){
                    'delivered'=>'success','in_transit'=>'primary','ready'=>'warning text-dark',
                    'returned'=>'info','lost'=>'danger',default=>'secondary'
                };
                @endphp
                <tr>
                    <td><a href="{{ route('dispatches.show',$batch) }}" class="fw-bold font-monospace text-decoration-none">{{ $batch->dispatch_number }}</a></td>
                    <td>{{ $batch->client?->name ?? '—' }}</td>
                    <td class="small">{{ $batch->courierPartner?->name ?? '—' }}@if($batch->awb_number)<br><span class="text-muted font-monospace">{{ $batch->awb_number }}</span>@endif</td>
                    <td><span class="badge bg-secondary">{{ $batch->items->count() }}</span></td>
                    <td class="small">{{ $batch->dispatch_date?->format('d M Y') ?? '—' }}</td>
                    <td class="small">{{ $batch->receiver_name }}<br><span class="text-muted">{{ $batch->destination_city }}</span></td>
                    <td><span class="badge bg-{{ $badge }}">{{ ucwords(str_replace('_',' ',$batch->status)) }}</span></td>
                    <td><a href="{{ route('dispatches.show',$batch) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No dispatch batches found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($batches->hasPages())
    <div class="card-footer">{{ $batches->links() }}</div>
    @endif
</div>
@endsection
