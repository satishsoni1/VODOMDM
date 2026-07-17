@extends('client-portal.layout')
@section('title', $mdm->pg_number)
@section('page-title','MDM Device Detail')

@php
    $lat = $mdm->locationLatest?->latitude ?? $mdm->latitude;
    $lng = $mdm->locationLatest?->longitude ?? $mdm->longitude;
@endphp

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="mb-0 fw-bold font-monospace" style="color:var(--gs-teal-dark)">{{ $mdm->pg_number }}</h5>
        <div class="text-muted small">{{ $mdm->model ?? '—' }}</div>
    </div>
    <a href="{{ route('client.mdm-devices') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to list
    </a>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="kpi-card text-center">
            @if($mdm->isOnline())
                <span class="badge rounded-pill badge-on fs-6"><i class="bi bi-wifi"></i> Online</span>
            @else
                <span class="badge rounded-pill badge-off fs-6"><i class="bi bi-wifi-off"></i> Offline</span>
            @endif
            <div class="text-muted small mt-2">
                Last sync:
                @if($mdm->sync_time)
                    <span class="text-{{ $mdm->syncFreshnessClass() }} fw-semibold">{{ $mdm->syncAgeLabel() }}</span>
                    ({{ $mdm->sync_time->format('d M Y, H:i') }})
                @else
                    Never
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card text-center">
            <div class="kpi-val" style="font-size:1.5rem">{{ $mdm->hardware?->battery !== null ? $mdm->hardware->battery.'%' : '—' }}</div>
            <div class="kpi-label">Battery</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card text-center">
            <div class="kpi-val" style="font-size:1.5rem">{{ $mdm->android_version ?? '—' }}</div>
            <div class="kpi-label">Android Version</div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-info-circle me-2"></i>Device Info</strong>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Serial Number</td><td class="font-monospace">{{ $mdm->serial_number ?? '—' }}</td></tr>
                    <tr><td class="text-muted">IMEI</td><td class="font-monospace">{{ $mdm->imei ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Configuration</td><td>{{ $mdm->configuration ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Group</td><td>{{ $mdm->mdm_group ?? '—' }}</td></tr>
                    <tr><td class="text-muted">IP Address</td><td class="font-monospace">{{ $mdm->ip_address ?? '—' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-person me-2"></i>Assigned Employee</strong>
            </div>
            <div class="card-body">
                @if($mdm->employee)
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Name</td><td class="fw-semibold">{{ $mdm->employee->name }}</td></tr>
                    <tr><td class="text-muted">Code</td><td>{{ $mdm->employee->employee_code ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Designation</td><td>{{ $mdm->employee->designation ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Phone</td><td>{{ $mdm->employee->phone ?? '—' }}</td></tr>
                </table>
                @else
                <div class="text-muted fst-italic">Not linked to an employee.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-geo-alt me-2"></i>Last Known Location</strong>
            </div>
            <div class="card-body">
                @if($lat && $lng)
                <div class="d-flex align-items-center justify-content-between">
                    <span class="font-monospace">{{ $lat }}, {{ $lng }}</span>
                    <a href="https://maps.google.com/?q={{ $lat }},{{ $lng }}" target="_blank" class="btn btn-sm text-white" style="background:var(--gs-teal)">
                        <i class="bi bi-map me-1"></i>Open in Google Maps
                    </a>
                </div>
                @else
                <div class="text-muted fst-italic">No GPS location recorded.</div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
