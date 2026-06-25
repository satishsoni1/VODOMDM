@extends('layouts.main')
@section('title','MDM Sync')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mdm.index') }}">MDM</a></li>
    <li class="breadcrumb-item active">Sync</li>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success border-0 alert-dismissible mb-4">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger border-0 alert-dismissible mb-4">
    <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('warning'))
<div class="alert alert-warning border-0 alert-dismissible mb-4">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ── Connection Status ──────────────────────────────────────────────────── --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-4">
            <i class="bi bi-database-fill{{ $pgReady ? '' : '-slash' }} fs-1 mb-2 {{ $pgReady ? 'text-success' : 'text-danger' }}"></i>
            <div class="fw-bold">PostgreSQL {{ $pgReady ? 'Ready' : 'Unavailable' }}</div>
            <div class="text-muted small">{{ $pgHost }} / {{ $pgDb }}</div>
            @if(!$pgReady)
            <div class="small text-danger mt-1">Enable <code>pdo_pgsql</code> in php.ini</div>
            @endif
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-4">
            <i class="bi bi-phone-fill fs-1 mb-2 text-primary"></i>
            <div class="fw-bold fs-3">{{ number_format($total) }}</div>
            <div class="text-muted small">Devices in Local DB</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-4">
            <i class="bi bi-clock-history fs-1 mb-2 text-info"></i>
            <div class="fw-bold">{{ $lastLog ? $lastLog->created_at->diffForHumans() : 'Never' }}</div>
            <div class="text-muted small">Last Sync</div>
            @if($lastLog)
            <div class="small mt-1">
                <span class="badge bg-{{ $lastLog->status === 'completed' ? 'success' : 'danger' }}-subtle text-{{ $lastLog->status === 'completed' ? 'success' : 'danger' }} border">
                    {{ ucfirst($lastLog->status) }}
                </span>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Live Progress Panel ────────────────────────────────────────────────── --}}
<div id="progressPanel" class="card border-0 shadow-sm mb-4 d-none">
    <div class="card-header d-flex align-items-center justify-content-between py-2" id="progressHeader">
        <div class="d-flex align-items-center gap-2">
            <span id="progressSpinnerWrap">
                <span class="spinner-border spinner-border-sm text-primary" style="width:16px;height:16px"></span>
            </span>
            <strong id="progressTitle">Sync in Progress</strong>
        </div>
        <div class="d-flex align-items-center gap-3 small">
            <span class="text-muted" id="elapsedTime"></span>
            <span class="fw-semibold" id="etaDisplay"></span>
        </div>
    </div>
    <div class="card-body pb-1">
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-semibold">Overall Progress</span>
                <span class="small text-muted" id="overallPct">—</span>
            </div>
            <div class="progress" style="height:10px">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                     id="overallBar" style="width:0%" role="progressbar"></div>
            </div>
        </div>
        <div id="stagesContainer" class="border-top pt-1"></div>
    </div>
    <div class="card-footer bg-white d-none py-2" id="progressResult"></div>
</div>

{{-- ── Actions ────────────────────────────────────────────────────────────── --}}
<div class="row g-4 mb-4">

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0"
                         style="width:48px;height:48px;background:#6f42c1;font-size:1.4rem">
                        <i class="bi bi-database-fill-up"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Sync from PostgreSQL</h5>
                        <p class="text-muted small mb-0">
                            Connects to <strong>{{ $pgHost }}/{{ $pgDb }}</strong> and syncs
                            device list, GPS, hardware params, and apps to local tables.
                            Runs in background — live progress shown above.
                        </p>
                    </div>
                </div>
                <div class="mt-auto">
                    @if($lastLog)
                    <div class="text-muted small mb-2">
                        Last run {{ $lastLog->created_at->format('d M Y H:i') }} —
                        {{ $lastLog->total_rows }} devices, {{ $lastLog->imported }} new, {{ $lastLog->updated }} updated
                    </div>
                    @endif
                    <form id="syncForm" method="POST" action="{{ route('mdm.sync.run') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100" id="syncBtn" {{ !$pgReady ? 'disabled' : '' }}>
                            <i class="bi bi-database-fill-up me-2"></i>
                            {{ $pgReady ? 'Sync from PostgreSQL Now' : 'pdo_pgsql not available' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0"
                         style="width:48px;height:48px;background:var(--gs-teal);font-size:1.4rem">
                        <i class="bi bi-link-45deg"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Auto-Match Devices</h5>
                        <p class="text-muted small mb-0">
                            Automatically matches MDM devices to internal asset records
                            by IMEI and Serial Number. Also links the assigned employee
                            from the matched asset if the device is currently unlinked.
                        </p>
                    </div>
                </div>
                <div class="mt-auto">
                    <div class="text-muted small mb-2">
                        {{ $synced }} of {{ $total }} devices synced from PostgreSQL
                    </div>
                    <form method="POST" action="{{ route('mdm.sync.automatch') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-link-45deg me-2"></i>Run Auto-Match
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Sync History ───────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
        <strong><i class="bi bi-clock-history me-2"></i>Sync History</strong>
        <span class="text-muted small">{{ $logs->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 small align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date / Time</th>
                    <th>Source</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">New</th>
                    <th class="text-center">Updated</th>
                    <th class="text-center">Matched</th>
                    <th class="text-center">Skipped</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="text-nowrap">
                        <div>{{ $log->created_at->format('d M Y') }}</div>
                        <div class="text-muted">{{ $log->created_at->format('H:i:s') }}</div>
                    </td>
                    <td class="font-monospace small text-muted text-truncate" style="max-width:160px" title="{{ $log->filename }}">
                        {{ Str::limit($log->filename, 25) }}
                    </td>
                    <td class="text-center fw-bold">{{ number_format($log->total_rows) }}</td>
                    <td class="text-center text-success">{{ $log->imported }}</td>
                    <td class="text-center text-info">{{ $log->updated }}</td>
                    <td class="text-center text-primary">{{ $log->auto_matched }}</td>
                    <td class="text-center text-warning">{{ $log->skipped }}</td>
                    <td>
                        <span class="badge bg-{{ $log->status === 'completed' ? 'success' : 'danger' }}-subtle
                                           text-{{ $log->status === 'completed' ? 'success' : 'danger' }} border">
                            {{ ucfirst($log->status) }}
                        </span>
                    </td>
                    <td class="text-muted small text-truncate" style="max-width:200px" title="{{ $log->notes }}">
                        {{ Str::limit($log->notes, 40) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="bi bi-clock-history fs-3 d-block mb-2 opacity-30"></i>
                        No sync history yet
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white">
        {{ $logs->links() }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const PROGRESS_URL = '{{ route("mdm.sync.progress") }}';
    const SYNC_URL     = '{{ route("mdm.sync.run") }}';
    const CSRF         = '{{ csrf_token() }}';

    let pollTimer    = null;
    let elapsedTimer = null;
    let panelShown   = false;
    let syncStart    = null; // ms timestamp

    // ── Page load: resume if sync is running / show recent result ─────────────
    document.addEventListener('DOMContentLoaded', function () {
        fetchProgress().then(function (data) {
            if (!data) return;
            const age = data.updated_at ? (Date.now() / 1000 - data.updated_at) : 9999;
            if (data.status === 'running' || data.status === 'starting') {
                syncStart = data.started_at ? data.started_at * 1000 : Date.now();
                showPanel();
                renderProgress(data);
                startPoll();
            } else if ((data.status === 'completed' || data.status === 'failed') && age < 300) {
                syncStart = data.started_at ? data.started_at * 1000 : null;
                showPanel();
                renderProgress(data);
            }
        });
    });

    // ── Sync Now form — submit via AJAX ───────────────────────────────────────
    document.getElementById('syncForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('syncBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Starting…';
        syncStart = Date.now();
        showPanel();

        try {
            const r = await fetch(SYNC_URL, {
                method : 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            });
            const d = await r.json();
            if (d.error) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-database-fill-up me-2"></i>Sync from PostgreSQL Now';
                alert(d.error);
            } else {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Running in background…';
                startPoll();
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-database-fill-up me-2"></i>Sync from PostgreSQL Now';
            alert('Could not start sync: ' + err.message);
        }
    });

    // ── Polling ────────────────────────────────────────────────────────────────
    function startPoll() {
        if (pollTimer) return;
        pollTimer = setInterval(function () {
            fetchProgress().then(function (data) {
                if (!data) return;
                renderProgress(data);
                if (data.status === 'completed' || data.status === 'failed') {
                    clearInterval(pollTimer); pollTimer = null;
                    clearInterval(elapsedTimer); elapsedTimer = null;
                    const btn = document.getElementById('syncBtn');
                    if (data.status === 'completed') {
                        document.getElementById('progressTitle').textContent = 'Sync Completed — reloading…';
                        if (btn) btn.innerHTML = '<i class="bi bi-check-circle-fill me-2 text-success"></i>Completed';
                        setTimeout(function () { window.location.reload(); }, 3000);
                    } else {
                        document.getElementById('progressTitle').textContent = 'Sync Failed';
                        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-database-fill-up me-2"></i>Retry Sync'; }
                    }
                }
            });
        }, 2000);
    }

    async function fetchProgress() {
        try {
            const r = await fetch(PROGRESS_URL);
            return await r.json();
        } catch (e) { return null; }
    }

    // ── Show progress panel + start elapsed ticker ────────────────────────────
    function showPanel() {
        if (panelShown) return;
        panelShown = true;
        document.getElementById('progressPanel').classList.remove('d-none');
        // scroll into view smoothly
        document.getElementById('progressPanel').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        elapsedTimer = setInterval(tickElapsed, 1000);
    }

    function tickElapsed() {
        if (!syncStart) return;
        const sec = Math.floor((Date.now() - syncStart) / 1000);
        document.getElementById('elapsedTime').textContent = 'Elapsed: ' + fmt(sec);
    }

    // ── Render progress state ──────────────────────────────────────────────────
    function renderProgress(data) {
        const stages = Array.isArray(data.stages) ? data.stages : [];
        const status = data.status || 'idle';

        // Header icon
        const sw = document.getElementById('progressSpinnerWrap');
        if (status === 'completed') {
            sw.innerHTML = '<i class="bi bi-check-circle-fill text-success" style="font-size:1.1rem"></i>';
            document.getElementById('progressHeader').classList.add('bg-success-subtle');
        } else if (status === 'failed') {
            sw.innerHTML = '<i class="bi bi-x-circle-fill text-danger" style="font-size:1.1rem"></i>';
            document.getElementById('progressHeader').classList.add('bg-danger-subtle');
        }

        // Stage rows
        let html = '';
        if (!stages.length) {
            html = '<div class="text-muted small text-center py-2">Connecting to PostgreSQL…</div>';
        } else {
            stages.forEach(function (s) {
                const pct = s.total > 0 ? Math.min(100, Math.round(s.done / s.total * 100))
                                        : (s.status === 'done' || s.status === 'skipped' ? 100 : 0);
                const barCls = {
                    done:    'bg-success',
                    running: 'bg-primary progress-bar-striped progress-bar-animated',
                    failed:  'bg-danger',
                    skipped: 'bg-secondary',
                    pending: '',
                }[s.status] || '';

                const countText = (s.done > 0 || s.status === 'running')
                    ? (s.total ? fmtN(s.done) + ' / ' + fmtN(s.total) : fmtN(s.done) + ' rows')
                    : (s.status === 'done' ? 'Done' : '');

                let etaText = '';
                if (s.status === 'done')         etaText = '<span class="text-success">Done</span>';
                else if (s.status === 'failed')   etaText = '<span class="text-danger">Error</span>';
                else if (s.status === 'skipped')  etaText = '<span class="text-muted">—</span>';
                else if (s.status === 'running' && s.eta_sec > 0) etaText = '<span class="text-primary fw-semibold">~' + fmt(s.eta_sec) + '</span>';
                else if (s.status === 'running' && s.rate > 0)    etaText = '<span class="text-muted">' + s.rate + '/s</span>';

                html += '<div class="d-flex align-items-center gap-2 py-1 border-bottom">'
                    + '<div style="width:20px;text-align:center;flex-shrink:0">' + icon(s.status) + '</div>'
                    + '<div class="flex-grow-1">'
                    +   '<div class="d-flex justify-content-between align-items-center mb-1">'
                    +     '<span class="' + (s.status === 'pending' ? 'text-muted' : 'fw-semibold') + '" style="font-size:.78rem">' + esc(s.label) + '</span>'
                    +     '<span class="text-muted small ms-2" style="font-size:.74rem;white-space:nowrap">' + countText + '</span>'
                    +   '</div>'
                    +   '<div class="progress" style="height:4px">'
                    +     '<div class="progress-bar ' + barCls + '" style="width:' + pct + '%" role="progressbar"></div>'
                    +   '</div>'
                    + '</div>'
                    + '<div class="text-end" style="width:54px;flex-shrink:0;font-size:.75rem">' + etaText + '</div>'
                    + '</div>';
            });
        }
        document.getElementById('stagesContainer').innerHTML = html;

        // Overall bar
        const overall = overallPct(stages, status);
        const bar = document.getElementById('overallBar');
        bar.style.width = overall + '%';
        if (status === 'completed') bar.classList.remove('progress-bar-animated', 'progress-bar-striped');
        document.getElementById('overallPct').textContent = overall + '%';

        // ETA label
        if (syncStart && overall > 5 && overall < 100) {
            const elapsed = (Date.now() - syncStart) / 1000;
            const eta = Math.round(elapsed * (100 - overall) / overall);
            document.getElementById('etaDisplay').textContent = 'ETA: ~' + fmt(eta);
        } else if (status === 'completed' || status === 'failed') {
            document.getElementById('etaDisplay').textContent = '';
        }

        // Result footer
        const footer = document.getElementById('progressResult');
        if (status === 'completed' && data.result) {
            const res = data.result;
            footer.classList.remove('d-none');
            footer.innerHTML = '<div class="d-flex align-items-center gap-2 small text-success flex-wrap">'
                + '<i class="bi bi-check-circle-fill"></i><strong>Sync complete —</strong>'
                + fmtN(res.total_rows) + ' total &nbsp;·&nbsp;'
                + res.imported + ' new &nbsp;·&nbsp;'
                + res.updated  + ' updated &nbsp;·&nbsp;'
                + res.skipped  + ' skipped &nbsp;·&nbsp;'
                + res.auto_matched + ' matched'
                + '<span class="ms-2 text-muted fst-italic">Reloading…</span></div>';
        } else if (status === 'failed' && data.error) {
            footer.classList.remove('d-none');
            footer.innerHTML = '<div class="d-flex align-items-center gap-2 small text-danger">'
                + '<i class="bi bi-x-circle-fill"></i><strong>Sync failed:</strong> ' + esc(data.error) + '</div>';
        }
    }

    // ── Pure helpers ──────────────────────────────────────────────────────────
    function overallPct(stages, status) {
        if (status === 'completed') return 100;
        if (!stages.length) return 0;
        var done = 0;
        stages.forEach(function (s) {
            if (s.status === 'done' || s.status === 'skipped') done += 1;
            else if (s.status === 'running' && s.total > 0) done += s.done / s.total;
        });
        return Math.min(99, Math.round((done / stages.length) * 100));
    }

    function icon(status) {
        if (status === 'done')    return '<i class="bi bi-check-circle-fill text-success" style="font-size:.82rem"></i>';
        if (status === 'failed')  return '<i class="bi bi-x-circle-fill text-danger" style="font-size:.82rem"></i>';
        if (status === 'skipped') return '<i class="bi bi-dash-circle text-muted" style="font-size:.82rem"></i>';
        if (status === 'running') return '<span class="spinner-border text-primary" style="width:13px;height:13px;border-width:2px"></span>';
        return '<i class="bi bi-clock text-muted opacity-40" style="font-size:.82rem"></i>';
    }

    function fmt(sec) {
        sec = Math.max(0, Math.round(sec));
        if (sec < 60) return sec + 's';
        var m = Math.floor(sec / 60), s = sec % 60;
        return s > 0 ? m + 'm ' + s + 's' : m + 'm';
    }

    function fmtN(v)  { return Number(v).toLocaleString(); }
    function esc(s)   { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
})();
</script>
@endpush
