@extends('layouts.public')

@section('title', 'Device ' . $device->asset_tag)

@section('content')

<div class="card mb-3">
    <div class="card-header"><i class="bi bi-phone me-1"></i> Device</div>
    <div class="card-body">
        <h5 class="fw-bold mb-1">{{ $device->model?->brand?->name }} {{ $device->model?->model_name }}</h5>
        <div class="text-muted small mb-2 font-monospace">{{ $device->asset_tag }}</div>
        <span class="badge bg-primary badge-status">{{ ucwords(str_replace('_', ' ', $device->lifecycle_status)) }}</span>
        <span class="badge bg-secondary badge-status">{{ ucfirst($device->condition) }}</span>

        <table class="table table-sm table-borderless mt-3 mb-0">
            <tr><td class="text-muted w-50">Serial No.</td><td class="font-monospace">{{ $device->serial_number ?? '—' }}</td></tr>
            <tr><td class="text-muted">Color</td><td>{{ $device->color ?? '—' }}</td></tr>
        </table>
    </div>
</div>

@if($device->currentEmployee)
    {{-- Assigned: show custodian --}}
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-person-check me-1"></i> Current Custodian</div>
        <div class="card-body">
            <table class="table table-sm table-borderless mb-0">
                <tr><td class="text-muted w-50">Name</td><td class="fw-semibold">{{ $device->currentEmployee->name }}</td></tr>
                <tr><td class="text-muted">Employee Code</td><td class="font-monospace">{{ $device->currentEmployee->employee_code ?? '—' }}</td></tr>
                <tr><td class="text-muted">Designation</td><td>{{ $device->currentEmployee->designation ?? '—' }}</td></tr>
                <tr><td class="text-muted">Department</td><td>{{ $device->currentEmployee->department ?? '—' }}</td></tr>
                <tr><td class="text-muted">Client</td><td>{{ $device->client?->name ?? '—' }}</td></tr>
            </table>
        </div>
    </div>

    @if($device->handovers->isNotEmpty())
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-clock-history me-1"></i> Assignment History</div>
        <ul class="list-group list-group-flush">
            @foreach($device->handovers as $handover)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">{{ $handover->employee?->name ?? '—' }}</div>
                        <div class="text-muted small">{{ $handover->handover_date?->format('d M Y') }}</div>
                    </div>
                    <span class="badge bg-light text-dark border">{{ ucfirst($handover->status) }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

@elseif($pendingRequest)
    {{-- Unassigned, but a link request is already awaiting approval --}}
    <div class="card mb-3">
        <div class="card-body text-center">
            <i class="bi bi-hourglass-split fs-2 text-warning"></i>
            <h6 class="fw-bold mt-2">Link Request Pending</h6>
            <p class="text-muted small mb-0">
                A request to link this device to <strong>{{ $pendingRequest->employee?->name }}</strong>
                was submitted on {{ $pendingRequest->created_at->format('d M Y, h:i A') }} and is awaiting admin approval.
            </p>
        </div>
    </div>

@else
    {{-- Unassigned: allow finder to self-link via employee code --}}
    <div class="card mb-3" id="find-card">
        <div class="card-header"><i class="bi bi-question-circle me-1"></i> Not Yet Assigned</div>
        <div class="card-body">
            <p class="text-muted small">If this is your device, enter your employee code to request linking it to you.</p>
            <div id="lookup-error" class="alert alert-danger py-2 small d-none"></div>
            <div class="input-group">
                <input type="text" id="employee_code" class="form-control form-control-lg" placeholder="Employee Code">
                <button class="btn btn-primary btn-lg" id="find-me-btn" type="button">Find Me</button>
            </div>
        </div>
    </div>

    <div class="card mb-3 d-none" id="confirm-card">
        <div class="card-header"><i class="bi bi-person-check me-1"></i> Confirm It's You</div>
        <div class="card-body">
            <table class="table table-sm table-borderless mb-3">
                <tr><td class="text-muted w-50">Name</td><td class="fw-semibold" id="confirm-name"></td></tr>
                <tr><td class="text-muted">Employee Code</td><td class="font-monospace" id="confirm-code"></td></tr>
                <tr><td class="text-muted">Designation</td><td id="confirm-designation"></td></tr>
                <tr><td class="text-muted">Department</td><td id="confirm-department"></td></tr>
                <tr><td class="text-muted">Client</td><td id="confirm-client"></td></tr>
            </table>
            <div id="request-error" class="alert alert-danger py-2 small d-none"></div>
            <div class="d-grid gap-2">
                <button class="btn btn-primary btn-lg" id="request-link-btn" type="button">Yes, This Is Me — Request Link</button>
                <button class="btn btn-outline-secondary" id="not-me-btn" type="button">Not Me</button>
            </div>
        </div>
    </div>

    <div class="card mb-3 d-none" id="success-card">
        <div class="card-body text-center">
            <i class="bi bi-check-circle fs-2 text-success"></i>
            <h6 class="fw-bold mt-2">Request Submitted</h6>
            <p class="text-muted small mb-0" id="success-message"></p>
        </div>
    </div>
@endif

@include('scan._help')

@endsection

@push('scripts')
<script>
(function () {
    const findBtn = document.getElementById('find-me-btn');
    if (!findBtn) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const codeInput = document.getElementById('employee_code');
    const lookupError = document.getElementById('lookup-error');
    const requestError = document.getElementById('request-error');
    const findCard = document.getElementById('find-card');
    const confirmCard = document.getElementById('confirm-card');
    const successCard = document.getElementById('success-card');

    const lookupUrl = '{{ route('scan.lookup-employee', ['device' => $device->qr_token]) }}';
    const requestUrl = '{{ route('scan.request-link', ['device' => $device->qr_token]) }}';

    findBtn.addEventListener('click', async function () {
        lookupError.classList.add('d-none');
        const code = codeInput.value.trim();
        if (!code) return;

        findBtn.disabled = true;
        try {
            const res = await fetch(lookupUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ employee_code: code }),
            });
            const data = await res.json();

            if (!res.ok || !data.found) {
                lookupError.textContent = data.message || 'Employee not found.';
                lookupError.classList.remove('d-none');
                return;
            }

            document.getElementById('confirm-name').textContent = data.employee.name || '—';
            document.getElementById('confirm-code').textContent = data.employee.employee_code || '—';
            document.getElementById('confirm-designation').textContent = data.employee.designation || '—';
            document.getElementById('confirm-department').textContent = data.employee.department || '—';
            document.getElementById('confirm-client').textContent = data.employee.client || '—';

            findCard.classList.add('d-none');
            confirmCard.classList.remove('d-none');
        } catch (e) {
            lookupError.textContent = 'Something went wrong. Please try again.';
            lookupError.classList.remove('d-none');
        } finally {
            findBtn.disabled = false;
        }
    });

    document.getElementById('not-me-btn').addEventListener('click', function () {
        confirmCard.classList.add('d-none');
        findCard.classList.remove('d-none');
        codeInput.value = '';
    });

    document.getElementById('request-link-btn').addEventListener('click', async function () {
        requestError.classList.add('d-none');
        const btn = this;
        btn.disabled = true;

        try {
            const res = await fetch(requestUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ employee_code: codeInput.value.trim() }),
            });
            const data = await res.json();

            if (!res.ok) {
                requestError.textContent = data.message || 'Could not submit request.';
                requestError.classList.remove('d-none');
                btn.disabled = false;
                return;
            }

            document.getElementById('success-message').textContent = data.message;
            confirmCard.classList.add('d-none');
            successCard.classList.remove('d-none');
        } catch (e) {
            requestError.textContent = 'Something went wrong. Please try again.';
            requestError.classList.remove('d-none');
            btn.disabled = false;
        }
    });
})();
</script>
@endpush
