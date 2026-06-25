@extends('layouts.main')
@section('title','API Logs')
@section('breadcrumb')
    <li class="breadcrumb-item active">API Logs</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:var(--gs-teal-dark)">
        <i class="bi bi-journal-code me-2"></i>API Run Logs
    </h4>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([['Total Runs','total','teal','bi-list-ul'],['Success','success','success','bi-check-circle'],['Failed','failed','danger','bi-x-circle'],['Running','running','warning','bi-arrow-repeat']] as [$label,$key,$color,$icon])
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                    style="width:40px;height:40px;background:var(--gs-teal-light)">
                    <i class="bi {{ $icon }}" style="color:var(--gs-teal);font-size:1.1rem"></i>
                </div>
                <div>
                    <div class="fw-bold fs-5" style="color:var(--gs-teal-dark)">{{ $stats[$key] }}</div>
                    <div class="text-muted small">{{ $label }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    @foreach(['mdm_sync','employee_api','whatsapp'] as $t)
                    <option value="{{ $t }}" @selected(request('type')===$t)>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    @foreach(['success','failed','running'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['type','status']))
            <div class="col-auto">
                <a href="{{ route('api-logs.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Log Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($logs->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-25"></i>No API runs yet.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0 small align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Time</th>
                        <th>Type</th>
                        <th>Action</th>
                        <th>Summary</th>
                        <th class="text-center">In</th>
                        <th class="text-center">Out</th>
                        <th class="text-center">Steps</th>
                        <th class="text-center">Duration</th>
                        <th>By</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td class="ps-3 text-nowrap">
                            <div class="fw-semibold">{{ $log->created_at->format('d M H:i') }}</div>
                            <div class="text-muted" style="font-size:.7rem">{{ $log->created_at->format('Y') }}</div>
                        </td>
                        <td>
                            @php $typeColor = match($log->type) { 'mdm_sync'=>'teal','employee_api'=>'primary','whatsapp'=>'success', default=>'secondary' }; @endphp
                            <span class="badge bg-{{ $typeColor }}-subtle text-{{ $typeColor }} border" style="font-size:.72rem">
                                {{ str_replace('_',' ', ucfirst($log->type)) }}
                            </span>
                        </td>
                        <td class="fw-semibold font-monospace" style="font-size:.78rem">{{ $log->action }}</td>
                        <td style="max-width:260px">
                            <div class="text-truncate" title="{{ $log->summary }}">{{ $log->summary ?? '—' }}</div>
                        </td>
                        <td class="text-center">{{ $log->records_in ?? '—' }}</td>
                        <td class="text-center">{{ $log->records_out ?? '—' }}</td>
                        <td class="text-center">
                            @php $stepCount = \App\Models\ApiLog::where('parent_log_id',$log->id)->count(); @endphp
                            @if($stepCount)
                            <span class="badge bg-light text-secondary border">{{ $stepCount }}</span>
                            @else —
                            @endif
                        </td>
                        <td class="text-center text-muted">
                            {{ $log->duration_ms ? number_format($log->duration_ms/1000,1).'s' : '—' }}
                        </td>
                        <td class="text-nowrap">{{ $log->triggeredBy?->name ?? 'System' }}</td>
                        <td>
                            <span class="badge bg-{{ $log->statusBadgeClass() }}-subtle text-{{ $log->statusBadgeClass() }} border">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                        <td class="pe-3">
                            <a href="{{ route('api-logs.show', $log) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
