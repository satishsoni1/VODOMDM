@extends('layouts.main')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-speedometer2 me-2"></i>Operations Dashboard</h4>
    <small class="text-muted">{{ now()->format('d M Y, H:i') }}</small>
</div>

{{-- Device Stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4 col-xl-2">
        <div class="card stat-card blue h-100">
            <div class="card-body">
                <div class="text-muted small">Total Devices</div>
                <div class="fs-3 fw-bold text-primary">{{ number_format($stats['devices']['total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-xl-2">
        <div class="card stat-card green h-100">
            <div class="card-body">
                <div class="text-muted small">In Stock</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($stats['devices']['in_stock']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-xl-2">
        <div class="card stat-card blue h-100">
            <div class="card-body">
                <div class="text-muted small">Assigned</div>
                <div class="fs-3 fw-bold">{{ number_format($stats['devices']['assigned']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-xl-2">
        <div class="card stat-card orange h-100">
            <div class="card-body">
                <div class="text-muted small">In Transit</div>
                <div class="fs-3 fw-bold text-warning">{{ number_format($stats['devices']['in_transit']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-xl-2">
        <div class="card stat-card orange h-100">
            <div class="card-body">
                <div class="text-muted small">Under Repair</div>
                <div class="fs-3 fw-bold text-warning">{{ number_format($stats['devices']['under_repair']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-xl-2">
        <div class="card stat-card red h-100">
            <div class="card-body">
                <div class="text-muted small">Lost</div>
                <div class="fs-3 fw-bold text-danger">{{ number_format($stats['devices']['lost']) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Operations Overview --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Open Tickets</div>
                        <div class="fs-4 fw-bold">{{ $stats['operations']['open_tickets'] }}</div>
                    </div>
                    <i class="bi bi-ticket-perforated fs-2 text-primary opacity-50"></i>
                </div>
                @if($stats['operations']['sla_breached'] > 0)
                    <div class="text-danger small mt-1"><i class="bi bi-exclamation-triangle-fill"></i> {{ $stats['operations']['sla_breached'] }} SLA breached</div>
                @endif
                <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-primary mt-2">View All</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Recovery Cases</div>
                        <div class="fs-4 fw-bold">{{ $stats['recovery']['pending'] }}</div>
                    </div>
                    <i class="bi bi-arrow-return-left fs-2 text-warning opacity-50"></i>
                </div>
                @if($stats['recovery']['overdue'] > 0)
                    <div class="text-danger small mt-1"><i class="bi bi-clock-fill"></i> {{ $stats['recovery']['overdue'] }} overdue</div>
                @endif
                <a href="{{ route('recovery.index') }}" class="btn btn-sm btn-outline-warning mt-2">View All</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Active Policies</div>
                        <div class="fs-4 fw-bold">{{ $stats['insurance']['active_policies'] }}</div>
                    </div>
                    <i class="bi bi-shield-check fs-2 text-success opacity-50"></i>
                </div>
                @if($stats['insurance']['expiring_soon'] > 0)
                    <div class="text-warning small mt-1"><i class="bi bi-exclamation-circle-fill"></i> {{ $stats['insurance']['expiring_soon'] }} expiring soon</div>
                @endif
                <a href="{{ route('insurance.index') }}" class="btn btn-sm btn-outline-success mt-2">View All</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Pending POs</div>
                        <div class="fs-4 fw-bold">{{ $stats['procurement']['pending_pos'] }}</div>
                    </div>
                    <i class="bi bi-bag-check fs-2 text-info opacity-50"></i>
                </div>
                <div class="text-muted small mt-1">Value: ₹{{ number_format($stats['procurement']['total_po_value'], 0) }}</div>
                <a href="{{ route('procurement.purchase-orders') }}" class="btn btn-sm btn-outline-info mt-2">View All</a>
            </div>
        </div>
    </div>
</div>

{{-- Tables --}}
<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Recent Tickets</strong>
                <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Subject</th><th>Priority</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentTickets as $ticket)
                        <tr>
                            <td><a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none">{{ $ticket->ticket_number }}</a></td>
                            <td class="text-truncate" style="max-width:180px">{{ $ticket->subject }}</td>
                            <td>
                                <span class="badge bg-{{ match($ticket->priority) { 'critical' => 'danger', 'high' => 'warning text-dark', 'medium' => 'info', default => 'secondary' } }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td><span class="badge bg-secondary">{{ ucfirst($ticket->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No tickets</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Pending Recovery Cases</strong>
                <a href="{{ route('recovery.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Case #</th><th>Employee</th><th>Device</th><th>Due</th></tr>
                    </thead>
                    <tbody>
                        @forelse($pendingRecoveries as $case)
                        <tr>
                            <td><a href="{{ route('recovery.show', $case) }}" class="text-decoration-none">{{ $case->case_number }}</a></td>
                            <td>{{ $case->employee?->name }}</td>
                            <td class="font-monospace small">{{ $case->device?->asset_tag }}</td>
                            <td class="{{ $case->recovery_due_date?->isPast() ? 'text-danger fw-bold' : '' }}">
                                {{ $case->recovery_due_date?->format('d M Y') ?? '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No pending cases</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
