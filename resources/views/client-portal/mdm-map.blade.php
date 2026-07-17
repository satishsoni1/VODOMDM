@extends('client-portal.layout')
@section('title','MDM Device Map')
@section('page-title','MDM Device Map')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<style>
/* ── Layout ─────────────────────────────────────────────────────────────────── */
.map-page { display:flex; flex-direction:column; gap:.6rem; }

/* ── Filter bar ─────────────────────────────────────────────────────────────── */
.map-filter-bar {
    background:#fff; border-radius:8px; padding:.5rem 1rem;
    box-shadow:0 1px 6px rgba(0,0,0,.08);
    display:flex; align-items:center; gap:.5rem; flex-wrap:wrap;
}
.filter-search-wrap { position:relative; }
.filter-search-wrap .bi { position:absolute; left:.55rem; top:50%; transform:translateY(-50%); color:#aaa; font-size:.78rem; pointer-events:none; }
.filter-search-wrap input { padding-left:1.7rem; }
.s-dot { width:8px; height:8px; border-radius:50%; display:inline-block; flex-shrink:0; }

/* ── Map outer ──────────────────────────────────────────────────────────────── */
#mapOuter {
    position:relative;
    height:calc(100vh - 240px); min-height:520px;
    border-radius:10px; overflow:hidden;
    box-shadow:0 2px 18px rgba(0,0,0,.12);
}
#mapEl { height:100%; width:100%; }

#mapOuter:fullscreen,
#mapOuter:-webkit-full-screen,
#mapOuter:-ms-fullscreen {
    height:100vh !important; min-height:0; border-radius:0;
}

/* ── Fullscreen toggle ──────────────────────────────────────────────────────── */
#fullscreenBtn {
    position:absolute; bottom:10px; left:10px; z-index:900;
    background:#fff; border:none; border-radius:8px;
    padding:.42rem .58rem; box-shadow:0 2px 8px rgba(0,0,0,.14);
    cursor:pointer; color:#444; font-size:1rem; line-height:1;
}
#fullscreenBtn:hover { background:#f5f5f5; }

/* ── Cluster icons ───────────────────────────────────────────────────────────── */
.mc { border-radius:50%; display:flex; align-items:center; justify-content:center;
      font-weight:700; color:#fff; border:3px solid rgba(255,255,255,.85);
      box-shadow:0 2px 10px rgba(0,0,0,.28); font-family:system-ui,sans-serif; }
.mc-s  { width:38px;  height:38px;  font-size:12px; }
.mc-m  { width:48px;  height:48px;  font-size:14px; }
.mc-l  { width:58px;  height:58px;  font-size:16px; }
.mc-xl { width:70px;  height:70px;  font-size:18px; }

/* ── Individual device pin ──────────────────────────────────────────────────── */
.dev-pin {
    width:14px; height:14px;
    border-radius:50% 50% 50% 0; transform:rotate(-45deg);
    border:2px solid rgba(255,255,255,.85);
    box-shadow:0 1px 5px rgba(0,0,0,.35);
}

/* ── Side panel ─────────────────────────────────────────────────────────────── */
#sidePanel {
    position:absolute; top:10px; left:10px; z-index:800;
    width:240px; max-height:calc(100% - 66px);
    display:flex; flex-direction:column;
    background:rgba(255,255,255,.97); border-radius:10px;
    box-shadow:0 3px 18px rgba(0,0,0,.15);
    transition:transform .22s ease;
}
#sidePanel.hidden { transform:translateX(-264px); }
#showPanelBtn {
    position:absolute; top:10px; left:10px; z-index:900; display:none;
    background:#fff; border:none; border-radius:8px;
    padding:.42rem .58rem; box-shadow:0 2px 8px rgba(0,0,0,.14);
    cursor:pointer; color:#444; font-size:1rem; line-height:1;
}
#showPanelBtn.vis { display:block; }
.panel-head { padding:.55rem .75rem; border-bottom:1px solid #eee; display:flex; align-items:center; gap:.5rem; }
.panel-body { flex:1; overflow-y:auto; scrollbar-width:thin; scrollbar-color:#ccc transparent; }
.dev-row { padding:.38rem .75rem; border-bottom:1px solid #f2f4f7; cursor:pointer; font-size:.76rem; transition:background .1s; }
.dev-row:hover  { background:#f0f5ff; }
.dev-row.active { background:#dbe9ff; }

/* ── Stats overlay ──────────────────────────────────────────────────────────── */
#statsBox {
    position:absolute; top:10px; right:10px; z-index:800;
    background:rgba(255,255,255,.96); backdrop-filter:blur(6px);
    border-radius:10px; padding:.65rem 1rem;
    box-shadow:0 2px 14px rgba(0,0,0,.12);
    font-size:.78rem; min-width:145px;
}

/* ── Legend ─────────────────────────────────────────────────────────────────── */
#mapLegend {
    position:absolute; bottom:26px; right:10px; z-index:800;
    background:rgba(255,255,255,.95); border-radius:8px;
    padding:.45rem .75rem; box-shadow:0 1px 8px rgba(0,0,0,.1);
    font-size:.72rem;
}

/* ── Popup ──────────────────────────────────────────────────────────────────── */
.mdm-pop { font-family:system-ui,sans-serif; min-width:215px; }
.mdm-pop .p-num   { font-family:monospace; font-weight:700; font-size:.9rem; margin-bottom:.15rem; }
.mdm-pop .p-sub   { font-size:.74rem; color:#666; margin-bottom:.4rem; }
.mdm-pop .p-tags  { display:flex; flex-wrap:wrap; gap:.3rem; margin-bottom:.4rem; }
.mdm-pop .p-tag   { display:inline-flex; align-items:center; gap:.25rem; padding:2px 7px; border-radius:20px; font-size:.69rem; font-weight:600; }
.mdm-pop .p-cfg   { background:#eef2ff; color:#3730a3; padding:2px 7px; border-radius:4px; font-size:.7rem; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.mdm-pop .p-emp   { font-size:.76rem; margin:.35rem 0; }
.mdm-pop .p-row   { display:flex; align-items:center; gap:.4rem; font-size:.71rem; color:#888; margin:.18rem 0; }
.mdm-pop .p-row i { color:#bbb; }
.mdm-pop .p-acts  { display:flex; gap:.35rem; margin-top:.5rem; }
.mdm-pop .p-btn   { padding:3px 11px; border-radius:5px; font-size:.72rem; font-weight:500; text-decoration:none; }
.leaflet-popup-content { margin:10px 12px !important; }

/* ── Battery bar ────────────────────────────────────────────────────────────── */
.batt-bar { display:inline-block; width:28px; height:10px; border:1.5px solid #ccc; border-radius:2px; position:relative; overflow:hidden; vertical-align:middle; margin-right:2px; }
.batt-bar::after { content:''; position:absolute; top:1px; left:1px; bottom:1px; border-radius:1px; }
.batt-green::after  { background:#198754; }
.batt-yellow::after { background:#ffc107; }
.batt-red::after    { background:#dc3545; }
</style>
@endpush

@section('content')
<div class="map-page">

@if(empty($configs))
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-gear fs-1 d-block mb-2 opacity-25"></i>
        No MDM configuration has been assigned to your account yet.<br>
        Please contact your account manager.
    </div>
</div>
@else

{{-- ── Filter bar (all client-side) ────────────────────────────────────────── --}}
<div class="map-filter-bar">

    {{-- Search --}}
    <div class="filter-search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" id="fSearch" class="form-control form-control-sm"
               style="width:190px" placeholder="Device #, model, employee…">
    </div>

    {{-- Status --}}
    <select id="fStatus" class="form-select form-select-sm" style="width:130px">
        <option value="">All Statuses</option>
        <option value="on">Online Only</option>
        <option value="off">Offline Only</option>
    </select>

    {{-- Group --}}
    <select id="fGroup" class="form-select form-select-sm" style="width:155px">
        <option value="">All Groups</option>
        @foreach($groups as $g)
        <option value="{{ $g }}">{{ $g }}</option>
        @endforeach
    </select>

    {{-- Configuration --}}
    <select id="fConfig" class="form-select form-select-sm" style="width:170px">
        <option value="">All Configurations</option>
        @foreach($configs as $c)
        <option value="{{ $c }}">{{ $c }}</option>
        @endforeach
    </select>

    {{-- Linked --}}
    <select id="fLinked" class="form-select form-select-sm" style="width:135px">
        <option value="">All Devices</option>
        <option value="yes">Linked Only</option>
        <option value="no">Unlinked Only</option>
    </select>

    {{-- Clear --}}
    <button id="fClear" class="btn btn-outline-secondary btn-sm d-none">
        <i class="bi bi-x me-1"></i>Clear
    </button>

    {{-- Stats --}}
    <div class="d-flex align-items-center gap-3 ms-auto small">
        <span class="text-success fw-semibold">
            <span class="s-dot me-1" style="background:#198754;vertical-align:middle"></span>
            <span id="fOnline">{{ $online }}</span> online
        </span>
        <span class="text-danger fw-semibold">
            <span class="s-dot me-1" style="background:#dc3545;vertical-align:middle"></span>
            <span id="fOffline">{{ $total - $online }}</span> offline
        </span>
        <span class="text-muted">
            <i class="bi bi-geo-alt-fill me-1"></i>
            <span id="fTotal">{{ $total }}</span> shown
        </span>
        <a href="{{ route('client.mdm-devices') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-table me-1"></i>List
        </a>
    </div>
</div>

{{-- ── Map ─────────────────────────────────────────────────────────────────── --}}
<div id="mapOuter">
    <div id="mapEl"></div>

    {{-- Show panel button (visible when panel collapsed) --}}
    <button id="showPanelBtn" title="Show device list">
        <i class="bi bi-layout-sidebar-inset"></i>
    </button>

    {{-- Full screen toggle --}}
    <button id="fullscreenBtn" title="Full screen">
        <i class="bi bi-arrows-fullscreen"></i>
    </button>

    {{-- Device list sidebar --}}
    <div id="sidePanel">
        <div class="panel-head">
            <i class="bi bi-phone-fill text-primary" style="font-size:.82rem"></i>
            <span class="fw-semibold small flex-grow-1">
                <span id="listCount">{{ $total }}</span> devices
            </span>
            <button class="btn btn-sm btn-link p-0 text-muted" id="hidePanelBtn" title="Collapse">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>
        <div class="px-2 py-1 border-bottom">
            <input type="text" id="sideSearch" class="form-control form-control-sm"
                   placeholder="Filter list…">
        </div>
        <div class="panel-body" id="deviceList"></div>
    </div>

    {{-- Stats overlay --}}
    <div id="statsBox">
        <div class="fw-bold mb-2" style="font-size:.8rem">
            <i class="bi bi-bar-chart-fill me-1 text-primary"></i>Fleet
        </div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="s-dot" style="background:#198754"></span>
            <span class="text-muted flex-grow-1">Online</span>
            <strong class="text-success" id="oOnline">{{ $online }}</strong>
        </div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="s-dot" style="background:#dc3545"></span>
            <span class="text-muted flex-grow-1">Offline</span>
            <strong class="text-danger" id="oOffline">{{ $total - $online }}</strong>
        </div>
        <div class="d-flex align-items-center gap-2 pt-1 mt-1 border-top">
            <span class="s-dot" style="background:#6c757d"></span>
            <span class="text-muted flex-grow-1">Total</span>
            <strong id="oTotal">{{ $total }}</strong>
        </div>
    </div>

    {{-- Cluster legend --}}
    <div id="mapLegend">
        <div class="fw-semibold mb-1" style="color:#444">Cluster Health</div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="mc" style="background:#198754;width:13px;height:13px;font-size:0;border-width:2px"></span>
            <span>&gt;65% online</span>
        </div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="mc" style="background:#fd7e14;width:13px;height:13px;font-size:0;border-width:2px"></span>
            <span>35–65% online</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="mc" style="background:#dc3545;width:13px;height:13px;font-size:0;border-width:2px"></span>
            <span>&lt;35% online</span>
        </div>
    </div>
</div>

@endif
</div>{{-- .map-page --}}
@endsection

@if(!empty($configs))
<script id="mdm-map-data" type="application/json">{!! json_encode($mapData) !!}</script>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
(function () {
'use strict';

var DEVICES = JSON.parse(document.getElementById('mdm-map-data').textContent);

// Pre-compute searchable string for each device
DEVICES.forEach(function (d) {
    d.linked = !!d.employee;
    d.q = (
        (d.number   || '') + ' ' +
        (d.model    || '') + ' ' +
        (d.group    || '') + ' ' +
        (d.config   || '') + ' ' +
        (d.imei     || '') + ' ' +
        (d.serial   || '') + ' ' +
        (d.employee ? d.employee.name + ' ' + (d.employee.code || '') : '')
    ).toLowerCase();
});

// ── Map init ─────────────────────────────────────────────────────────────────
var map = L.map('mapEl', { zoomControl: false }).setView([22.5, 78.9], 5);
L.control.zoom({ position: 'bottomright' }).addTo(map);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19,
}).addTo(map);

// ── Cluster group ─────────────────────────────────────────────────────────────
var clusters = L.markerClusterGroup({
    chunkedLoading       : true,
    chunkInterval        : 200,
    chunkDelay           : 50,
    maxClusterRadius     : 65,
    spiderfyOnMaxZoom    : true,
    showCoverageOnHover  : false,
    zoomToBoundsOnClick  : true,
    iconCreateFunction: function (cluster) {
        var kids   = cluster.getAllChildMarkers();
        var total  = kids.length;
        var online = kids.filter(function (m) { return m.options.isOnline; }).length;
        var ratio  = online / total;
        var color  = ratio > 0.65 ? '#198754' : ratio > 0.35 ? '#fd7e14' : '#dc3545';
        var sz     = total < 10 ? 'mc-s' : total < 50 ? 'mc-m' : total < 200 ? 'mc-l' : 'mc-xl';
        var px     = { 'mc-s': 38, 'mc-m': 48, 'mc-l': 58, 'mc-xl': 70 }[sz];
        return L.divIcon({
            html      : '<div class="mc ' + sz + '" style="background:' + color + '">' + total + '</div>',
            className : '',
            iconSize  : [px, px],
            iconAnchor: [px / 2, px / 2],
        });
    },
});

// ── Per-device pin icon ───────────────────────────────────────────────────────
function pinIcon(online, status) {
    var c = online ? '#198754' : (status === 'off' ? '#dc3545' : '#6c757d');
    return L.divIcon({
        html       : '<div class="dev-pin" style="background:' + c + '"></div>',
        iconSize   : [14, 14],
        iconAnchor : [7, 14],
        popupAnchor: [0, -16],
        className  : '',
    });
}

// ── Battery bar HTML ─────────────────────────────────────────────────────────
function battHtml(pct) {
    if (pct === null || pct === undefined) return '';
    var cls = pct > 50 ? 'batt-green' : pct > 20 ? 'batt-yellow' : 'batt-red';
    var w   = Math.max(2, pct);
    return '<span class="batt-bar ' + cls + '" style=""><span style="position:absolute;top:1px;left:1px;bottom:1px;width:' + w + '%;border-radius:1px;background:currentColor"></span></span>';
}

// ── Popup HTML ────────────────────────────────────────────────────────────────
function makePopup(d) {
    // Status badge
    var statusTag = d.online
        ? '<span class="p-tag" style="background:#d1fae5;color:#065f46"><i class="bi bi-wifi" style="font-size:.7rem"></i>Online</span>'
        : '<span class="p-tag" style="background:#fee2e2;color:#991b1b"><i class="bi bi-wifi-off" style="font-size:.7rem"></i>Offline</span>';

    // Configuration badge
    var cfgTag = d.config
        ? '<span class="p-tag" style="background:#eef2ff;color:#3730a3"><i class="bi bi-gear" style="font-size:.7rem"></i>' + esc(d.config) + '</span>'
        : '';

    // Battery
    var battTag = (d.battery !== null && d.battery !== undefined)
        ? '<span class="p-tag" style="background:#f0fdf4;color:#166534">'
            + battHtml(d.battery)
            + d.battery + '%</span>'
        : '';

    // Employee row
    var empRow = d.employee
        ? '<div class="p-emp"><i class="bi bi-person-fill" style="color:#6366f1"></i> <b>' + esc(d.employee.name) + '</b>'
            + (d.employee.desig ? ' <span style="color:#888">&mdash; ' + esc(d.employee.desig) + '</span>' : '')
            + (d.employee.code  ? ' <span style="color:#aaa;font-size:.68rem">(' + esc(d.employee.code)  + ')</span>' : '')
            + '</div>'
        : '<div class="p-emp" style="color:#bbb"><i class="bi bi-person"></i> Not linked</div>';

    // Sync time
    var syncRow = d.sync_ts
        ? '<div class="p-row"><i class="bi bi-clock-history"></i>' + esc(d.sync_ts) + ' <span style="color:#bbb">(' + esc(d.sync_age) + ')</span></div>'
        : '';

    // Android version
    var androidRow = d.android
        ? '<div class="p-row"><i class="bi bi-phone"></i>Android ' + esc(d.android) + '</div>'
        : '';

    // Coordinates
    var geoRow = '<div class="p-row"><i class="bi bi-geo-alt"></i>' + d.lat.toFixed(6) + ', ' + d.lng.toFixed(6) + '</div>';

    return '<div class="mdm-pop">'
        + '<div class="p-num">' + esc(d.number) + '</div>'
        + '<div class="p-sub">' + esc(d.model || '—') + (d.group ? ' &bull; ' + esc(d.group) : '') + '</div>'
        + '<div class="p-tags">' + statusTag + cfgTag + battTag + '</div>'
        + empRow
        + syncRow
        + androidRow
        + geoRow
        + '<div class="p-acts">'
        +   '<a href="' + d.url + '" class="p-btn" style="background:#0d6efd;color:#fff">View Details</a>'
        +   '<a href="https://maps.google.com/?q=' + d.lat + ',' + d.lng + '" target="_blank" class="p-btn" style="background:#198754;color:#fff">Google Maps</a>'
        + '</div></div>';
}

// ── Build all markers ─────────────────────────────────────────────────────────
var markerById = {};
var listEl     = document.getElementById('deviceList');
var frag       = document.createDocumentFragment();

DEVICES.forEach(function (d) {
    if (!d.lat || !d.lng) return;

    var m = L.marker([d.lat, d.lng], {
        icon    : pinIcon(d.online, d.status),
        isOnline: d.online,
        deviceId: d.id,
    });
    m.bindPopup(makePopup(d), { maxWidth: 290 });
    markerById[d.id] = { m: m, d: d };

    // Sidebar row
    var row = document.createElement('div');
    row.className  = 'dev-row';
    row.dataset.id = d.id;
    var dotC       = d.online ? '#198754' : '#dc3545';
    row.innerHTML  =
        '<div class="d-flex align-items-center gap-2">'
        + '<span class="s-dot" style="background:' + dotC + '"></span>'
        + '<span class="fw-semibold font-monospace" style="font-size:.75rem">' + esc(d.number) + '</span>'
        + (d.config ? '<span class="ms-auto text-muted" style="font-size:.63rem;max-width:90px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="' + esc(d.config) + '">' + esc(d.config) + '</span>' : '')
        + '</div>'
        + '<div class="text-muted ps-3" style="font-size:.69rem">'
        + esc(d.model || '—')
        + (d.employee ? ' &middot; ' + esc(d.employee.name) : '')
        + (d.sync_ts ? ' <span style="color:#bbb;font-size:.65rem">' + esc(d.sync_age) + '</span>' : '')
        + '</div>';
    row.addEventListener('click', function () {
        document.querySelectorAll('.dev-row.active').forEach(function (el) { el.classList.remove('active'); });
        row.classList.add('active');
        clusters.zoomToShowLayer(m, function () { m.openPopup(); });
    });
    frag.appendChild(row);
});
listEl.appendChild(frag);

// Add all layers initially
Object.values(markerById).forEach(function (o) { clusters.addLayer(o.m); });
map.addLayer(clusters);

// ── Fit bounds ────────────────────────────────────────────────────────────────
var pts = DEVICES.filter(function (d) { return d.lat && d.lng; }).map(function (d) { return [d.lat, d.lng]; });
if (pts.length) {
    try { map.fitBounds(pts, { padding: [60, 60], maxZoom: 13 }); } catch (e) {}
}

// ── Client-side filtering ────────────────────────────────────────────────────
function applyFilters() {
    var status = document.getElementById('fStatus').value;
    var group  = document.getElementById('fGroup').value;
    var config = document.getElementById('fConfig').value;
    var linked = document.getElementById('fLinked').value;
    var search = (document.getElementById('fSearch').value || '').toLowerCase().trim();
    var side   = (document.getElementById('sideSearch').value || '').toLowerCase().trim();
    var active = !!(status || group || config || linked || search || side);

    document.getElementById('fClear').classList.toggle('d-none', !active);

    clusters.clearLayers();
    var shown = 0, onShown = 0;

    DEVICES.forEach(function (d) {
        var pass = true;
        if (status === 'on'  && !d.online)  pass = false;
        if (status === 'off' && d.online)   pass = false;
        if (group  && d.group !== group)    pass = false;
        if (config && d.config !== config)  pass = false;
        if (linked === 'yes' && !d.linked)  pass = false;
        if (linked === 'no'  && d.linked)   pass = false;
        if (search && !d.q.includes(search)) pass = false;

        // Also filter sidebar separately
        var sidePass = pass && (!side || d.q.includes(side));
        var row = listEl.querySelector('[data-id="' + d.id + '"]');
        if (row) row.style.display = sidePass ? '' : 'none';

        if (pass && markerById[d.id]) {
            clusters.addLayer(markerById[d.id].m);
            shown++;
            if (d.online) onShown++;
        }
    });

    // Update counts
    document.getElementById('listCount').textContent = shown;
    document.getElementById('fOnline').textContent   = onShown;
    document.getElementById('fOffline').textContent  = shown - onShown;
    document.getElementById('fTotal').textContent    = shown;
    document.getElementById('oOnline').textContent   = onShown;
    document.getElementById('oOffline').textContent  = shown - onShown;
    document.getElementById('oTotal').textContent    = shown;
}

['fStatus','fGroup','fConfig','fLinked'].forEach(function (id) {
    document.getElementById(id).addEventListener('change', applyFilters);
});
document.getElementById('fSearch').addEventListener('input', applyFilters);
document.getElementById('sideSearch').addEventListener('input', applyFilters);

document.getElementById('fClear').addEventListener('click', function () {
    document.getElementById('fStatus').value  = '';
    document.getElementById('fGroup').value   = '';
    document.getElementById('fConfig').value  = '';
    document.getElementById('fLinked').value  = '';
    document.getElementById('fSearch').value  = '';
    document.getElementById('sideSearch').value = '';
    applyFilters();
});

// ── Sidebar panel toggle ──────────────────────────────────────────────────────
var panel   = document.getElementById('sidePanel');
var showBtn = document.getElementById('showPanelBtn');

document.getElementById('hidePanelBtn').addEventListener('click', function () {
    panel.classList.add('hidden');
    showBtn.classList.add('vis');
});
showBtn.addEventListener('click', function () {
    panel.classList.remove('hidden');
    showBtn.classList.remove('vis');
});

// ── Full screen toggle ─────────────────────────────────────────────────────────
var mapOuter    = document.getElementById('mapOuter');
var fsBtn       = document.getElementById('fullscreenBtn');
var fsBtnIcon   = fsBtn.querySelector('i');

function isFullscreen() {
    return !!(document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement);
}

fsBtn.addEventListener('click', function () {
    if (!isFullscreen()) {
        var req = mapOuter.requestFullscreen || mapOuter.webkitRequestFullscreen || mapOuter.msRequestFullscreen;
        if (req) req.call(mapOuter);
    } else {
        var exit = document.exitFullscreen || document.webkitExitFullscreen || document.msExitFullscreen;
        if (exit) exit.call(document);
    }
});

function onFullscreenChange() {
    var active = isFullscreen();
    fsBtnIcon.className = active ? 'bi bi-fullscreen-exit' : 'bi bi-arrows-fullscreen';
    fsBtn.title = active ? 'Exit full screen' : 'Full screen';
    setTimeout(function () { map.invalidateSize(); }, 150);
}
['fullscreenchange', 'webkitfullscreenchange', 'msfullscreenchange'].forEach(function (ev) {
    document.addEventListener(ev, onFullscreenChange);
});

// ── Highlight sidebar row when marker popup opens ─────────────────────────────
clusters.on('click', function (e) {
    var id = e.layer.options.deviceId;
    if (!id) return;
    document.querySelectorAll('.dev-row.active').forEach(function (el) { el.classList.remove('active'); });
    var row = listEl.querySelector('[data-id="' + id + '"]');
    if (row) {
        row.classList.add('active');
        row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});

// ── Helper ────────────────────────────────────────────────────────────────────
function esc(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

})();
</script>
@endpush
@endif
