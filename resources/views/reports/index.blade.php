@extends('layouts.main')
@section('title','Reports & Analytics')
@section('breadcrumb')
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2"></i>Reports & Analytics</h5>
</div>

{{-- KPI Summary --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3"><i class="bi bi-phone fs-4 text-primary"></i></div>
                <div><div class="fs-3 fw-bold">{{ number_format($summary['total_devices']) }}</div><div class="text-muted small">Total Devices</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="bi bi-check-circle fs-4 text-success"></i></div>
                <div><div class="fs-3 fw-bold">{{ number_format($summary['active_devices']) }}</div><div class="text-muted small">Active Devices</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="bi bi-ticket-detailed fs-4 text-warning"></i></div>
                <div><div class="fs-3 fw-bold">{{ number_format($summary['open_tickets']) }}</div><div class="text-muted small">Open Tickets</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3"><i class="bi bi-arrow-counterclockwise fs-4 text-danger"></i></div>
                <div><div class="fs-3 fw-bold">{{ number_format($summary['open_recovery']) }}</div><div class="text-muted small">Open Recovery</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3"><i class="bi bi-shield-check fs-4 text-info"></i></div>
                <div><div class="fs-3 fw-bold">{{ number_format($summary['active_insurance']) }}</div><div class="text-muted small">Active Policies</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-secondary bg-opacity-10 p-3"><i class="bi bi-tools fs-4 text-secondary"></i></div>
                <div><div class="fs-3 fw-bold">{{ number_format($summary['active_repairs']) }}</div><div class="text-muted small">Active Repairs</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="bi bi-cart fs-4 text-warning"></i></div>
                <div><div class="fs-3 fw-bold">{{ number_format($summary['pending_po']) }}</div><div class="text-muted small">Pending POs</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="bi bi-currency-rupee fs-4 text-success"></i></div>
                <div><div class="fs-3 fw-bold">₹{{ number_format($summary['total_po_value']/100000,1) }}L</div><div class="text-muted small">Total PO Value</div></div>
            </div>
        </div>
    </div>
</div>

{{-- Report cards --}}
<div class="row g-3">
    @php
    $reports = [
        ['route'=>'reports.inventory','icon'=>'bi-box-seam','color'=>'primary','title'=>'Inventory Report','desc'=>'Device stock levels, lifecycle status breakdown, location-wise distribution and recent receipts.'],
        ['route'=>'reports.procurement','icon'=>'bi-cart-check','color'=>'success','title'=>'Procurement Report','desc'=>'Purchase order summary, vendor-wise spend, demand requests pipeline and approval stats.'],
        ['route'=>'reports.recovery','icon'=>'bi-arrow-counterclockwise','color'=>'danger','title'=>'Recovery Report','desc'=>'Open cases, overdue devices, call log summary and recovery rate by client.'],
        ['route'=>'reports.insurance','icon'=>'bi-shield-check','color'=>'info','title'=>'Insurance Report','desc'=>'Active policies, expiring coverage, claim pipeline and settlement analysis.'],
        ['route'=>'reports.financial','icon'=>'bi-currency-rupee','color'=>'warning','title'=>'Financial Report','desc'=>'Procurement spend, repair costs, insurance premiums, claims settled — cost overview.'],
        ['route'=>'reports.device-lifecycle','icon'=>'bi-recycle','color'=>'secondary','title'=>'Device Lifecycle','desc'=>'Age analysis, warranty expiry, condition distribution and lifecycle status breakdown.'],
        ['route'=>'reports.device-tracking','icon'=>'bi-geo-alt','color'=>'primary','title'=>'Device Tracking','desc'=>'Employee, warehouse, group and MDM-installed status for every device in one filterable view.'],
    ];
    @endphp
    @foreach($reports as $r)
    <div class="col-md-6 col-xl-4">
        <a href="{{ route($r['route']) }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-hover">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-{{ $r['color'] }} bg-opacity-10 p-3">
                            <i class="bi {{ $r['icon'] }} fs-4 text-{{ $r['color'] }}"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark">{{ $r['title'] }}</h6>
                    </div>
                    <p class="text-muted small mb-0">{{ $r['desc'] }}</p>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <span class="small text-{{ $r['color'] }} fw-semibold">View Report <i class="bi bi-arrow-right ms-1"></i></span>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

@push('styles')
<style>
.card-hover { transition: transform .15s, box-shadow .15s; }
.card-hover:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.12) !important; }
</style>
@endpush
@endsection
