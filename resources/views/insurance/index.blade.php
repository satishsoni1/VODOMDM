@extends('layouts.main')
@section('title','Insurance Policies')
@section('breadcrumb')
    <li class="breadcrumb-item active">Insurance</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-shield-check me-2"></i>Insurance Policies</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('insurance.claims') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-medical me-1"></i>All Claims</a>
        <a href="{{ route('insurance.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Policy</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-success border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-success">{{ $stats['active'] }}</div><div class="text-muted small">Active Policies</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-warning border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-warning">{{ $stats['expiring'] }}</div><div class="text-muted small">Expiring Soon</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-danger border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-danger">{{ $stats['claims'] }}</div><div class="text-muted small">Open Claims</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm border-start border-primary border-3"><div class="card-body py-3"><div class="fs-4 fw-bold text-primary small">₹{{ number_format($stats['total_sum']/100000,1) }}L</div><div class="text-muted small">Total Sum Insured</div></div></div></div>
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
                    @foreach(['active','expiring','expired','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['status','client_id']))
            <div class="col-auto"><a href="{{ route('insurance.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr><th>Policy #</th><th>Provider</th><th>Client</th><th>Coverage Type</th><th>Sum Insured</th><th>Devices</th><th>Claims</th><th>Expiry</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($policies as $pol)
                @php
                $pBadge = match($pol->status){'active'=>'success','expiring'=>'warning text-dark','expired'=>'danger','cancelled'=>'secondary',default=>'secondary'};
                $expiringSoon = $pol->expiry_date && $pol->expiry_date->diffInDays(now()) <= 30 && $pol->status === 'active';
                @endphp
                <tr class="{{ $expiringSoon ? 'table-warning' : '' }}">
                    <td><a href="{{ route('insurance.show',$pol) }}" class="fw-bold font-monospace text-decoration-none">{{ $pol->policy_number }}</a></td>
                    <td class="small">{{ $pol->provider?->name }}</td>
                    <td class="small">{{ $pol->client?->name ?? '<span class="text-muted">All</span>' }}</td>
                    <td class="small">{{ $pol->coverage_type }}</td>
                    <td class="small fw-bold">₹{{ number_format($pol->sum_insured,0) }}</td>
                    <td class="text-center"><span class="badge bg-secondary">{{ $pol->deviceInsurances->count() }}</span></td>
                    <td class="text-center"><span class="badge bg-{{ $pol->claims->count() ? 'warning text-dark' : 'light text-muted' }}">{{ $pol->claims->count() }}</span></td>
                    <td class="small {{ $expiringSoon?'text-warning fw-bold':'' }}">{{ $pol->expiry_date?->format('d M Y') }}</td>
                    <td><span class="badge bg-{{ $pBadge }}">{{ ucfirst($pol->status) }}</span></td>
                    <td><a href="{{ route('insurance.show',$pol) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-4">No insurance policies found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($policies->hasPages())
    <div class="card-footer">{{ $policies->links() }}</div>
    @endif
</div>
@endsection
