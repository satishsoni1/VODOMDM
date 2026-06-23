@extends('layouts.main')
@section('title','Insurance Claims')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('insurance.index') }}">Insurance</a></li>
    <li class="breadcrumb-item active">Claims</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-medical me-2"></i>All Insurance Claims</h5>
    <a href="{{ route('insurance.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Policies</a>
</div>

<div class="card">
    <div class="card-header">
        <form class="row g-2" method="GET">
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(['draft','submitted','under_review','approved','partially_approved','rejected','settled','closed'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            @if(request('status'))
            <div class="col-auto"><a href="{{ route('insurance.claims') }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr><th>Claim #</th><th>Policy</th><th>Device</th><th>Incident</th><th>Claimed (₹)</th><th>Approved (₹)</th><th>Settled (₹)</th><th>Filed On</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($claims as $claim)
                @php $clBadge = match($claim->status){'settled'=>'success','rejected'=>'danger','approved','partially_approved'=>'info','submitted'=>'primary','under_review'=>'warning text-dark',default=>'secondary'}; @endphp
                <tr>
                    <td class="font-monospace fw-bold small">{{ $claim->claim_number }}</td>
                    <td class="small"><a href="{{ route('insurance.show',$claim->policy) }}" class="text-decoration-none font-monospace">{{ $claim->policy?->policy_number }}</a><br><span class="text-muted">{{ $claim->policy?->provider?->name }}</span></td>
                    <td class="small font-monospace">{{ $claim->device?->asset_tag }}<br><span class="text-muted">{{ $claim->device?->model?->model_name }}</span></td>
                    <td class="small">{{ $claim->incident_type }}<br><span class="text-muted">{{ $claim->incident_date?->format('d M Y') }}</span></td>
                    <td class="small">{{ number_format($claim->claimed_amount,0) }}</td>
                    <td class="small">{{ $claim->approved_amount ? number_format($claim->approved_amount,0) : '—' }}</td>
                    <td class="small {{ $claim->settled_amount?'text-success fw-bold':'' }}">{{ $claim->settled_amount ? number_format($claim->settled_amount,0) : '—' }}</td>
                    <td class="small">{{ $claim->claim_date?->format('d M Y') }}</td>
                    <td><span class="badge bg-{{ $clBadge }}">{{ ucwords(str_replace('_',' ',$claim->status)) }}</span></td>
                </tr>
                @if($claim->rejection_reason)
                <tr class="table-danger"><td colspan="9" class="small text-danger ps-4"><i class="bi bi-x-circle me-1"></i>Rejection: {{ $claim->rejection_reason }}</td></tr>
                @endif
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No claims found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($claims->hasPages())
    <div class="card-footer">{{ $claims->links() }}</div>
    @endif
</div>
@endsection
