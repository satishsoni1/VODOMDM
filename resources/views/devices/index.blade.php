@extends('layouts.main')

@section('title', 'Devices')

@section('breadcrumb')
    <li class="breadcrumb-item active">Devices</li>
@endsection

@section('content')
@if(session('device_import_summary'))
@php $dis = session('device_import_summary'); @endphp
<div class="alert alert-{{ count($dis['errors']) ? 'warning' : 'success' }} alert-dismissible fade show">
    <strong>Import Complete:</strong>
    {{ $dis['imported'] }} created, {{ $dis['updated'] }} updated, {{ $dis['skipped'] }} skipped,
    {{ $dis['mdm_matched'] }} auto-linked to MDM.
    @if(count($dis['errors']))
    <ul class="mb-0 mt-2">
        @foreach($dis['errors'] as $err)<li class="small">{{ $err }}</li>@endforeach
    </ul>
    @endif
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-phone me-2"></i>Device Registry</h5>
    <div>
        <a href="{{ route('devices.template') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-spreadsheet"></i> Template
        </a>
        <a href="{{ route('devices.import.form') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-cloud-upload"></i> Import Devices
        </a>
        <a href="{{ route('devices.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add Device
        </a>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="text-muted small" id="selected-count">0 selected</div>
    <div>
        <button type="submit" form="labels-form" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-qr-code"></i> Print Selected QR Labels
        </button>
        <a href="{{ route('devices.labels', array_merge(request()->query(), ['all' => 1])) }}" target="_blank" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-qr-code-scan"></i> Print All Labels (this filter)
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" name="q" placeholder="Serial / IMEI / Asset Tag" value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Statuses</option>
                    @foreach(['in_stock','assigned','in_transit','under_repair','disposed','lost'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('devices.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('devices.labels') }}" target="_blank" id="labels-form">
    @csrf
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-dark">
                    <tr>
                        <th><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Asset Tag</th>
                        <th>Serial Number</th>
                        <th>Model</th>
                        <th>IMEI 1</th>
                        <th>Warehouse</th>
                        <th>Assigned To</th>
                        <th>Group</th>
                        <th>Status</th>
                        <th>MDM Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                    <tr>
                        <td><input type="checkbox" class="form-check-input device-checkbox" name="device_ids[]" value="{{ $device->id }}"></td>
                        <td class="font-monospace fw-bold">
                            <a href="{{ route('devices.show', $device) }}" class="text-decoration-none">{{ $device->asset_tag }}</a>
                        </td>
                        <td class="font-monospace small">{{ $device->serial_number }}</td>
                        <td>{{ $device->model?->brand?->name }} {{ $device->model?->model_name }}</td>
                        <td class="font-monospace small">{{ $device->imei1 ?? '—' }}</td>
                        <td>{{ $device->currentLocation?->name ?? '—' }}</td>
                        <td>{{ $device->currentEmployee?->name ?? '—' }}</td>
                        <td>{{ $device->current_group ?? '—' }}</td>
                        <td>
                            @php
                                $statusColors = [
                                    'in_stock' => 'success', 'assigned' => 'primary', 'activated' => 'primary',
                                    'in_transit' => 'warning text-dark', 'under_repair' => 'warning text-dark',
                                    'lost' => 'danger', 'disposed' => 'dark', 'written_off' => 'dark',
                                    'received' => 'info', 'enrolled' => 'success',
                                ];
                                $color = $statusColors[$device->lifecycle_status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }} badge-status">{{ ucwords(str_replace('_', ' ', $device->lifecycle_status)) }}</span>
                        </td>
                        <td>
                            @if($device->mdmDevice)
                                <span class="badge bg-success">MDM Installed</span>
                            @else
                                <span class="badge bg-secondary">Not Enrolled</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('devices.show', $device) }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-1">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4 text-muted">No devices found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($devices->hasPages())
        <div class="card-footer">
            {{ $devices->links() }}
        </div>
        @endif
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.device-checkbox');
    const countLabel = document.getElementById('selected-count');

    function updateCount() {
        const checked = document.querySelectorAll('.device-checkbox:checked').length;
        countLabel.textContent = checked + ' selected';
    }

    selectAll?.addEventListener('change', function () {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateCount();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));
})();
</script>
@endpush
