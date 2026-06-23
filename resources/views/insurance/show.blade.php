@extends('layouts.main')
@section('title','Policy — '.$insurance->policy_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('insurance.index') }}">Insurance</a></li>
    <li class="breadcrumb-item active">{{ $insurance->policy_number }}</li>
@endsection

@section('content')
@php
$pBadge = match($insurance->status){'active'=>'success','expiring'=>'warning text-dark','expired'=>'danger','cancelled'=>'secondary',default=>'secondary'};
$daysLeft = $insurance->expiry_date ? now()->diffInDays($insurance->expiry_date, false) : null;
@endphp

<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1">{{ $insurance->policy_number }}
            <span class="badge ms-2 bg-{{ $pBadge }}">{{ ucfirst($insurance->status) }}</span>
            @if($daysLeft !== null && $daysLeft <= 30 && $daysLeft >= 0)
            <span class="badge ms-1 bg-warning text-dark"><i class="bi bi-clock me-1"></i>{{ $daysLeft }} days left</span>
            @endif
        </h5>
        <p class="text-muted small mb-0">{{ $insurance->provider?->name }} &mdash; {{ $insurance->coverage_type }}</p>
    </div>
    <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#claimModal">
        <i class="bi bi-file-earmark-plus me-1"></i>File Claim
    </button>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><strong>Policy Details</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Policy #</td><td class="font-monospace">{{ $insurance->policy_number }}</td></tr>
                    <tr><td class="text-muted">Provider</td><td>{{ $insurance->provider?->name }}</td></tr>
                    <tr><td class="text-muted">Client</td><td>{{ $insurance->client?->name ?? 'General' }}</td></tr>
                    <tr><td class="text-muted">Coverage</td><td>{{ $insurance->coverage_type }}</td></tr>
                    <tr><td class="text-muted">Premium</td><td class="fw-bold">₹{{ number_format($insurance->premium_amount,2) }}</td></tr>
                    <tr><td class="text-muted">Sum Insured</td><td class="fw-bold text-success">₹{{ number_format($insurance->sum_insured,2) }}</td></tr>
                    <tr><td class="text-muted">Start Date</td><td>{{ $insurance->start_date?->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Expiry Date</td><td class="{{ ($daysLeft !== null && $daysLeft <= 30 && $daysLeft >= 0) ? 'text-warning fw-bold' : '' }}">{{ $insurance->expiry_date?->format('d M Y') }}</td></tr>
                </table>
            </div>
        </div>

        @if($insurance->coverage_details)
        <div class="card mb-3">
            <div class="card-header"><strong>Coverage Details</strong></div>
            <div class="card-body"><p class="small mb-0" style="white-space:pre-wrap;">{{ $insurance->coverage_details }}</p></div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><strong>Insured Devices ({{ $insurance->deviceInsurances->count() }})</strong></div>
            @if($insurance->deviceInsurances->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Asset Tag</th><th>Model</th><th>Insured Value</th><th>Effective</th><th>Expiry</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach($insurance->deviceInsurances as $di)
                        <tr>
                            <td><a href="{{ route('devices.show',$di->device) }}" class="font-monospace text-decoration-none fw-bold">{{ $di->device?->asset_tag }}</a></td>
                            <td class="small">{{ $di->device?->model?->model_name }}</td>
                            <td>₹{{ number_format($di->insured_value,0) }}</td>
                            <td class="small">{{ $di->effective_date?->format('d M Y') }}</td>
                            <td class="small">{{ $di->expiry_date?->format('d M Y') }}</td>
                            <td><span class="badge bg-{{ $di->status==='active'?'success':'secondary' }}">{{ ucfirst($di->status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body text-center text-muted py-3 small">No devices linked to this policy.</div>
            @endif
        </div>

        <div class="card">
            <div class="card-header"><strong>Claims ({{ $insurance->claims->count() }})</strong></div>
            @if($insurance->claims->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Claim #</th><th>Device</th><th>Incident</th><th>Claimed</th><th>Settled</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach($insurance->claims as $claim)
                        @php $clBadge = match($claim->status){'settled'=>'success','rejected'=>'danger','approved'=>'info','submitted'=>'primary',default=>'secondary'}; @endphp
                        <tr>
                            <td class="font-monospace small">{{ $claim->claim_number }}</td>
                            <td class="font-monospace small">{{ $claim->device?->asset_tag }}</td>
                            <td class="small">{{ $claim->incident_type }}<br><span class="text-muted">{{ $claim->incident_date?->format('d M Y') }}</span></td>
                            <td>₹{{ number_format($claim->claimed_amount,0) }}</td>
                            <td>{{ $claim->settled_amount ? '₹'.number_format($claim->settled_amount,0) : '—' }}</td>
                            <td><span class="badge bg-{{ $clBadge }}">{{ ucfirst($claim->status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body text-center text-muted py-3 small">No claims filed yet.</div>
            @endif
        </div>
    </div>
</div>

{{-- File Claim Modal --}}
<div class="modal fade" id="claimModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('insurance.claims.store',$insurance) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2"></i>File Insurance Claim</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">Device *</label>
                            <select class="form-select" name="device_id" required>
                                <option value="">— Select Device —</option>
                                @foreach($availableDevices as $dev)
                                <option value="{{ $dev->id }}">{{ $dev->asset_tag }} — {{ $dev->model?->model_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5"><label class="form-label">Claim Date *</label><input type="date" class="form-control" name="claim_date" value="{{ now()->format('Y-m-d') }}" required></div>
                        <div class="col-md-4"><label class="form-label">Incident Date *</label><input type="date" class="form-control" name="incident_date" required></div>
                        <div class="col-md-4"><label class="form-label">Incident Type *</label><input class="form-control" name="incident_type" required placeholder="Theft / Damage / Screen break…"></div>
                        <div class="col-md-4"><label class="form-label">Claimed Amount (₹) *</label><input type="number" class="form-control" name="claimed_amount" step="0.01" min="0" required></div>
                        <div class="col-12"><label class="form-label">Incident Description *</label><textarea class="form-control" name="incident_description" rows="3" required></textarea></div>
                        <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>File Claim</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
