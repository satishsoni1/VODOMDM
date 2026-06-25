@extends('layouts.main')
@section('title','Device — '.$mdm->pg_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mdm.index') }}">MDM</a></li>
    <li class="breadcrumb-item"><a href="{{ route('mdm.devices') }}">Devices</a></li>
    <li class="breadcrumb-item active">{{ $mdm->pg_number }}</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #gpsMap { height:280px; border-radius:0 0 8px 8px; }
    .param-row { display:flex; justify-content:space-between; align-items:center;
                 padding:.3rem 0; border-bottom:1px solid #f0f2f4; font-size:.82rem; }
    .param-row:last-child { border-bottom:none; }
    .param-key { color:#6c757d; flex:0 0 50%; }
    .param-val { font-weight:600; font-family:monospace; text-align:right; word-break:break-all; }
    .bar-wrap  { height:7px; background:#e9ecef; border-radius:4px; overflow:hidden; }
    .bar-fill  { height:100%; border-radius:4px; }
</style>
@endpush

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0"
                 style="width:52px;height:52px;background:{{ $mdm->isOnline() ? '#198754' : '#6c757d' }};font-size:1.4rem">
                <i class="bi bi-phone-fill"></i>
            </div>
            <div class="flex-grow-1">
                <h4 class="fw-bold mb-0 font-monospace">{{ $mdm->pg_number }}</h4>
                <div class="text-muted small">
                    {{ $mdm->model ?? '—' }}
                    @php $brand = $deviceParams['brand'] ?? $deviceParams['manufacturer'] ?? null; @endphp
                    @if($brand) &nbsp;&middot;&nbsp; {{ $brand }} @endif
                    @if($mdm->android_version) &nbsp;&middot;&nbsp; Android {{ $mdm->android_version }} @endif
                    @if($mdm->mdm_group) &nbsp;&middot;&nbsp; {{ $mdm->mdm_group }} @endif
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                @if($mdm->isOnline())
                    <span class="badge bg-success-subtle text-success border px-3 py-2">
                        <i class="bi bi-wifi me-1"></i>Online
                    </span>
                @else
                    <span class="badge bg-danger-subtle text-danger border px-3 py-2">
                        <i class="bi bi-wifi-off me-1"></i>Offline
                    </span>
                @endif
                @if($battery !== null)
                    <span class="badge bg-{{ $battery>50?'success':($battery>20?'warning':'danger') }}-subtle
                                 text-{{ $battery>50?'success':($battery>20?'warning':'danger') }} border px-3 py-2">
                        <i class="bi bi-battery{{ $battery>75?'-full':($battery>25?'-half':'') }} me-1"></i>
                        {{ $battery }}%
                    </span>
                @endif
                @if($mdm->latitude)
                    <span class="badge bg-success-subtle text-success border px-3 py-2">
                        <i class="bi bi-geo-alt-fill me-1"></i>GPS
                    </span>
                @endif
            </div>
            <div class="text-end small">
                <div class="text-{{ $mdm->syncFreshnessClass() }} fw-semibold">{{ $mdm->syncAgeLabel() }}</div>
                <div class="text-muted">{{ $mdm->sync_time?->format('d M H:i') ?? 'Never' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ── 2-col layout ────────────────────────────────────────────────────────── --}}
<div class="row g-4">

    {{-- LEFT: Tabs ─────────────────────────────────────────────────────────── --}}
    <div class="col-xl-8">
        <ul class="nav nav-tabs mb-3" id="devTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-overview">
                    <i class="bi bi-info-circle me-1"></i>Overview
                </a>
            </li>
            @if($deviceParams)
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-hardware">
                    <i class="bi bi-cpu me-1"></i>Hardware
                    <span class="badge bg-success-subtle text-success border ms-1" style="font-size:.6rem">live</span>
                </a>
            </li>
            @endif
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-gps">
                    <i class="bi bi-geo-alt-fill me-1"></i>GPS
                    @if($mdm->latitude)
                    <span class="badge bg-success-subtle text-success border ms-1" style="font-size:.6rem">fix</span>
                    @endif
                </a>
            </li>
            @if(count($apps))
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-apps">
                    <i class="bi bi-grid me-1"></i>Apps
                    <span class="badge bg-secondary-subtle text-secondary border ms-1" style="font-size:.6rem">{{ count($apps) }}</span>
                </a>
            </li>
            @endif
        </ul>

        <div class="tab-content">

            {{-- Overview ── --}}
            <div class="tab-pane fade show active" id="tab-overview">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row g-3">
                            @php
                            $fields = [
                                'Device Number'    => $mdm->pg_number,
                                'Serial Number'    => $mdm->serial_number,
                                'IMEI'             => $mdm->imei,
                                'Model'            => $mdm->model,
                                'Android Version'  => $mdm->android_version ?: null,
                                'Phone Number'     => $mdm->phone ?? null,
                                'IP Address'       => $mdm->ip_address ?? null,
                                'Public IP'        => $mdm->public_ip ?? null,
                                'MDM Group'        => $mdm->mdm_group ?? null,
                                'Configuration'    => $mdm->configuration ?? null,
                                'Launcher'         => $mdm->default_launcher ?? null,
                                'Launcher Version' => $mdm->launcher_version ?? null,
                                'Division'         => $mdm->division ?? null,
                                'Description'      => $mdm->description ?? null,
                                'MDM Mode'         => $mdm->mdm_mode  ? 'Enabled' : 'Disabled',
                                'Kiosk Mode'       => $mdm->kiosk_mode ? 'Enabled' : 'Disabled',
                                'Enrolled'         => $mdm->enrollment_date?->format('d M Y H:i'),
                                'Last Sync'        => $mdm->sync_time?->format('d M Y H:i'),
                                'PG Synced At'     => $mdm->pg_synced_at?->format('d M Y H:i'),
                            ];
                            @endphp
                            @foreach($fields as $label => $val)
                            @if($val !== null)
                            <div class="col-md-4">
                                <div class="text-muted" style="font-size:.7rem">{{ $label }}</div>
                                <div class="fw-semibold font-monospace small">{{ $val }}</div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        @if($mdm->permission_status)
                        <hr class="my-3">
                        <div class="small {{ $mdm->isPermissionCompliant() ? 'text-success' : 'text-warning' }}">
                            <i class="bi bi-shield-{{ $mdm->isPermissionCompliant() ? 'check' : 'exclamation' }} me-1"></i>
                            {{ $mdm->permission_status }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Hardware ── --}}
            @if($deviceParams)
            <div class="tab-pane fade" id="tab-hardware">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-cpu text-success"></i>
                        <strong>Hardware Parameters</strong>
                        <small class="text-muted ms-auto">plugin_deviceinfo_deviceparams_device</small>
                    </div>
                    <div class="card-body">
                        @php
                            $skipP   = ['id','deviceid','device_id','ts','timestamp','createdate','created_at'];
                            $batKeys = ['battery','battery_level','batterylevel','battery_percentage','batterypercentage'];
                            $storKeys= ['total_internal_storage','totalinternalstorage','available_internal_storage',
                                        'availableinternalstorage','total_external_storage','available_external_storage'];
                            $ramKeys = ['total_ram','totalram','available_ram','availableram','used_ram'];

                            $totalStorage = $freeStorage = $totalRam = $freeRam = null;
                            foreach (['total_internal_storage','totalinternalstorage'] as $k)
                                if (isset($deviceParams[$k]) && is_numeric($deviceParams[$k])) { $totalStorage=(int)$deviceParams[$k]; break; }
                            foreach (['available_internal_storage','availableinternalstorage'] as $k)
                                if (isset($deviceParams[$k]) && is_numeric($deviceParams[$k])) { $freeStorage=(int)$deviceParams[$k]; break; }
                            foreach (['total_ram','totalram'] as $k)
                                if (isset($deviceParams[$k]) && is_numeric($deviceParams[$k])) { $totalRam=(int)$deviceParams[$k]; break; }
                            foreach (['available_ram','availableram'] as $k)
                                if (isset($deviceParams[$k]) && is_numeric($deviceParams[$k])) { $freeRam=(int)$deviceParams[$k]; break; }

                            $fmt = function($b) {
                                if ($b < 1048576)    return round($b/1024).' KB';
                                if ($b < 1073741824) return round($b/1048576,1).' MB';
                                return round($b/1073741824,2).' GB';
                            };
                        @endphp

                        @if($battery !== null)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="fw-semibold">Battery</span>
                                <span class="fw-bold text-{{ $battery>50?'success':($battery>20?'warning':'danger') }}">{{ $battery }}%</span>
                            </div>
                            <div class="bar-wrap">
                                <div class="bar-fill bg-{{ $battery>50?'success':($battery>20?'warning':'danger') }}" style="width:{{ $battery }}%"></div>
                            </div>
                        </div>
                        @endif

                        @if($totalStorage && $freeStorage !== null)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="fw-semibold">Internal Storage</span>
                                <span class="text-muted">{{ $fmt($freeStorage) }} free / {{ $fmt($totalStorage) }}</span>
                            </div>
                            <div class="bar-wrap">
                                <div class="bar-fill bg-info" style="width:{{ $totalStorage>0 ? round(($totalStorage-$freeStorage)/$totalStorage*100) : 0 }}%"></div>
                            </div>
                        </div>
                        @endif

                        @if($totalRam && $freeRam !== null)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="fw-semibold">RAM</span>
                                <span class="text-muted">{{ $fmt($freeRam) }} free / {{ $fmt($totalRam) }}</span>
                            </div>
                            <div class="bar-wrap">
                                <div class="bar-fill bg-primary" style="width:{{ $totalRam>0 ? round(($totalRam-$freeRam)/$totalRam*100) : 0 }}%"></div>
                            </div>
                        </div>
                        @endif

                        @foreach($deviceParams as $k => $v)
                            @php $kl=strtolower($k); @endphp
                            @if(in_array($kl,$skipP)||in_array($kl,$batKeys)||in_array($kl,$storKeys)||in_array($kl,$ramKeys)||$v===null||$v==='') @continue @endif
                            <div class="param-row">
                                <span class="param-key">{{ ucwords(str_replace('_',' ',$k)) }}</span>
                                <span class="param-val">{{ is_bool($v)?($v?'Yes':'No'):$v }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- GPS ── --}}
            <div class="tab-pane fade" id="tab-gps">
                @if($mdm->latitude && $mdm->longitude)
                @php
                    $gpsTs = null;
                    if ($lastGps) {
                        foreach (['ts','timestamp','createdate','created_at'] as $tk) {
                            if (!empty($lastGps[$tk])) {
                                try { $raw=$lastGps[$tk]; $gpsTs=is_numeric($raw)?\Carbon\Carbon::createFromTimestampMs((int)$raw):\Carbon\Carbon::parse($raw); } catch(\Throwable $e) {}
                                break;
                            }
                        }
                    }
                @endphp
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill text-success"></i>
                        <strong>Last GPS Fix</strong>
                        <small class="text-muted ms-1">plugin_deviceinfo_deviceparams_gps</small>
                        @if($gpsTs)
                        <span class="ms-auto text-muted small">
                            <i class="bi bi-clock me-1"></i>{{ $gpsTs->diffForHumans() }}
                        </span>
                        @endif
                    </div>
                    <div id="gpsMap"></div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-6 col-md-3">
                                <div class="text-muted small">Latitude</div>
                                <div class="fw-bold font-monospace">{{ $mdm->latitude }}</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted small">Longitude</div>
                                <div class="fw-bold font-monospace">{{ $mdm->longitude }}</div>
                            </div>
                            @if($gpsTs)
                            <div class="col-6 col-md-3">
                                <div class="text-muted small">GPS Time</div>
                                <div class="fw-bold small">{{ $gpsTs->format('d M Y H:i') }}</div>
                            </div>
                            @endif
                            @foreach($lastGps ?? [] as $gk => $gv)
                                @php $gkl=strtolower($gk); @endphp
                                @if(in_array($gkl,['id','deviceid','device_id','lat','latitude','lon','lng','longitude','ts','timestamp','createdate','created_at'])||$gv===null||$gv==='') @continue @endif
                                <div class="col-6 col-md-3">
                                    <div class="text-muted small">{{ ucwords(str_replace('_',' ',$gk)) }}</div>
                                    <div class="fw-bold font-monospace small">{{ $gv }}</div>
                                </div>
                            @endforeach
                        </div>
                        <a href="https://maps.google.com/?q={{ $mdm->latitude }},{{ $mdm->longitude }}"
                           target="_blank" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-map me-1"></i>Open in Google Maps
                        </a>
                        &nbsp;
                        <a href="{{ route('mdm.map') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-geo-alt me-1"></i>Fleet Map
                        </a>
                    </div>
                </div>
                @else
                <div class="alert alert-warning border-0">
                    <i class="bi bi-geo-alt me-2"></i>
                    No GPS data. Run sync to fetch from <code>plugin_deviceinfo_deviceparams_gps</code>.
                </div>
                @endif
            </div>

            {{-- Apps ── --}}
            @if(count($apps))
            <div class="tab-pane fade" id="tab-apps">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex justify-content-between">
                        <strong><i class="bi bi-grid me-2 text-primary"></i>Installed Apps</strong>
                        <div class="d-flex gap-2">
                            @php $outCount = collect($apps)->where('outdated',true)->count(); @endphp
                            @if($outCount)
                            <span class="badge bg-warning-subtle text-warning border">{{ $outCount }} updates</span>
                            @endif
                            <span class="badge bg-secondary-subtle text-secondary border">{{ count($apps) }} apps</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 small">
                            <thead class="table-light">
                                <tr><th>App</th><th>Installed</th><th>Available</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @foreach($apps as $app)
                                <tr class="{{ $app['outdated']?'table-warning':'' }}">
                                    <td class="fw-semibold">{{ $app['name'] }}</td>
                                    <td class="font-monospace">{{ $app['installed'] }}</td>
                                    <td class="font-monospace">{{ $app['available'] ?? '—' }}</td>
                                    <td>
                                        @if($app['outdated'])
                                            <span class="badge bg-warning-subtle text-warning border">Update</span>
                                        @elseif($app['available'])
                                            <span class="badge bg-success-subtle text-success border">Current</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border">Installed</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- RIGHT sidebar ───────────────────────────────────────────────────────── --}}
    <div class="col-xl-4">

        {{-- Employee ── --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-person-check me-2 text-success"></i>Linked Employee</strong>
                @if($mdm->employee)
                <a href="{{ route('employees.show', $mdm->employee) }}"
                   class="btn btn-sm btn-outline-success py-0 px-2" style="font-size:.72rem">View</a>
                @endif
            </div>
            <div class="card-body">
                @if($mdm->employee)
                <div class="d-flex gap-3 p-2 bg-success-subtle rounded mb-3">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                         style="width:40px;height:40px;font-size:1rem">
                        {{ strtoupper(substr($mdm->employee->name,0,1)) }}
                    </div>
                    <div>
                        <div class="fw-bold">{{ $mdm->employee->name }}</div>
                        <div class="text-muted small">{{ $mdm->employee->employee_code }} &middot; {{ $mdm->employee->designation }}</div>
                        @if($mdm->employee->client)
                        <div class="text-muted small">{{ $mdm->employee->client->name }}</div>
                        @endif
                        @if($mdm->employee->phone)
                        <div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $mdm->employee->phone }}</div>
                        @endif
                        @if($mdm->employee->city)
                        <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i>{{ $mdm->employee->city }}</div>
                        @endif
                    </div>
                </div>
                @else
                <div class="text-center text-muted py-2 mb-3">
                    <i class="bi bi-person-x fs-3 d-block mb-1 opacity-30"></i>
                    Not linked to any employee
                </div>
                @endif
                <a href="{{ route('mdm.link', ['q'=>$mdm->pg_number]) }}" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-link-45deg me-1"></i>
                    {{ $mdm->employee ? 'Change Link' : 'Link Employee' }}
                </a>
            </div>
        </div>

        {{-- Asset ── --}}
        @if($mdm->device)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><strong><i class="bi bi-phone me-2 text-info"></i>Linked Asset</strong></div>
            <div class="card-body small">
                <div class="fw-bold">{{ $mdm->device->asset_tag }}</div>
                @if($mdm->device->model)
                <div class="text-muted">{{ $mdm->device->model->model_name }}</div>
                @endif
                <div class="text-muted">S/N: {{ $mdm->device->serial_number }}</div>
                <a href="{{ route('devices.show', $mdm->device) }}" class="btn btn-outline-info btn-sm w-100 mt-2">
                    <i class="bi bi-phone me-1"></i>View Asset
                </a>
            </div>
        </div>
        @endif

        {{-- Quick facts ── --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header"><strong><i class="bi bi-info-circle me-2"></i>Quick Facts</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small">
                    <tbody>
                        @if($battery !== null)
                        <tr>
                            <td class="text-muted">Battery</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bar-wrap flex-grow-1">
                                        <div class="bar-fill bg-{{ $battery>50?'success':($battery>20?'warning':'danger') }}" style="width:{{ $battery }}%"></div>
                                    </div>
                                    <span class="fw-semibold">{{ $battery }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Enrolled</td>
                            <td>{{ $mdm->enrollment_date?->format('d M Y') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">MDM Mode</td>
                            <td><span class="badge bg-{{ $mdm->mdm_mode?'success':'secondary' }}-subtle text-{{ $mdm->mdm_mode?'success':'secondary' }} border">{{ $mdm->mdm_mode?'ON':'OFF' }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kiosk</td>
                            <td><span class="badge bg-{{ $mdm->kiosk_mode?'warning':'secondary' }}-subtle text-{{ $mdm->kiosk_mode?'warning':'secondary' }} border">{{ $mdm->kiosk_mode?'ON':'OFF' }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">IP Address</td>
                            <td class="font-monospace">{{ $mdm->ip_address ?? '—' }}</td>
                        </tr>
                        @if($mdm->latitude)
                        <tr>
                            <td class="text-muted">Coordinates</td>
                            <td>
                                <a href="https://maps.google.com/?q={{ $mdm->latitude }},{{ $mdm->longitude }}" target="_blank" class="font-monospace small">
                                    {{ round($mdm->latitude,4) }}, {{ round($mdm->longitude,4) }}
                                </a>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
@if($mdm->latitude && $mdm->longitude)
(function(){
    const map = L.map('gpsMap').setView([{{ (float)$mdm->latitude }}, {{ (float)$mdm->longitude }}], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 19
    }).addTo(map);
    L.marker([{{ (float)$mdm->latitude }}, {{ (float)$mdm->longitude }}])
        .addTo(map)
        .bindPopup('<b>{{ $mdm->pg_number }}</b><br>{{ addslashes($mdm->model ?? "") }}')
        .openPopup();
})();
@endif
</script>
@endpush
