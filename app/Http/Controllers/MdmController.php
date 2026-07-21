<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Employee;
use App\Models\MdmDevice;
use App\Models\MdmImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MdmController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────────────────────────
    public function dashboard()
    {
        $total    = MdmDevice::count();
        $online   = MdmDevice::where('device_status', 'on')->count();
        $offline  = MdmDevice::where('device_status', 'off')->count();
        $linked   = MdmDevice::whereNotNull('local_employee_id')->count();
        $unlinked = $total - $linked;
        $withGps  = MdmDevice::whereNotNull('latitude')->count();

        $models = MdmDevice::select('model', DB::raw('count(*) as cnt'))
            ->whereNotNull('model')->where('model', '!=', '')
            ->groupBy('model')->orderByDesc('cnt')->limit(10)
            ->pluck('cnt', 'model');

        $groups = MdmDevice::select('mdm_group', DB::raw('count(*) as cnt'))
            ->whereNotNull('mdm_group')
            ->groupBy('mdm_group')->orderByDesc('cnt')
            ->pluck('cnt', 'mdm_group');

        $androidVersions = MdmDevice::select('android_version', DB::raw('count(*) as cnt'))
            ->whereNotNull('android_version')->where('android_version', '!=', '')
            ->groupBy('android_version')->orderByDesc('cnt')->limit(8)
            ->pluck('cnt', 'android_version');

        $designationStats = Employee::select(
                'employees.designation',
                DB::raw('COUNT(DISTINCT employees.id) as emp_total'),
                DB::raw('COUNT(DISTINCT mdm_devices.id) as device_count')
            )
            ->leftJoin('mdm_devices', 'mdm_devices.local_employee_id', '=', 'employees.id')
            ->whereNotNull('employees.designation')
            ->groupBy('employees.designation')
            ->orderByDesc(DB::raw('COUNT(DISTINCT employees.id)'))
            ->limit(10)
            ->get();

        $attention = MdmDevice::with(['employee.client'])
            ->where(function ($q) {
                $q->where('device_status', 'off')
                  ->orWhereNull('latitude')
                  ->orWhereNull('local_employee_id');
            })
            ->orderByRaw("FIELD(device_status,'off','unknown','on')")
            ->orderBy('sync_time')
            ->limit(20)
            ->get();

        $lastSync = MdmImportLog::latest()->first();

        return view('mdm.dashboard', compact(
            'total', 'online', 'offline', 'linked', 'unlinked', 'withGps',
            'models', 'groups', 'androidVersions',
            'designationStats', 'attention', 'lastSync'
        ));
    }

    // ── Sync Management ───────────────────────────────────────────────────────
    public function sync()
    {
        $logs    = MdmImportLog::latest()->paginate(25)->withQueryString();
        $lastLog = MdmImportLog::latest()->first();
        $pgReady = extension_loaded('pdo_pgsql');
        $total   = MdmDevice::count();
        $synced  = MdmDevice::whereNotNull('pg_synced_at')->count();
        $pgHost  = config('database.connections.mdm_pgsql.host');
        $pgDb    = config('database.connections.mdm_pgsql.database');

        return view('mdm.sync', compact(
            'logs', 'lastLog', 'pgReady', 'total', 'synced', 'pgHost', 'pgDb'
        ));
    }

    public function runSync(Request $request)
    {
        if (Cache::has('mdm_sync_running')) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Sync is already running.'], 409)
                : redirect()->route('mdm.sync')->with('warning', 'Sync is already running. Check progress below.');
        }

        // Note: the actual lock is acquired atomically inside MdmSyncCommand::handle()
        // (via Cache::add) so it also guards against a directly-invoked `php artisan
        // mdm:sync` colliding with this web-triggered run. This check is just a fast
        // pre-flight UX rejection.
        Cache::put('mdm_sync_progress', [
            'status'     => 'starting',
            'started_at' => now()->timestamp,
            'updated_at' => now()->timestamp,
            'stages'     => [],
            'result'     => null,
            'error'      => null,
        ], 3600);

        $php     = PHP_BINARY;
        $artisan = base_path('artisan');
        $userId  = Auth::id() ?? 1;

        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen("start \"MDMSync\" /B \"{$php}\" \"{$artisan}\" mdm:sync --user={$userId}", 'r'));
        } else {
            exec("\"{$php}\" \"{$artisan}\" mdm:sync --user={$userId} > /dev/null 2>&1 &");
        }

        return $request->expectsJson()
            ? response()->json(['started' => true])
            : redirect()->route('mdm.sync');
    }

    public function syncProgress()
    {
        $progress = Cache::get('mdm_sync_progress');
        if (! $progress) {
            return response()->json(['status' => 'idle', 'stages' => []]);
        }
        return response()->json($progress);
    }

    public function autoMatch()
    {
        $matched = $this->runAutoMatchInternal();
        return back()->with('success', "Auto-matched {$matched} devices by IMEI / Serial Number.");
    }

    // ── Device List ───────────────────────────────────────────────────────────
    public function devices(Request $request)
    {
        $q = MdmDevice::with(['employee.client', 'device', 'hardware', 'locationLatest']);

        if ($request->filled('status'))  $q->where('device_status', $request->status);
        if ($request->filled('group'))   $q->where('mdm_group', $request->group);
        if ($request->filled('model'))   $q->where('model', $request->model);
        if ($request->filled('linked')) {
            $request->linked === 'yes'
                ? $q->whereNotNull('local_employee_id')
                : $q->whereNull('local_employee_id');
        }
        if ($request->filled('gps')) {
            $request->gps === 'yes'
                ? $q->whereNotNull('latitude')
                : $q->whereNull('latitude');
        }
        if ($request->filled('q')) {
            $search = $request->q;
            $q->where(function ($sub) use ($search) {
                $sub->where('pg_number', 'LIKE', "%{$search}%")
                    ->orWhere('imei', 'LIKE', "%{$search}%")
                    ->orWhere('serial_number', 'LIKE', "%{$search}%")
                    ->orWhere('model', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $devices   = $q->orderByDesc('sync_time')->paginate(30)->withQueryString();
        $groups    = MdmDevice::whereNotNull('mdm_group')->distinct()->orderBy('mdm_group')->pluck('mdm_group');
        $modelList = MdmDevice::whereNotNull('model')->where('model', '!=', '')->distinct()->orderBy('model')->pluck('model');

        $stats = [
            'total'   => MdmDevice::count(),
            'online'  => MdmDevice::where('device_status', 'on')->count(),
            'offline' => MdmDevice::where('device_status', 'off')->count(),
            'linked'  => MdmDevice::whereNotNull('local_employee_id')->count(),
            'gps'     => MdmDevice::whereNotNull('latitude')->count(),
        ];

        return view('mdm.devices', compact('devices', 'groups', 'modelList', 'stats'));
    }

    // ── Device Detail ─────────────────────────────────────────────────────────
    public function show(MdmDevice $mdm)
    {
        $mdm->load(['hardware', 'gps', 'locationLatest', 'employee.client', 'device.model']);

        // Prefer locationLatest GPS over device table lat/lng
        if ($mdm->locationLatest && $mdm->locationLatest->latitude) {
            $mdm->latitude  = $mdm->locationLatest->latitude;
            $mdm->longitude = $mdm->locationLatest->longitude;
        }

        return view('mdm.show', [
            'mdm'          => $mdm,
            'deviceInfo'   => [],
            'deviceParams' => $mdm->hardware?->raw_json,
            'lastGps'      => $mdm->locationLatest?->raw_json ?? $mdm->gps?->raw_json,
            'battery'      => $mdm->batteryLevel(),
            'apps'         => $mdm->parsedApps(),
        ]);
    }

    // ── Device-Employee Linking ───────────────────────────────────────────────
    public function link(Request $request)
    {
        $filter = $request->input('filter', 'all');

        $q = MdmDevice::with(['employee.client', 'device']);

        if ($filter === 'linked')   $q->whereNotNull('local_employee_id');
        if ($filter === 'unlinked') $q->whereNull('local_employee_id');

        if ($request->filled('q')) {
            $search = $request->q;
            $q->where(function ($sub) use ($search) {
                $sub->where('pg_number', 'LIKE', "%{$search}%")
                    ->orWhere('imei', 'LIKE', "%{$search}%")
                    ->orWhere('serial_number', 'LIKE', "%{$search}%")
                    ->orWhere('model', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('group'))  $q->where('mdm_group', $request->group);
        if ($request->filled('status')) $q->where('device_status', $request->status);
        if ($request->filled('client')) {
            $clientId = $request->client;
            $q->whereHas('employee', fn ($eq) => $eq->where('client_id', $clientId));
        }

        $q->orderByRaw('ISNULL(local_employee_id) DESC, sync_time DESC');
        $devices   = $q->paginate(30)->withQueryString();

        $groups    = MdmDevice::whereNotNull('mdm_group')->distinct()->orderBy('mdm_group')->pluck('mdm_group');
        $clients   = Client::orderBy('name')->get();
        $employees = Employee::with('client')->orderBy('name')->get()
                        ->groupBy(fn ($e) => $e->client?->name ?? 'No Client');

        $stats = [
            'total'    => MdmDevice::count(),
            'linked'   => MdmDevice::whereNotNull('local_employee_id')->count(),
            'unlinked' => MdmDevice::whereNull('local_employee_id')->count(),
        ];

        return view('mdm.link', compact(
            'devices', 'groups', 'clients', 'employees', 'stats', 'filter'
        ));
    }

    public function saveLink(Request $request, MdmDevice $mdm)
    {
        $request->validate(['employee_id' => 'nullable|exists:employees,id']);
        $mdm->update(['local_employee_id' => $request->employee_id ?: null]);

        return back()->with('success', $request->employee_id
            ? 'Device linked to employee.'
            : 'Employee link removed.');
    }

    // ── GPS Map ───────────────────────────────────────────────────────────────
    public function map(Request $request)
    {
        $q = MdmDevice::with(['employee.client', 'locationLatest', 'hardware'])
            ->where(function ($q) {
                // GPS in main device row OR in the normalized location_latest table
                $q->where(function ($inner) {
                    $inner->whereNotNull('latitude')->where('latitude', '!=', 0);
                })->orWhereHas('locationLatest', function ($inner) {
                    $inner->whereNotNull('latitude')->where('latitude', '!=', 0);
                });
            });

        if ($request->filled('status'))        $q->where('device_status', $request->status);
        if ($request->filled('group'))         $q->where('mdm_group', $request->group);
        if ($request->filled('configuration')) $q->where('configuration', $request->configuration);
        if ($request->filled('linked')) {
            $request->linked === 'yes'
                ? $q->whereNotNull('local_employee_id')
                : $q->whereNull('local_employee_id');
        }
        if ($request->filled('client')) {
            $clientId = $request->client;
            $q->whereHas('employee', fn ($eq) => $eq->where('client_id', $clientId));
        }

        $devices = $q->orderByDesc('sync_time')->limit(5000)->get();

        $mapData = $devices->map(fn ($d) => [
            'id'       => $d->id,
            'number'   => $d->pg_number,
            'model'    => $d->model,
            'serial'   => $d->serial_number,
            'imei'     => $d->imei,
            'status'   => $d->device_status,
            'online'   => $d->isOnline(),
            'group'    => $d->mdm_group,
            'config'   => $d->configuration ?: null,
            'battery'  => $d->batteryLevel(),
            'android'  => $d->android_version ?: null,
            'ip'       => $d->ip_address,
            'sync_age' => $d->syncAgeLabel(),
            'sync_ts'  => $d->sync_time?->format('d M Y, H:i'),
            'lat'      => (float) ($d->locationLatest?->latitude ?? $d->latitude),
            'lng'      => (float) ($d->locationLatest?->longitude ?? $d->longitude),
            'url'      => route('mdm.show', $d),
            'employee' => $d->employee ? [
                'name'  => $d->employee->name,
                'code'  => $d->employee->employee_code,
                'desig' => $d->employee->designation,
                'phone' => $d->employee->phone,
            ] : null,
            'client' => $d->employee?->client ? [
                'id'   => $d->employee->client->id,
                'name' => $d->employee->client->name,
            ] : null,
        ])->filter(fn ($d) => $d['lat'] && $d['lng'])->values();

        $groups  = MdmDevice::whereNotNull('mdm_group')->distinct()->orderBy('mdm_group')->pluck('mdm_group');
        $configs = $mapData->pluck('config')->filter()->unique()->sort()->values();
        $clients = $mapData->pluck('client')->filter()->unique('id')->sortBy('name')->values();
        $total   = $mapData->count();
        $online  = $devices->filter(fn ($d) => $d->isOnline())->count();

        return view('mdm.map', compact('mapData', 'groups', 'configs', 'clients', 'total', 'online'));
    }

    // ── Internal helpers ──────────────────────────────────────────────────────
    private function runAutoMatchInternal(): int
    {
        $matched = 0;

        MdmDevice::whereNull('local_device_id')->each(function ($mdm) use (&$matched) {
            $device = null;
            if ($mdm->imei) {
                $device = \App\Models\Device::where('imei1', $mdm->imei)
                    ->orWhere('imei2', $mdm->imei)->first();
            }
            if (! $device && $mdm->serial_number) {
                $device = \App\Models\Device::where('serial_number', $mdm->serial_number)->first();
            }
            if ($device) {
                $update = ['local_device_id' => $device->id];
                if (! $mdm->local_employee_id && $device->current_employee_id) {
                    $update['local_employee_id'] = $device->current_employee_id;
                }
                $mdm->update($update);
                $matched++;
            }
        });

        return $matched;
    }
}
