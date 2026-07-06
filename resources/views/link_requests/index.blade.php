@extends('layouts.main')

@section('title', 'QR Link Requests')

@section('breadcrumb')
    <li class="breadcrumb-item active">QR Link Requests</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-qr-code-scan me-2"></i>Device Link Requests</h5>
    <div class="btn-group btn-group-sm">
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
            <a href="{{ route('link-requests.index', ['status' => $key]) }}"
               class="btn {{ $status === $key ? 'btn-primary' : 'btn-outline-primary' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Device</th>
                    <th>Requested Employee</th>
                    <th>Entered Code</th>
                    <th>Requested At</th>
                    <th>Status</th>
                    <th>Reviewed By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($linkRequests as $lr)
                    <tr>
                        <td>
                            <a href="{{ route('devices.show', $lr->device_id) }}">{{ $lr->device->asset_tag }}</a>
                            <div class="text-muted small">{{ $lr->device->model?->brand?->name }} {{ $lr->device->model?->model_name }}</div>
                        </td>
                        <td>
                            {{ $lr->employee?->name }}
                            <div class="text-muted small">{{ $lr->employee?->designation }}</div>
                        </td>
                        <td class="font-monospace">{{ $lr->employee_code_entered }}</td>
                        <td>{{ $lr->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            @if($lr->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($lr->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                                @if($lr->rejection_reason)
                                    <div class="text-muted small">{{ $lr->rejection_reason }}</div>
                                @endif
                            @endif
                        </td>
                        <td>{{ $lr->reviewer?->name ?? '—' }}</td>
                        <td class="text-end">
                            @if($lr->status === 'pending')
                                <form action="{{ route('link-requests.approve', $lr) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success" onclick="return confirm('Approve this link request? The device will be assigned to {{ addslashes($lr->employee?->name) }}.')">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $lr->id }}">
                                    <i class="bi bi-x-lg"></i> Reject
                                </button>

                                <div class="modal fade" id="rejectModal{{ $lr->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form action="{{ route('link-requests.reject', $lr) }}" method="POST">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title">Reject Link Request</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <label class="form-label small text-muted">Reason (optional)</label>
                                                    <textarea name="rejection_reason" class="form-control" rows="2"></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No {{ $status !== 'all' ? $status : '' }} link requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $linkRequests->links() }}
</div>
@endsection
