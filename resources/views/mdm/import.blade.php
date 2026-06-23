@extends('layouts.main')
@section('title','MDM — Import & Sync Log')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mdm.index') }}">MDM</a></li>
    <li class="breadcrumb-item active">Import & Sync Log</li>
@endsection

@push('styles')
<style>
    .upload-zone { border: 2px dashed #dee2e6; border-radius: 12px; transition: border-color .2s, background .2s; cursor: pointer; }
    .upload-zone.drag-over, .upload-zone:hover { border-color: #0d6efd; background: #f0f7ff; }
    .upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .log-status-completed { color: #198754; }
    .log-status-failed    { color: #dc3545; }
</style>
@endpush

@section('content')
<div class="row g-4">

    {{-- ── UPLOAD PANEL ──────────────────────────────────────────────────── --}}
    <div class="col-xl-5">

        {{-- Last import summary --}}
        @if($lastLog)
        <div class="alert {{ $lastLog->status === 'completed' ? 'alert-success' : 'alert-danger' }} border-0 mb-4">
            <div class="fw-bold mb-1">
                <i class="bi bi-{{ $lastLog->status === 'completed' ? 'check-circle' : 'x-circle' }} me-2"></i>
                Last Import — {{ $lastLog->created_at->format('d M Y H:i') }}
            </div>
            <div class="small">{{ $lastLog->notes }}</div>
            <div class="small text-muted mt-1">File: {{ $lastLog->filename }} &nbsp;|&nbsp; By: {{ $lastLog->importedBy?->name ?? 'System' }}</div>
        </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Bulk Import MDM Data (CSV)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Export device data from your Headwind MDM portal as CSV and upload here.
                    The system will <strong>upsert</strong> (create new + update existing) records and
                    auto-match devices to employees by IMEI/Serial.
                </p>

                <form method="POST" action="{{ route('mdm.import.process') }}" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="upload-zone position-relative text-center p-5 mb-3" id="uploadZone">
                        <input type="file" name="csv_file" id="csvFile" accept=".csv,.txt" required>
                        <div id="uploadPlaceholder">
                            <i class="bi bi-file-earmark-spreadsheet fs-1 text-primary d-block mb-2"></i>
                            <div class="fw-semibold">Drag & drop CSV file here</div>
                            <div class="text-muted small">or click to browse &middot; max 20 MB</div>
                        </div>
                        <div id="uploadSelected" class="d-none">
                            <i class="bi bi-file-earmark-check fs-1 text-success d-block mb-2"></i>
                            <div class="fw-semibold" id="selectedFileName">—</div>
                            <div class="text-muted small" id="selectedFileSize">—</div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" id="importBtn">
                            <i class="bi bi-cloud-upload me-2"></i>Upload & Import
                        </button>
                    </div>
                </form>

                <hr class="my-3">

                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('mdm.auto-match') }}" class="flex-grow-1">
                        @csrf
                        <button class="btn btn-outline-info w-100 btn-sm">
                            <i class="bi bi-link-45deg me-1"></i>Run Auto-Match Only (IMEI / Serial)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Expected CSV Format --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header"><strong><i class="bi bi-table me-2"></i>Expected CSV Column Order</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small">
                    <thead class="table-light"><tr><th>#</th><th>Column Header</th><th>Example</th></tr></thead>
                    <tbody>
                        @php
                        $cols = [
                            [1,'Device number','1000089'],
                            [2,'IMEI',''],
                            [3,'Serial number','RZGL3083E0A'],
                            [4,'Phone',''],
                            [5,'Description',''],
                            [6,'Group','Zorvia'],
                            [7,'Configuration','Zorvia'],
                            [8,'Launcher version','6.29'],
                            [9,'Device Status','off'],
                            [10,'Permission Status','All permissions are granted'],
                            [11,'Installation Status','- App: installed 1.0, available 1.1'],
                            [12,'Sync time','20-06-2026 17:12:47'],
                            [13,'Model','SM-X230'],
                            [14,'Default launcher','com.hmdm.launcher'],
                            [15,'IP address','152.59.155.133'],
                            [16,'MDM mode','true'],
                            [17,'Kiosk mode','false'],
                            [18,'Enrollment date','15-04-2026 17:44:34'],
                            [19,'Android version','16'],
                            [20,'Location',''],
                            [21,'Division',''],
                            [22,'Status',''],
                        ];
                        @endphp
                        @foreach($cols as $c)
                        <tr>
                            <td class="text-muted">{{ $c[0] }}</td>
                            <td class="fw-semibold">{{ $c[1] }}</td>
                            <td class="text-muted font-monospace">{{ $c[2] ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── IMPORT LOG ───────────────────────────────────────────────────────── --}}
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <strong><i class="bi bi-journal-text me-2 text-primary"></i>Import History</strong>
                <span class="badge bg-secondary">{{ $logs->total() }} imports</span>
            </div>

            {{-- Summary strip --}}
            <div class="bg-light border-bottom px-4 py-2">
                <div class="row g-2 text-center small">
                    <div class="col-3">
                        <div class="fw-bold text-primary">{{ $totalDevices }}</div>
                        <div class="text-muted">Total in DB</div>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold text-success">{{ $logs->sum('imported') }}</div>
                        <div class="text-muted">Lifetime New</div>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold text-info">{{ $logs->sum('updated') }}</div>
                        <div class="text-muted">Lifetime Updated</div>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold text-secondary">{{ $logs->total() }}</div>
                        <div class="text-muted">Import Runs</div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                @if($logs->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                    No imports yet. Upload a CSV file to begin.
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date / Time</th>
                                <th>File</th>
                                <th class="text-center">Rows</th>
                                <th class="text-center">New</th>
                                <th class="text-center">Updated</th>
                                <th class="text-center">Matched</th>
                                <th class="text-center">Skip</th>
                                <th>By</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            <tr>
                                <td class="text-nowrap">
                                    <div class="fw-semibold">{{ $log->created_at->format('d M Y') }}</div>
                                    <div class="text-muted">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-truncate" style="max-width:150px" title="{{ $log->filename }}">{{ $log->filename }}</div>
                                </td>
                                <td class="text-center">{{ $log->total_rows }}</td>
                                <td class="text-center text-success fw-semibold">{{ $log->imported }}</td>
                                <td class="text-center text-info fw-semibold">{{ $log->updated }}</td>
                                <td class="text-center text-primary">{{ $log->auto_matched }}</td>
                                <td class="text-center text-muted">{{ $log->skipped }}</td>
                                <td class="text-nowrap">{{ $log->importedBy?->name ?? '—' }}</td>
                                <td>
                                    @php $sc = $log->status === 'completed' ? 'success' : 'danger'; @endphp
                                    <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} border">
                                        {{ ucfirst($log->status) }}
                                    </span>
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
    </div>
</div>
@endsection

@push('scripts')
<script>
const zone     = document.getElementById('uploadZone');
const fileInput= document.getElementById('csvFile');
const ph       = document.getElementById('uploadPlaceholder');
const sel      = document.getElementById('uploadSelected');
const selName  = document.getElementById('selectedFileName');
const selSize  = document.getElementById('selectedFileSize');

function showFile(file) {
    if (!file) return;
    selName.textContent = file.name;
    selSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
    ph.classList.add('d-none');
    sel.classList.remove('d-none');
}

fileInput.addEventListener('change', () => showFile(fileInput.files[0]));

zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        showFile(file);
    }
});

document.getElementById('importForm').addEventListener('submit', function() {
    const btn = document.getElementById('importBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Importing…';
});
</script>
@endpush
