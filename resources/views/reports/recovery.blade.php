@extends('layouts.main')
@section('title','Recovery Report')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Recovery</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-arrow-counterclockwise me-2"></i>Recovery Report</h5>
    <a href="{{ route('recovery.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Recovery Cases</a>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header"><strong>Cases by Status</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Status</th><th class="text-end">Count</th></tr></thead>
                    <tbody>
                    @php $totalCases = $byStatus->sum('count'); @endphp
                    @foreach($byStatus as $row)
                    @php $b = match($row->status){'recovered'=>'success','closed'=>'dark','escalated'=>'danger','pickup_scheduled'=>'info','contacted'=>'primary','open'=>'warning text-dark',default=>'secondary'}; @endphp
                    <tr>
                        <td><span class="badge bg-{{ $b }} me-1 small">&nbsp;</span>{{ ucwords(str_replace('_',' ',$row->status)) }}</td>
                        <td class="text-end fw-bold">{{ $row->count }}</td>
                    </tr>
                    @endforeach
                    <tr class="table-light fw-bold"><td>Total</td><td class="text-end">{{ $totalCases }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Cases by Trigger Reason</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Reason</th><th class="text-end">Count</th></tr></thead>
                    <tbody>
                    @foreach($byReason as $row)
                    <tr>
                        <td>{{ ucwords(str_replace('_',' ',$row->trigger_reason)) }}</td>
                        <td class="text-end fw-bold">{{ $row->count }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        @if($overdue->isNotEmpty())
        <div class="card mb-3 border-danger">
            <div class="card-header bg-danger bg-opacity-10 text-danger"><strong><i class="bi bi-exclamation-triangle-fill me-1"></i>Overdue Cases ({{ $overdue->count() }})</strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Case #</th><th>Device</th><th>Employee</th><th>Due</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach($overdue as $case)
                    <tr>
                        <td><a href="{{ route('recovery.show',$case) }}" class="font-monospace text-decoration-none">{{ $case->case_number }}</a></td>
                        <td class="font-monospace small">{{ $case->device?->asset_tag }}</td>
                        <td class="small">{{ $case->employee?->name }}</td>
                        <td class="text-danger fw-bold small">{{ $case->recovery_due_date?->format('d M Y') }}</td>
                        <td><span class="badge bg-warning text-dark small">{{ ucwords(str_replace('_',' ',$case->status)) }}</span></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><strong>Recent Cases</strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Case #</th><th>Device</th><th>Employee</th><th>Client</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($recentCases as $case)
                    @php $cBadge = match($case->status){'recovered'=>'success','closed'=>'dark','escalated'=>'danger','pickup_scheduled'=>'info','contacted'=>'primary','open'=>'warning text-dark',default=>'secondary'}; @endphp
                    <tr>
                        <td><a href="{{ route('recovery.show',$case) }}" class="font-monospace text-decoration-none small">{{ $case->case_number }}</a></td>
                        <td class="font-monospace small">{{ $case->device?->asset_tag }}</td>
                        <td class="small">{{ $case->employee?->name }}</td>
                        <td class="small">{{ $case->client?->name }}</td>
                        <td><span class="badge bg-{{ $cBadge }} small">{{ ucwords(str_replace('_',' ',$case->status)) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No cases</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
