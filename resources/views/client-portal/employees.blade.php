@extends('client-portal.layout')
@section('title','Field Staff')
@section('page-title','Field Staff')

@section('content')

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                    placeholder="Search name or code…">
            </div>
            <div class="col-md-3">
                <select name="designation" class="form-select form-select-sm">
                    <option value="">All Designations</option>
                    @foreach($designations as $d)
                    <option value="{{ $d }}" @selected(request('designation')===$d)>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button class="btn btn-sm text-white" style="background:var(--gs-teal)">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                @if(request()->hasAny(['q','designation']))
                <a href="{{ route('client.employees') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Employee Cards --}}
@if($employees->isEmpty())
<div class="text-center py-5 text-muted">
    <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>No staff found.
</div>
@else
<div class="row g-3">
    @foreach($employees as $emp)
    @php
        $mdm = $emp->mdmDevice;
        $hasMdm  = $mdm !== null;
        $isOnline = $hasMdm && $mdm->isOnline();
        $borderColor = $isOnline ? '#1a8a7c' : ($hasMdm ? '#f07030' : '#dee2e6');
    @endphp
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid {{ $borderColor }} !important">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="kpi-icon kpi-teal" style="width:44px;height:44px;border-radius:10px;font-size:1.1rem;flex-shrink:0">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold" style="color:var(--gs-teal-dark)">{{ $emp->name }}</div>
                        <div class="text-muted small">{{ $emp->designation }}</div>
                        <div class="text-muted" style="font-size:.72rem">{{ $emp->employee_code }}
                            @if($emp->city) &nbsp;· {{ $emp->city }}@endif
                        </div>
                    </div>
                    @if($emp->phone)
                    <a href="tel:{{ $emp->phone }}" class="btn btn-sm btn-outline-secondary" title="{{ $emp->phone }}">
                        <i class="bi bi-telephone"></i>
                    </a>
                    @endif
                </div>

                @if($hasMdm)
                <div class="rounded p-2 small" style="background:{{ $isOnline ? 'var(--gs-teal-light)' : '#fff3eb' }}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="fw-semibold" style="color:var(--gs-teal-dark)">MDM #{{ $mdm->mdm_number }}</span>
                        <span class="badge rounded-pill {{ $isOnline ? 'badge-on' : 'badge-off' }}">
                            <i class="bi bi-circle-fill me-1" style="font-size:.45rem"></i>
                            {{ $isOnline ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                    <div class="text-muted">{{ $mdm->model }} &nbsp;·&nbsp;
                        <span class="badge bg-{{ $mdm->syncFreshnessClass() }}-subtle text-{{ $mdm->syncFreshnessClass() }} border">
                            {{ $mdm->syncAgeLabel() }}
                        </span>
                    </div>
                    @if(!$mdm->isPermissionCompliant())
                    <div class="mt-1 text-warning small"><i class="bi bi-shield-exclamation me-1"></i>Permission issue</div>
                    @endif
                    @if($mdm->hasOutdatedApps())
                    <div class="mt-1 small" style="color:var(--gs-orange)"><i class="bi bi-arrow-up-circle me-1"></i>App updates pending</div>
                    @endif
                </div>
                @else
                <div class="rounded p-2 small bg-light text-muted text-center">
                    <i class="bi bi-phone-x me-1"></i>No MDM device
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($employees->hasPages())
<div class="mt-3">{{ $employees->links() }}</div>
@endif
@endif
@endsection
