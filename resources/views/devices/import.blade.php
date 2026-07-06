@extends('layouts.main')
@section('title','Import Devices')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('devices.index') }}">Devices</a></li>
    <li class="breadcrumb-item active">Bulk Import</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-8">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-cloud-upload me-2"></i>Bulk Import Devices</h5>
    <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-info-circle me-2 text-primary"></i>Instructions</div>
    <div class="card-body">
        <ol class="mb-2">
            <li>Download the <a href="{{ route('devices.template') }}"><i class="bi bi-download"></i> CSV template</a> — column order is already set correctly.</li>
            <li>Fill data from row 2 onward. Do <strong>not</strong> change the header row.</li>
            <li>
                <strong>Identifier:</strong> at least one of <code>asset_tag</code>, <code>serial_number</code>, <code>imei1</code>, <code>imei2</code> is required per row.
                <br><span class="text-muted small">If a device with a matching identifier already exists, it is <strong>updated</strong>; otherwise a new device is <strong>created</strong>.</span>
            </li>
            <li>
                <strong>brand / model_name</strong> — resolves to a Device Model. If the brand or model doesn't exist yet, it is created automatically.
            </li>
            <li>
                <strong>warehouse_code / warehouse_name</strong> — resolves to a warehouse Location and sets the device's current location. If it doesn't exist, it is created automatically as a warehouse.
            </li>
            <li>
                <strong>vendor_code / vendor_name</strong> — optional; created automatically if not found.
            </li>
            <li>
                <strong>client_code</strong> — optional; must match an existing Client's code exactly (not auto-created). Tags the device directly
                to that client so it appears in the client's portal login even before it's assigned to a specific employee.
            </li>
            <li>
                <strong>MDM auto-match:</strong> after import, each device's IMEI/serial number is checked against synced MDM records. If a match is found, the device is automatically linked and shows as <span class="badge bg-success">MDM Installed</span> on the device list.
            </li>
            <li><code>condition</code> values: <code>new</code>, <code>good</code>, <code>fair</code>, <code>poor</code>, <code>damaged</code>.</li>
            <li>Dates: <code>YYYY-MM-DD</code>. Excel date cells are accepted automatically.</li>
        </ol>
        <a href="{{ route('devices.template') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Download CSV Template
        </a>
    </div>
</div>

<div class="card" id="uploadCard">
    <div class="card-header fw-semibold"><i class="bi bi-upload me-2"></i>Upload File</div>
    <div class="card-body">
        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form id="importForm" method="POST" action="{{ route('devices.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Select CSV or Excel file *</label>
                <input type="file" class="form-control @error('file') is-invalid @enderror"
                       id="fileInput" name="file" accept=".csv,.xls,.xlsx" required>
                @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Accepted: .csv, .xls, .xlsx</div>
            </div>
            <button type="submit" id="submitBtn" class="btn btn-primary">
                <i class="bi bi-cloud-upload me-1"></i> Upload & Import
            </button>
        </form>
    </div>
</div>

{{-- Progress overlay --}}
<div id="progressCard" class="card d-none mt-3">
    <div class="card-body py-4">
        <div class="text-center mb-3">
            <div class="spinner-border text-primary mb-2" role="status" id="spinner"></div>
            <div class="fw-semibold fs-6" id="progressStatus">Uploading file...</div>
            <div class="text-muted small mt-1" id="progressDetail"></div>
        </div>

        <div class="progress" style="height: 22px; border-radius: 6px;">
            <div id="progressBar"
                 class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                 role="progressbar"
                 style="width: 0%; transition: width 0.4s ease;"
                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                <span id="progressPct" class="fw-bold">0%</span>
            </div>
        </div>
    </div>
</div>

</div></div>
@endsection

@push('scripts')
<script>
document.getElementById('importForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const form       = this;
    const submitBtn  = document.getElementById('submitBtn');
    const progCard   = document.getElementById('progressCard');
    const bar        = document.getElementById('progressBar');
    const pctLabel   = document.getElementById('progressPct');
    const statusEl   = document.getElementById('progressStatus');
    const detailEl   = document.getElementById('progressDetail');
    const spinner    = document.getElementById('spinner');

    const fileInput = document.getElementById('fileInput');
    if (!fileInput.files.length) {
        alert('Please select a file first.');
        return;
    }

    submitBtn.disabled = true;
    progCard.classList.remove('d-none');
    progCard.scrollIntoView({ behavior: 'smooth', block: 'center' });

    function setBar(pct, status, detail) {
        const p = Math.min(Math.round(pct), 100);
        bar.style.width   = p + '%';
        bar.setAttribute('aria-valuenow', p);
        pctLabel.textContent = p + '%';
        if (status) statusEl.textContent = status;
        if (detail !== undefined) detailEl.textContent = detail;
    }

    const xhr    = new XMLHttpRequest();
    const fmData = new FormData(form);

    xhr.upload.addEventListener('progress', function (ev) {
        if (ev.lengthComputable) {
            const uploadPct = (ev.loaded / ev.total) * 40;
            setBar(uploadPct, 'Uploading file...',
                   formatBytes(ev.loaded) + ' / ' + formatBytes(ev.total));
        }
    });

    xhr.upload.addEventListener('load', function () {
        setBar(40, 'Processing rows...', 'This may take a minute for large files...');
        startProcessingAnimation();
    });

    xhr.addEventListener('load', function () {
        clearInterval(window._processingTimer);
        try {
            const resp = JSON.parse(xhr.responseText);
            if (resp.redirect) {
                setBar(100, 'Import complete!', 'Redirecting...');
                bar.classList.remove('progress-bar-animated');
                bar.classList.add('bg-success');
                spinner.classList.add('d-none');
                setTimeout(() => { window.location.href = resp.redirect; }, 600);
                return;
            }
        } catch (_) {}
        setBar(100, 'Done!', 'Redirecting...');
        setTimeout(() => { window.location.href = '{{ route('devices.index') }}'; }, 800);
    });

    xhr.addEventListener('error', function () {
        clearInterval(window._processingTimer);
        setBar(0, 'Upload failed.', 'Please try again.');
        bar.classList.add('bg-danger');
        bar.classList.remove('progress-bar-animated');
        submitBtn.disabled = false;
    });

    xhr.open('POST', form.action);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(fmData);

    function startProcessingAnimation() {
        let current = 40;
        window._processingTimer = setInterval(function () {
            if (current < 88) {
                current += (90 - current) * 0.025;
                setBar(current);
            }
        }, 400);
    }

    function formatBytes(bytes) {
        if (bytes < 1024)       return bytes + ' B';
        if (bytes < 1048576)    return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }
});
</script>
@endpush
