@extends('layouts.main')
@section('title','Device Tracking Report')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Device Tracking</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt me-2"></i>Device Tracking</h5>
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="employee_id">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->employee_code }} — {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="location_id">
                    <option value="">All Warehouses</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm" name="group" placeholder="Group" value="{{ request('group') }}">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Statuses</option>
                    @foreach(['in_stock','assigned','in_transit','under_repair','disposed','lost'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="mdm">
                    <option value="">MDM: Any</option>
                    <option value="installed" {{ request('mdm') == 'installed' ? 'selected' : '' }}>MDM Installed</option>
                    <option value="not_installed" {{ request('mdm') == 'not_installed' ? 'selected' : '' }}>Not Enrolled</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('reports.device-tracking') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                    <th>Serial / IMEI</th>
                    <th>Model</th>
                    <th>Warehouse</th>
                    <th>Employee</th>
                    <th>Group</th>
                    <th>Status</th>
                    <th>MDM Status</th>
                    <th>Last Handover</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                <tr>
                    <td class="font-monospace fw-bold">
                        <a href="{{ route('devices.show', $device) }}" class="text-decoration-none">{{ $device->asset_tag }}</a>
                    </td>
                    <td class="font-monospace small">{{ $device->serial_number }}<br>{{ $device->imei1 ?? '—' }}</td>
                    <td>{{ $device->model?->brand?->name }} {{ $device->model?->model_name }}</td>
                    <td>{{ $device->currentLocation?->name ?? '—' }}</td>
                    <td>
                        @if($device->currentEmployee)
                            {{ $device->currentEmployee->employee_code }} — {{ $device->currentEmployee->name }}
                        @else
                            —
                        @endif
                    </td>
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
                    <td>{{ $device->latestHandover?->handover_date?->format('Y-m-d') ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">No devices found.</td>
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
