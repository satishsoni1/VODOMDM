@extends('layouts.main')
@section('title', 'Device Map')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mdm.index') }}">MDM</a></li>
    <li class="breadcrumb-item active">Device Map</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css"/>
<style>
    /* Full-height layout */
    .map-page-wrap {
        display: flex;
        gap: 0;
        height: calc(100vh - 56px - 3rem); /* viewport minus topbar minus content padding */
        min-height: 500px;
    }

    /* ── Left sidebar ── */
    .map-sidebar {
        width: 320px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 12px 0 0 12px;
        border: 1px solid #d4eeeb;
        border-right: none;
        overflow: hidden;
    }

    .map-sidebar-header {
        padding: 1rem 1.1rem .75rem;
        background: var(--gs-teal-light);
        border-bottom: 1px solid #b2d8d4;
        flex-shrink: 0;
    }
    .map-sidebar-header h6 {
        font-weight: 700;
        color: var(--gs-teal-dark);
        margin: 0 0 .7rem;
        font-size: .9rem;
    }

    /* filter inputs */
    .map-filter { font-size: .82rem; border: 1.5px solid #d4eeeb; border-radius: 7px; padding: .35rem .6rem; color: #1a2e2c; background: #fff; width: 100%; margin-bottom: .5rem; outline: none; }
    .map-filter:focus { border-color: var(--gs-teal); box-shadow: 0 0 0 3px rgba(26,138,124,.1); }
    .map-filter-btn { font-size: .82rem; padding: .35rem .75rem; border-radius: 7px; border: none; background: var(--gs-teal); color: #fff; font-weight: 600; cursor: pointer; }
    .map-filter-btn:hover { background: var(--gs-teal-dark); }
    .map-clear-btn  { font-size: .82rem; padding: .35rem .65rem; border-radius: 7px; border: 1.5px solid #d4eeeb; background: #fff; color: #6c757d; cursor: pointer; }
    .map-clear-btn:hover { background: #f0f7f6; }

    /* KPI strip */
    .map-kpi-strip {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        border-bottom: 1px solid #e8f5f3;
        flex-shrink: 0;
    }
    .map-kpi {
        text-align: center;
        padding: .6rem .4rem;
        border-right: 1px solid #e8f5f3;
    }
    .map-kpi:last-child { border-right: none; }
    .map-kpi-val { font-size: 1.3rem; font-weight: 800; color: var(--gs-teal-dark); }
    .map-kpi-lbl { font-size: .68rem; color: #6c757d; }

    /* device list */
    .map-device-list {
        flex: 1;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #b2d8d4 #f0f7f6;
    }
    .map-device-list::-webkit-scrollbar { width: 4px; }
    .map-device-list::-webkit-scrollbar-thumb { background: #b2d8d4; border-radius: 4px; }

    .device-item {
        padding: .65rem 1rem;
        border-bottom: 1px solid #f0f7f6;
        cursor: pointer;
        transition: background .15s;
        display: flex;
        align-items: flex-start;
        gap: .65rem;
    }
    .device-item:hover, .device-item.active {
        background: var(--gs-teal-light);
    }
    .device-dot {
        width: 9px; height: 9px; border-radius: 50%;
        margin-top: 5px; flex-shrink: 0;
    }
    .dot-online  { background: #1a8a7c; }
    .dot-offline { background: #f07030; }
    .dot-unknown { background: #adb5bd; }

    .device-item-name { font-size: .84rem; font-weight: 600; color: #1a2e2c; }
    .device-item-sub  { font-size: .72rem; color: #6c757d; }

    .no-location-badge {
        font-size: .65rem;
        background: #fff3eb;
        color: #f07030;
        border: 1px solid #f07030;
        border-radius: 4px;
        padding: 0 .35rem;
        display: inline-block;
        margin-top: 2px;
    }

    /* ── Map panel ── */
    .map-panel {
        flex: 1;
        border-radius: 0 12px 12px 0;
        overflow: hidden;
        border: 1px solid #d4eeeb;
        position: relative;
    }
    #deviceMap { height: 100%; width: 100%; }

    /* Map legend */
    .map-legend {
        position: absolute;
        bottom: 24px; right: 10px;
        background: rgba(255,255,255,.95);
        border-radius: 10px;
        padding: .6rem .9rem;
        font-size: .78rem;
        box-shadow: 0 2px 12px rgba(0,0,0,.15);
        z-index: 999;
        border: 1px solid #d4eeeb;
    }
    .legend-row { display: flex; align-items: center; gap: .5rem; margin-bottom: .3rem; }
    .legend-row:last-child { margin-bottom: 0; }
    .legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

    /* Popup styling */
    .lf-popup-title { font-weight: 700; color: var(--gs-teal-dark); font-size: .88rem; margin-bottom: .4rem; }
    .lf-popup-row   { font-size: .78rem; color: #444; margin-bottom: .2rem; }
    .lf-popup-row strong { color: #1a2e2c; }
    .lf-badge-on  { display:inline-block;padding:.1rem .4rem;border-radius:4px;background:#d4edda;color:#155724;font-size:.72rem;font-weight:600; }
    .lf-badge-off { display:inline-block;padding:.1rem .4rem;border-radius:4px;background:#f8d7da;color:#721c24;font-size:.72rem;font-weight:600; }

    /* No data overlay */
    .map-no-location-notice {
        position: absolute;
        top: 12px; left: 50%; transform: translateX(-50%);
        background: rgba(255,243,235,.95);
        border: 1px solid #f07030;
        border-radius: 8px;
        padding: .45rem 1rem;
        font-size: .8rem;
        color: #7a3a10;
        z-index: 999;
        white-space: nowrap;
        pointer-events: none;
    }

    @media (max-width: 900px) {
        .map-page-wrap { flex-direction: column; height: auto; }
        .map-sidebar   { width: 100%; border-radius: 12px 12px 0 0; border-right: 1px solid #d4eeeb; border-bottom: none; max-height: 320px; }
        .map-panel     { height: 400px; border-radius: 0 0 12px 12px; }
    }
</style>
@endpush

@section('content')

{{-- ── Filter bar (above main wrap) ─────────────────────────────────────────── --}}
<form method="GET" id="filterForm" class="row g-2 align-items-end mb-3">
    <div class="col-auto">
        <select name="state" class="form-select form-select-sm" id="stateSelect" onchange="this.form.submit()">
            <option value="">All States</option>
            @foreach($states as $s)
            <option value="{{ $s }}" @selected(request('state') === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="city" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All Cities</option>
            @foreach($cities as $c)
            <option value="{{ $c }}" @selected(request('city') === $c)>{{ $c }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="on"  @selected(request('status')==='on')>Online</option>
            <option value="off" @selected(request('status')==='off')>Offline</option>
        </select>
    </div>
    <div class="col-auto">
        <select name="group" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All Groups</option>
            @foreach($groups as $g)
            <option value="{{ $g }}" @selected(request('group') === $g)>{{ $g }}</option>
            @endforeach
        </select>
    </div>
    @if(request()->hasAny(['state','city','status','group']))
    <div class="col-auto">
        <a href="{{ route('mdm.map') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i>Clear
        </a>
    </div>
    @endif

    <div class="col-auto ms-auto">
        <div class="d-flex gap-2 small align-items-center">
            <span class="badge rounded-pill" style="background:var(--gs-teal-light);color:var(--gs-teal-dark);padding:.4rem .8rem">
                <i class="bi bi-phone me-1"></i>{{ $total }} devices
            </span>
            <span class="badge rounded-pill bg-success-subtle text-success border" style="padding:.4rem .8rem">
                <i class="bi bi-wifi me-1"></i>{{ $online }} online
            </span>
            <span class="badge rounded-pill bg-warning-subtle text-warning border" style="padding:.4rem .8rem">
                <i class="bi bi-geo-alt me-1"></i>{{ $located }} GPS precise
            </span>
        </div>
    </div>
</form>

{{-- ── Main split ───────────────────────────────────────────────────────────── --}}
<div class="map-page-wrap">

    {{-- ── Sidebar: device list ── --}}
    <div class="map-sidebar">
        <div class="map-kpi-strip">
            <div class="map-kpi">
                <div class="map-kpi-val">{{ $total }}</div>
                <div class="map-kpi-lbl">Total</div>
            </div>
            <div class="map-kpi">
                <div class="map-kpi-val text-success">{{ $online }}</div>
                <div class="map-kpi-lbl">Online</div>
            </div>
            <div class="map-kpi">
                <div class="map-kpi-val text-danger">{{ $total - $online }}</div>
                <div class="map-kpi-lbl">Offline</div>
            </div>
        </div>

        <div class="map-device-list" id="deviceList">
            @forelse($mapData as $d)
            <div class="device-item" id="list-{{ $d['id'] }}"
                 onclick="focusDevice({{ $d['id'] }})">
                <div class="device-dot {{ $d['online'] ? 'dot-online' : 'dot-offline' }}"></div>
                <div style="min-width:0">
                    <div class="device-item-name">{{ $d['mdm_number'] }} — {{ $d['model'] ?? '—' }}</div>
                    <div class="device-item-sub">
                        {{ $d['employee']['name'] ?? '—' }}
                        @if($d['employee']['city'] ?? null)
                            · {{ $d['employee']['city'] }}
                        @endif
                    </div>
                    @if(!$d['lat'])
                    <span class="no-location-badge"><i class="bi bi-geo-alt-fill me-1"></i>city approx.</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted small">
                <i class="bi bi-phone-x d-block fs-1 opacity-25 mb-2"></i>No devices match filters
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── Map ── --}}
    <div class="map-panel">
        <div id="deviceMap"></div>

        {{-- Legend --}}
        <div class="map-legend">
            <div class="legend-row"><div class="legend-dot" style="background:#1a8a7c"></div> Online</div>
            <div class="legend-row"><div class="legend-dot" style="background:#f07030"></div> Offline</div>
            <div class="legend-row"><div class="legend-dot" style="background:#adb5bd"></div> Unknown</div>
            <hr class="my-1" style="border-color:#dee2e6">
            <div class="legend-row" style="color:#6c757d"><i class="bi bi-geo-alt-fill me-1" style="color:#f07030;font-size:.8rem"></i> City approx.</div>
            <div class="legend-row" style="color:#6c757d"><i class="bi bi-geo-alt-fill me-1" style="color:#1a8a7c;font-size:.8rem"></i> GPS precise</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
// ── City → approximate coordinates (India) ────────────────────────────────────
const CITY_COORDS = {
    'mumbai':       [19.0760,  72.8777],
    'pune':         [18.5204,  73.8567],
    'nashik':       [20.0059,  73.7910],
    'nagpur':       [21.1458,  79.0882],
    'aurangabad':   [19.8762,  75.3433],
    'delhi':        [28.6139,  77.2090],
    'new delhi':    [28.6139,  77.2090],
    'gurgaon':      [28.4595,  77.0266],
    'noida':        [28.5355,  77.3910],
    'bangalore':    [12.9716,  77.5946],
    'bengaluru':    [12.9716,  77.5946],
    'chennai':      [13.0827,  80.2707],
    'hyderabad':    [17.3850,  78.4867],
    'kolkata':      [22.5726,  88.3639],
    'ahmedabad':    [23.0225,  72.5714],
    'surat':        [21.1702,  72.8311],
    'jaipur':       [26.9124,  75.7873],
    'lucknow':      [26.8467,  80.9462],
    'bhopal':       [23.2599,  77.4126],
    'indore':       [22.7196,  75.8577],
    'coimbatore':   [11.0168,  76.9558],
    'kochi':        [ 9.9312,  76.2673],
    'chandigarh':   [30.7333,  76.7794],
    'patna':        [25.5941,  85.1376],
    'bhubaneswar':  [20.2961,  85.8245],
    'visakhapatnam':[17.6868,  83.2185],
    'vadodara':     [22.3072,  73.1812],
    'rajkot':       [22.3039,  70.8022],
    'thane':        [19.2183,  72.9781],
    'navi mumbai':  [19.0330,  73.0297],
};

function resolveCoords(device) {
    if (device.lat && device.lng) return { coords: [device.lat, device.lng], precise: true };
    const city = (device.employee?.city || '').toLowerCase().trim();
    if (city && CITY_COORDS[city]) {
        // jitter slightly so stacked markers don't overlap perfectly
        const jitter = () => (Math.random() - 0.5) * 0.06;
        return { coords: [CITY_COORDS[city][0] + jitter(), CITY_COORDS[city][1] + jitter()], precise: false };
    }
    return null;
}

// ── Raw data from server ──────────────────────────────────────────────────────
const DEVICES = @json($mapData);

// ── Init map ──────────────────────────────────────────────────────────────────
const map = L.map('deviceMap', { zoomControl: false }).setView([20.5937, 78.9629], 5);

L.control.zoom({ position: 'bottomleft' }).addTo(map);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
    maxZoom: 19,
}).addTo(map);

// ── Custom markers ────────────────────────────────────────────────────────────
function makeIcon(color, precise) {
    const outer = precise ? '#fff' : '#fff3eb';
    return L.divIcon({
        className: '',
        html: `<div style="
            width:28px;height:28px;border-radius:50%;
            background:${color};border:3px solid ${outer};
            box-shadow:0 2px 8px rgba(0,0,0,.3);
            display:flex;align-items:center;justify-content:center;">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="white">
              <circle cx="12" cy="12" r="5"/>
            </svg>
        </div>`,
        iconSize:   [28, 28],
        iconAnchor: [14, 14],
        popupAnchor:[0, -16],
    });
}

const COLOR_ON      = '#1a8a7c';
const COLOR_OFF     = '#f07030';
const COLOR_UNKNOWN = '#adb5bd';

// ── Marker cluster ────────────────────────────────────────────────────────────
const cluster = L.markerClusterGroup({
    maxClusterRadius: 50,
    iconCreateFunction(c) {
        const count = c.getChildCount();
        return L.divIcon({
            html: `<div style="
                width:38px;height:38px;border-radius:50%;
                background:var(--gs-teal,#1a8a7c);color:#fff;
                display:flex;align-items:center;justify-content:center;
                font-weight:700;font-size:.85rem;
                border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.25);">
                ${count}
            </div>`,
            className: '',
            iconSize: [38, 38],
            iconAnchor: [19, 19],
        });
    },
});

// ── Build markers map (id → marker) ──────────────────────────────────────────
const markerById = {};
let placedCount  = 0;
let skippedCount = 0;

DEVICES.forEach(d => {
    const resolved = resolveCoords(d);
    if (!resolved) { skippedCount++; return; }

    const color = d.status === 'on'  ? COLOR_ON
                : d.status === 'off' ? COLOR_OFF
                : COLOR_UNKNOWN;

    const marker = L.marker(resolved.coords, {
        icon: makeIcon(color, resolved.precise),
        title: `${d.mdm_number} — ${d.model || ''}`,
    });

    const empRows = d.employee ? `
        <div class="lf-popup-row"><strong>Employee:</strong> ${d.employee.name} (${d.employee.desig})</div>
        <div class="lf-popup-row"><strong>Location:</strong> ${d.employee.city || '—'}, ${d.employee.state || '—'}</div>
        ${d.employee.phone ? `<div class="lf-popup-row"><strong>Phone:</strong> ${d.employee.phone}</div>` : ''}
    ` : '';

    const locNote = resolved.precise
        ? `<div class="lf-popup-row" style="color:#1a8a7c;font-size:.72rem"><i>GPS precise</i></div>`
        : `<div class="lf-popup-row" style="color:#f07030;font-size:.72rem"><i>City approximation</i></div>`;

    marker.bindPopup(`
        <div style="min-width:200px;font-family:'Segoe UI',system-ui,sans-serif">
            <div class="lf-popup-title">
                ${d.mdm_number} &nbsp;
                <span class="${d.online ? 'lf-badge-on' : 'lf-badge-off'}">
                    ${d.online ? 'Online' : 'Offline'}
                </span>
            </div>
            <div class="lf-popup-row"><strong>Model:</strong> ${d.model || '—'}</div>
            <div class="lf-popup-row"><strong>Serial:</strong> ${d.serial || '—'}</div>
            <div class="lf-popup-row"><strong>Group:</strong> ${d.group || '—'}</div>
            <div class="lf-popup-row"><strong>IP:</strong> ${d.ip || '—'}</div>
            <div class="lf-popup-row"><strong>Last sync:</strong> ${d.sync_age}</div>
            ${empRows}
            ${locNote}
            <div style="margin-top:.5rem">
                <a href="/mdm/${d.id}" target="_blank" style="font-size:.78rem;color:#1a8a7c">
                    View full detail →
                </a>
            </div>
        </div>
    `, { maxWidth: 260 });

    marker.on('click', () => highlightListItem(d.id));
    markerById[d.id] = { marker, resolved };
    cluster.addLayer(marker);
    placedCount++;
});

map.addLayer(cluster);

// Show notice if some devices couldn't be placed
if (skippedCount > 0) {
    const notice = document.createElement('div');
    notice.className = 'map-no-location-notice';
    notice.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i>${skippedCount} device(s) have no city or GPS data and aren't shown on the map`;
    document.querySelector('.map-panel').appendChild(notice);
    setTimeout(() => notice.style.opacity = '0', 6000);
    notice.style.transition = 'opacity 1s';
}

// ── Fit map to all markers ────────────────────────────────────────────────────
if (placedCount > 0) {
    try { map.fitBounds(cluster.getBounds().pad(0.1)); } catch(e) {}
}

// ── Sidebar click → fly to marker ────────────────────────────────────────────
function focusDevice(id) {
    const entry = markerById[id];
    if (!entry) return;
    const { marker, resolved } = entry;
    map.flyTo(resolved.coords, 13, { duration: 0.8 });
    setTimeout(() => {
        cluster.zoomToShowLayer(marker, () => marker.openPopup());
    }, 900);
    highlightListItem(id);
}

function highlightListItem(id) {
    document.querySelectorAll('.device-item').forEach(el => el.classList.remove('active'));
    const el = document.getElementById('list-' + id);
    if (el) {
        el.classList.add('active');
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// ── Click on map clears sidebar highlight ─────────────────────────────────────
map.on('click', () => {
    document.querySelectorAll('.device-item').forEach(el => el.classList.remove('active'));
});
</script>
@endpush
