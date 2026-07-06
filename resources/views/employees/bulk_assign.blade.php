@extends('layouts.main')
@section('title','Bulk Assignment')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">Bulk Assignment</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-9">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-link-45deg me-2"></i>Bulk Assignment</h5>
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3" id="assignTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="company-tab" data-bs-toggle="tab" data-bs-target="#company-pane"
                type="button" role="tab">
            <i class="bi bi-building me-1"></i> Assign Company to Employees
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="device-tab" data-bs-toggle="tab" data-bs-target="#device-pane"
                type="button" role="tab">
            <i class="bi bi-phone me-1"></i> Assign Devices to Employees
        </button>
    </li>
</ul>

<div class="tab-content" id="assignTabContent">

    {{-- ── TAB 1: Company Assignment ──────────────────────────────────── --}}
    <div class="tab-pane fade show active" id="company-pane" role="tabpanel">

        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-info-circle me-2 text-primary"></i>How it works</div>
            <div class="card-body">
                <p class="mb-2">Upload a CSV/Excel with two columns to link employees to a company (Client).</p>
                <ol class="mb-2">
                    <li>Download the <a href="{{ route('employees.bulk-assign.company-template') }}"><i class="bi bi-download"></i> Company Assignment Template</a>.</li>
                    <li>Fill each row: <code>company_code</code> = Client Code, <code>employee_code</code> = Employee Code.</li>
                    <li>Upload — existing employees are updated; unknown employees/companies are reported as errors.</li>
                </ol>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered w-auto mb-0">
                        <thead class="table-light"><tr><th>company_code</th><th>employee_code</th></tr></thead>
                        <tbody>
                            <tr><td>CLIENT001</td><td>EMP001</td></tr>
                            <tr><td>CLIENT001</td><td>EMP002</td></tr>
                            <tr><td>CLIENT002</td><td>EMP050</td></tr>
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('employees.bulk-assign.company-template') }}" class="btn btn-outline-success btn-sm mt-2">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Download Template
                </a>
            </div>
        </div>

        <div class="card" id="companyUploadCard">
            <div class="card-header fw-semibold"><i class="bi bi-upload me-2"></i>Upload File</div>
            <div class="card-body">
                <form id="companyForm" method="POST" action="{{ route('employees.bulk-assign.company') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select CSV or Excel file *</label>
                        <input type="file" class="form-control" id="companyFile" name="file"
                               accept=".csv,.xls,.xlsx" required>
                        <div class="form-text">Accepted: .csv, .xls, .xlsx</div>
                    </div>
                    <button type="submit" id="companySubmitBtn" class="btn btn-primary">
                        <i class="bi bi-cloud-upload me-1"></i> Upload & Assign
                    </button>
                </form>
            </div>
        </div>

        {{-- Progress --}}
        <div id="companyProgressCard" class="card d-none mt-3">
            <div class="card-body py-4">
                <div class="text-center mb-3">
                    <div class="spinner-border text-primary mb-2" role="status" id="companySpinner"></div>
                    <div class="fw-semibold fs-6" id="companyStatus">Uploading file...</div>
                    <div class="text-muted small mt-1" id="companyDetail"></div>
                </div>
                <div class="progress" style="height: 22px; border-radius: 6px;">
                    <div id="companyBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                         role="progressbar" style="width: 0%; transition: width 0.4s ease;"
                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <span id="companyPct" class="fw-bold">0%</span>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /company-pane --}}

    {{-- ── TAB 2: Device Assignment ────────────────────────────────────── --}}
    <div class="tab-pane fade" id="device-pane" role="tabpanel">

        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-info-circle me-2 text-primary"></i>How it works</div>
            <div class="card-body">
                <p class="mb-2">Upload a CSV/Excel to assign devices to employees in bulk. For each row the system:</p>
                <ul class="mb-2">
                    <li>Finds the device by <strong>asset_tag</strong>, <strong>serial_number</strong>, or <strong>imei</strong> (at least one required).</li>
                    <li>Finds the employee by <strong>company_code + employee_code</strong>.</li>
                    <li>Creates a <strong>Handover record</strong>, updates the device status to <em>assigned</em>, and tags it with the optional <strong>group</strong> label for tracking/filtering.</li>
                </ul>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" style="font-size:.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th>company_code</th><th>employee_code</th>
                                <th>asset_tag</th><th>serial_number</th><th>imei</th><th>group</th>
                                <th>handover_date</th><th>handover_location</th>
                                <th>handover_city</th><th>condition</th>
                                <th>accessories</th><th>remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>CLIENT001</td><td>EMP001</td>
                                <td>AST-00123</td><td></td><td></td><td>Sales Team</td>
                                <td>2024-06-01</td><td>Head Office</td>
                                <td>Mumbai</td><td>new</td>
                                <td>Charger, Box</td><td>Initial issue</td>
                            </tr>
                            <tr>
                                <td>CLIENT001</td><td>EMP002</td>
                                <td></td><td>SN9876543</td><td></td><td>Region-North</td>
                                <td>2024-06-01</td><td></td>
                                <td>Delhi</td><td>good</td>
                                <td></td><td></td>
                            </tr>
                            <tr>
                                <td>CLIENT001</td><td>EMP003</td>
                                <td></td><td></td><td>351234567890123</td><td>Region-North</td>
                                <td>2024-06-01</td><td></td>
                                <td>Delhi</td><td>good</td>
                                <td></td><td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mt-2 mb-1"><strong>condition</strong> values: <code>new</code>, <code>good</code>, <code>fair</code>, <code>poor</code> (default: good).</p>
                <p class="text-muted small mb-2">Dates: <code>YYYY-MM-DD</code>. If blank, today's date is used.</p>
                <a href="{{ route('employees.bulk-assign.device-template') }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Download Template
                </a>
            </div>
        </div>

        <div class="card" id="deviceUploadCard">
            <div class="card-header fw-semibold"><i class="bi bi-upload me-2"></i>Upload File</div>
            <div class="card-body">
                <form id="deviceForm" method="POST" action="{{ route('employees.bulk-assign.device') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select CSV or Excel file *</label>
                        <input type="file" class="form-control" id="deviceFile" name="file"
                               accept=".csv,.xls,.xlsx" required>
                        <div class="form-text">Accepted: .csv, .xls, .xlsx</div>
                    </div>
                    <button type="submit" id="deviceSubmitBtn" class="btn btn-primary">
                        <i class="bi bi-cloud-upload me-1"></i> Upload & Assign
                    </button>
                </form>
            </div>
        </div>

        {{-- Progress --}}
        <div id="deviceProgressCard" class="card d-none mt-3">
            <div class="card-body py-4">
                <div class="text-center mb-3">
                    <div class="spinner-border text-primary mb-2" role="status" id="deviceSpinner"></div>
                    <div class="fw-semibold fs-6" id="deviceStatus">Uploading file...</div>
                    <div class="text-muted small mt-1" id="deviceDetail"></div>
                </div>
                <div class="progress" style="height: 22px; border-radius: 6px;">
                    <div id="deviceBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                         role="progressbar" style="width: 0%; transition: width 0.4s ease;"
                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <span id="devicePct" class="fw-bold">0%</span>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /device-pane --}}

</div>{{-- /tab-content --}}

</div></div>
@endsection

@push('scripts')
<script>
function makeUploader(opts) {
    const form      = document.getElementById(opts.formId);
    const submitBtn = document.getElementById(opts.submitBtnId);
    const progCard  = document.getElementById(opts.progressCardId);
    const bar       = document.getElementById(opts.barId);
    const pctLabel  = document.getElementById(opts.pctId);
    const statusEl  = document.getElementById(opts.statusId);
    const detailEl  = document.getElementById(opts.detailId);
    const spinner   = document.getElementById(opts.spinnerId);
    const fileInput = document.getElementById(opts.fileInputId);

    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!fileInput.files.length) { alert('Please select a file first.'); return; }

        submitBtn.disabled = true;
        progCard.classList.remove('d-none');
        progCard.scrollIntoView({ behavior: 'smooth', block: 'center' });

        function setBar(pct, status, detail) {
            const p = Math.min(Math.round(pct), 100);
            bar.style.width = p + '%';
            bar.setAttribute('aria-valuenow', p);
            pctLabel.textContent = p + '%';
            if (status) statusEl.textContent = status;
            if (detail !== undefined) detailEl.textContent = detail;
        }

        function formatBytes(b) {
            if (b < 1024)    return b + ' B';
            if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
            return (b / 1048576).toFixed(1) + ' MB';
        }

        function startAnimation() {
            let c = 40;
            opts._timer = setInterval(function () {
                if (c < 88) { c += (90 - c) * 0.025; setBar(c); }
            }, 400);
        }

        const xhr    = new XMLHttpRequest();
        const fmData = new FormData(form);

        xhr.upload.addEventListener('progress', function (ev) {
            if (ev.lengthComputable) {
                setBar((ev.loaded / ev.total) * 40, 'Uploading file...',
                       formatBytes(ev.loaded) + ' / ' + formatBytes(ev.total));
            }
        });

        xhr.upload.addEventListener('load', function () {
            setBar(40, 'Processing rows...', 'This may take a moment...');
            startAnimation();
        });

        xhr.addEventListener('load', function () {
            clearInterval(opts._timer);
            try {
                const resp = JSON.parse(xhr.responseText);
                if (resp.redirect) {
                    setBar(100, 'Done!', 'Redirecting...');
                    bar.classList.remove('progress-bar-animated');
                    bar.classList.add('bg-success');
                    spinner.classList.add('d-none');
                    setTimeout(() => { window.location.href = resp.redirect; }, 600);
                    return;
                }
            } catch (_) {}
            setBar(100, 'Done!', 'Redirecting...');
            setTimeout(() => { window.location.href = opts.fallbackUrl; }, 800);
        });

        xhr.addEventListener('error', function () {
            clearInterval(opts._timer);
            setBar(0, 'Upload failed.', 'Please try again.');
            bar.classList.add('bg-danger');
            bar.classList.remove('progress-bar-animated');
            submitBtn.disabled = false;
        });

        xhr.open('POST', form.action);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(fmData);
    });
}

makeUploader({
    formId: 'companyForm', submitBtnId: 'companySubmitBtn',
    progressCardId: 'companyProgressCard', barId: 'companyBar',
    pctId: 'companyPct', statusId: 'companyStatus', detailId: 'companyDetail',
    spinnerId: 'companySpinner', fileInputId: 'companyFile',
    fallbackUrl: '{{ route('employees.index') }}',
});

makeUploader({
    formId: 'deviceForm', submitBtnId: 'deviceSubmitBtn',
    progressCardId: 'deviceProgressCard', barId: 'deviceBar',
    pctId: 'devicePct', statusId: 'deviceStatus', detailId: 'deviceDetail',
    spinnerId: 'deviceSpinner', fileInputId: 'deviceFile',
    fallbackUrl: '{{ route('handovers.index') }}',
});
</script>
@endpush
