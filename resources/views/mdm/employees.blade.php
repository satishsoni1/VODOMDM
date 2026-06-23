@extends('layouts.main')
@section('title','MDM — Employee Device View')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mdm.index') }}">MDM</a></li>
    <li class="breadcrumb-item active">Employee Devices</li>
@endsection

@push('styles')
<style>
    .emp-card { transition: box-shadow .2s; border-left: 3px solid transparent; }
    .emp-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); }
    .emp-card.has-device { border-left-color: #198754; }
    .emp-card.no-device  { border-left-color: #dee2e6; }
    .emp-card.device-offline { border-left-color: #dc3545; }
    .status-dot { width:10px; height:10px; border-radius:50%; display:inline-block; }
    .desig-group-header { background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 8px; }
</style>
@endpush

@section('content')

{{-- Designation Summary Cards --}}
<div class="row g-3 mb-4">
    @foreach($designationSummary as $ds)
    @php $pct = $ds->total ? round($ds->device_count / $ds->total * 100) : 0; @endphp
    <div class="col-xl-2 col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-2">
            <div class="fw-bold fs-5">{{ $ds->device_count }}<span class="text-muted fs-6">/{{ $ds->total }}</span></div>
            <div class="small text-muted px-1">{{ $ds->designation }}</div>
            <div class="px-3 mt-1">
                <div class="bg-light rounded" style="height:4px">
                    <div class="rounded bg-primary" style="width:{{ $pct }}%; height:4px"></div>
                </div>
                <div class="text-muted" style="font-size:.7rem">{{ $pct }}% with device</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="designation">
                    <option value="">All Designations</option>
                    @foreach($designations as $d)
                    <option value="{{ $d }}" {{ request('designation')===$d?'selected':'' }}>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="client_id">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="linked">
                    <option value="">All</option>
                    <option value="yes" {{ request('linked')==='yes'?'selected':'' }}>With Device</option>
                    <option value="no" {{ request('linked')==='no'?'selected':'' }}>Without Device</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('mdm.employees') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- Employee Cards grouped by Designation --}}
@php $currentDesig = null; @endphp
@forelse($employees as $emp)
    @if($currentDesig !== $emp->designation)
        @if($currentDesig !== null) </div> @endif
        @php $currentDesig = $emp->designation; @endphp
        <div class="desig-group-header d-flex align-items-center gap-3 px-3 py-2 mb-3 mt-2">
            <i class="bi bi-person-badge text-primary"></i>
            <span class="fw-bold">{{ $emp->designation }}</span>
            <span class="badge bg-primary-subtle text-primary border">
                {{ $employees->where('designation', $emp->designation)->count() }} employees
            </span>
        </div>
        <div class="row g-3 mb-2">
    @endif

    @php
        $mdm = $emp->mdmDevice;
        $cardClass = 'no-device';
        if ($mdm) $cardClass = $mdm->isOnline() ? 'has-device' : 'device-offline';
    @endphp
    <div class="col-xl-3 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm emp-card {{ $cardClass }} h-100">
            <div class="card-body p-3">
                {{-- Employee Info --}}
                <div class="d-flex align-items-start gap-2 mb-2">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;font-size:.85rem;font-weight:700">
                        {{ strtoupper(substr($emp->name,0,1)) }}
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-semibold text-truncate">{{ $emp->name }}</div>
                        <div class="text-muted" style="font-size:.75rem">{{ $emp->employee_code }}</div>
                        <div class="text-muted" style="font-size:.72rem">{{ $emp->city }} · {{ $emp->region }}</div>
                    </div>
                    @if($mdm)
                    <span class="status-dot mt-1 flex-shrink-0" style="background:{{ $mdm->isOnline() ? '#198754' : '#dc3545' }}" title="{{ $mdm->isOnline() ? 'Online' : 'Offline' }}"></span>
                    @endif
                </div>

                <div class="small mb-2">
                    <i class="bi bi-briefcase text-muted me-1"></i>{{ $emp->client?->name ?? '—' }}
                </div>

                @if($mdm)
                {{-- MDM Device Info --}}
                <div class="border rounded p-2 bg-light" style="font-size:.78rem">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-semibold">{{ $mdm->mdm_number }}</span>
                        @php $b = $mdm->device_status === 'on' ? 'success' : 'danger'; @endphp
                        <span class="badge bg-{{ $b }}-subtle text-{{ $b }} border" style="font-size:.65rem">{{ strtoupper($mdm->device_status) }}</span>
                    </div>
                    <div class="text-muted mb-1">{{ $mdm->model ?? '—' }}</div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last sync:</span>
                        <span class="{{ $mdm->syncFreshnessClass() === 'success' ? 'text-success' : ($mdm->syncFreshnessClass() === 'warning' ? 'text-warning' : 'text-danger') }}">
                            {{ $mdm->syncAgeLabel() }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-muted">Perms:</span>
                        @if($mdm->isPermissionCompliant())
                            <span class="text-success"><i class="bi bi-check-circle-fill"></i> OK</span>
                        @else
                            <span class="text-danger"><i class="bi bi-exclamation-circle-fill"></i> Issue</span>
                        @endif
                    </div>
                    @if($mdm->hasOutdatedApps())
                    <div class="badge bg-warning-subtle text-warning border mt-1 w-100 text-center" style="font-size:.65rem">
                        <i class="bi bi-arrow-up-circle me-1"></i>App updates available
                    </div>
                    @endif
                    <div class="mt-2 text-end">
                        <a href="{{ route('mdm.show', $mdm) }}" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size:.72rem">
                            <i class="bi bi-eye me-1"></i>View Device
                        </a>
                    </div>
                </div>
                @else
                {{-- No device --}}
                <div class="border rounded p-2 text-center text-muted bg-light" style="font-size:.78rem">
                    <i class="bi bi-phone-slash d-block mb-1 fs-5 opacity-50"></i>
                    No MDM device linked
                </div>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="text-center py-5 text-muted">
        <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
        No employees found matching filters.
    </div>
@endforelse
@if($currentDesig !== null) </div> @endif

@if($employees->hasPages())
<div class="mt-3">{{ $employees->links() }}</div>
@endif
@endsection
