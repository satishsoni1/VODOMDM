@extends('layouts.main')
@section('title','Insurance Report')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Insurance</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-shield-check me-2"></i>Insurance Report</h5>
    <a href="{{ route('insurance.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Insurance</a>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header"><strong>Policies by Status</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Status</th><th class="text-end">Policies</th><th class="text-end">Sum Insured (₹)</th></tr></thead>
                    <tbody>
                    @foreach($byStatus as $row)
                    @php $b = match($row->status){'active'=>'success','expiring'=>'warning text-dark','expired'=>'danger','cancelled'=>'secondary',default=>'secondary'}; @endphp
                    <tr>
                        <td><span class="badge bg-{{ $b }} me-1">&nbsp;</span>{{ ucfirst($row->status) }}</td>
                        <td class="text-end">{{ $row->count }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->total_sum,0) }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Claims by Status</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Status</th><th class="text-end">Count</th><th class="text-end">Claimed (₹)</th></tr></thead>
                    <tbody>
                    @foreach($claimsByStatus as $row)
                    @php $b = match($row->status){'settled'=>'success','rejected'=>'danger','approved'=>'info','submitted'=>'primary','under_review'=>'warning text-dark',default=>'secondary'}; @endphp
                    <tr>
                        <td><span class="badge bg-{{ $b }} me-1">&nbsp;</span>{{ ucwords(str_replace('_',' ',$row->status)) }}</td>
                        <td class="text-end">{{ $row->count }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->total_claimed,0) }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        @if($expiringSoon->isNotEmpty())
        <div class="card mb-3 border-warning">
            <div class="card-header bg-warning bg-opacity-10 text-warning"><strong><i class="bi bi-clock me-1"></i>Expiring Within 60 Days ({{ $expiringSoon->count() }})</strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Policy #</th><th>Provider</th><th>Client</th><th>Expiry</th><th>Days Left</th></tr></thead>
                    <tbody>
                    @foreach($expiringSoon as $pol)
                    <tr>
                        <td><a href="{{ route('insurance.show',$pol) }}" class="font-monospace text-decoration-none">{{ $pol->policy_number }}</a></td>
                        <td class="small">{{ $pol->provider?->name }}</td>
                        <td class="small">{{ $pol->client?->name ?? 'General' }}</td>
                        <td class="small fw-bold {{ $pol->expiry_date->diffInDays(now()) <= 30 ? 'text-danger' : 'text-warning' }}">{{ $pol->expiry_date->format('d M Y') }}</td>
                        <td><span class="badge bg-{{ $pol->expiry_date->diffInDays(now()) <= 30 ? 'danger' : 'warning text-dark' }}">{{ $pol->expiry_date->diffInDays(now()) }}d</span></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><strong>Recent Claims</strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Claim #</th><th>Device</th><th>Provider</th><th>Claimed (₹)</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($recentClaims as $claim)
                    @php $b = match($claim->status){'settled'=>'success','rejected'=>'danger','approved'=>'info','submitted'=>'primary',default=>'secondary'}; @endphp
                    <tr>
                        <td class="font-monospace small">{{ $claim->claim_number }}</td>
                        <td class="font-monospace small">{{ $claim->device?->asset_tag }}</td>
                        <td class="small">{{ $claim->policy?->provider?->name }}</td>
                        <td class="fw-bold small">{{ number_format($claim->claimed_amount,0) }}</td>
                        <td><span class="badge bg-{{ $b }} small">{{ ucwords(str_replace('_',' ',$claim->status)) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No claims</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
