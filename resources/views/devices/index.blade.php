@extends('layouts.main')

@section('title', 'Devices')

@section('breadcrumb')
    <li class="breadcrumb-item active">Devices</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-phone me-2"></i>Device Registry</h5>
    <a href="{{ route('devices.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Add Device
    </a>
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

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Asset Tag</th>
                    <th>Serial Number</th>
                    <th>Model</th>
                    <th>IMEI 1</th>
                    <th>Client</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                <tr>
                    <td class="font-monospace fw-bold">
                        <a href="{{ route('devices.show', $device) }}" class="text-decoration-none">{{ $device->asset_tag }}</a>
                    </td>
                    <td class="font-monospace small">{{ $device->serial_number }}</td>
                    <td>{{ $device->model?->brand?->name }} {{ $device->model?->model_name }}</td>
                    <td class="font-monospace small">{{ $device->imei1 ?? '—' }}</td>
                    <td>{{ $device->client?->name ?? '—' }}</td>
                    <td>{{ $device->currentEmployee?->name ?? '—' }}</td>
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
                        <a href="{{ route('devices.show', $device) }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-1">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">No devices found.</td>
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
@endsection
