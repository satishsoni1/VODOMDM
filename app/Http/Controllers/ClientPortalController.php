<?php

namespace App\Http\Controllers;

use App\Models\ClientMdmConfiguration;
use App\Models\Device;
use App\Models\Employee;
use App\Models\MdmDevice;
use App\Models\MdmPortalDevice;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ClientPortalController extends Controller
{
    private function clientId(): int
    {
        return (int) request()->user()->client_id;
    }

    private function assertBelongsToClient(Device $device): void
    {
        $clientId = $this->clientId();
        $empIds   = Employee::where('client_id', $clientId)->pluck('id');

        $belongsToClient = $device->client_id === $clientId
            || in_array($device->current_employee_id, $empIds->toArray());
        if (!$belongsToClient) {
            abort(403, 'Device does not belong to your account.');
        }
    }

    private function assignedConfigurations(): array
    {
        return ClientMdmConfiguration::where('client_id', $this->clientId())
            ->pluck('configuration')->toArray();
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────
    public function dashboard()
    {
        $clientId = $this->clientId();

        $totalEmployees = Employee::where('client_id', $clientId)->count();

        // Devices via employees or tagged directly to this client
        $empIds = Employee::where('client_id', $clientId)->pluck('id');
        $clientDevices = fn ($q) => $q->where('client_id', $clientId)->orWhereIn('current_employee_id', $empIds);

        $totalDevices  = Device::where($clientDevices)->count();
        $activeDevices = Device::where($clientDevices)
            ->where('lifecycle_status', 'activated')->count();
        $inRepair      = Device::where($clientDevices)
            ->whereIn('lifecycle_status', ['under_repair', 'awaiting_parts'])->count();

        // MDM status for linked employees
        $mdmOnline  = MdmPortalDevice::whereIn('employee_id', $empIds)
            ->where('device_status', 'on')->count();
        $mdmOffline = MdmPortalDevice::whereIn('employee_id', $empIds)
            ->where('device_status', 'off')->count();
        $mdmTotal   = MdmPortalDevice::whereIn('employee_id', $empIds)->count();
        $mdmLinked  = $mdmTotal;

        // Open tickets
        $openTickets = Ticket::where('client_id', $clientId)
            ->whereIn('status', ['open', 'in_progress'])->count();

        // Lifecycle distribution
        $lifecycleStats = Device::where($clientDevices)
            ->select('lifecycle_status', DB::raw('count(*) as cnt'))
            ->groupBy('lifecycle_status')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'lifecycle_status');

        // MDM sync freshness
        $now = now();
        $mdmFresh   = MdmPortalDevice::whereIn('employee_id', $empIds)
            ->where('sync_time', '>=', $now->copy()->subHour())->count();
        $mdmToday   = MdmPortalDevice::whereIn('employee_id', $empIds)
            ->whereBetween('sync_time', [$now->copy()->subDay(), $now->copy()->subHour()])->count();
        $mdmStale   = MdmPortalDevice::whereIn('employee_id', $empIds)
            ->where(function ($q) use ($now) {
                $q->where('sync_time', '<', $now->copy()->subDay())->orWhereNull('sync_time');
            })->count();

        // Recent devices (last synced MDM)
        $recentMdm = MdmPortalDevice::whereIn('employee_id', $empIds)
            ->with('employee')
            ->whereNotNull('sync_time')
            ->orderByDesc('sync_time')
            ->limit(8)->get();

        // Devices needing attention (offline / non-compliant)
        $attentionDevices = MdmPortalDevice::whereIn('employee_id', $empIds)
            ->where(function ($q) use ($now) {
                $q->where('device_status', 'off')
                  ->orWhere('permission_status', 'NOT LIKE', '%All permissions are granted%')
                  ->orWhere('sync_time', '<', $now->copy()->subDay())
                  ->orWhereNull('sync_time');
            })
            ->with('employee')
            ->limit(10)->get();

        return view('client-portal.dashboard', compact(
            'totalEmployees', 'totalDevices', 'activeDevices', 'inRepair',
            'mdmOnline', 'mdmOffline', 'mdmTotal', 'mdmLinked',
            'openTickets', 'lifecycleStats',
            'mdmFresh', 'mdmToday', 'mdmStale',
            'recentMdm', 'attentionDevices'
        ));
    }

    // ── Device List ───────────────────────────────────────────────────────────
    public function devices(Request $request)
    {
        $clientId = $this->clientId();
        $empIds   = Employee::where('client_id', $clientId)->pluck('id');

        $query = Device::with(['model.brand', 'currentEmployee', 'mdmDevice'])
            ->where(fn ($q) => $q->where('client_id', $clientId)->orWhereIn('current_employee_id', $empIds));

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('serial_number', 'LIKE', "%{$q}%")
                   ->orWhere('imei1', 'LIKE', "%{$q}%")
                   ->orWhereHas('currentEmployee', fn ($e) => $e->where('name', 'LIKE', "%{$q}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('lifecycle_status', $request->status);
        }

        if ($request->filled('mdm_status')) {
            $online = $request->mdm_status === 'online';
            $query->whereHas('mdmDevice', fn ($m) => $m->where('device_status', $online ? 'on' : 'off'));
        }

        $devices = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        $statusList = Device::where(fn ($q) => $q->where('client_id', $clientId)->orWhereIn('current_employee_id', $empIds))
            ->distinct()->pluck('lifecycle_status');

        return view('client-portal.devices', compact('devices', 'statusList'));
    }

    // ── Device Detail ─────────────────────────────────────────────────────────
    public function show(Device $device)
    {
        $this->assertBelongsToClient($device);

        $device->load(['model.brand', 'currentEmployee', 'mdmDevice.hardware', 'handovers.employee', 'tickets']);

        return view('client-portal.show', compact('device'));
    }

    // ── Device QR Code ────────────────────────────────────────────────────────
    public function qrCode(Device $device)
    {
        $this->assertBelongsToClient($device);

        $url = route('scan.show', ['device' => $device->qr_token]);
        $svg = QrCode::format('svg')->size(400)->margin(1)->generate($url);

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }

    // ── Employees ─────────────────────────────────────────────────────────────
    public function employees(Request $request)
    {
        $clientId = $this->clientId();

        $query = Employee::with(['mdmDevice', 'currentDevices'])
            ->where('client_id', $clientId)
            ->orderBy('designation')->orderBy('name');

        if ($request->filled('designation')) {
            $query->where('designation', $request->designation);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'LIKE', "%{$q}%")
                   ->orWhere('employee_code', 'LIKE', "%{$q}%");
            });
        }

        $employees    = $query->paginate(25)->withQueryString();
        $designations = Employee::where('client_id', $clientId)->distinct()->orderBy('designation')->pluck('designation');

        return view('client-portal.employees', compact('employees', 'designations'));
    }

    // ── Tickets ───────────────────────────────────────────────────────────────
    public function tickets()
    {
        $clientId = $this->clientId();

        $tickets = Ticket::with(['device.model', 'raisedBy'])
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        return view('client-portal.tickets', compact('tickets'));
    }

    // ── MDM Device Map (scoped to assigned configurations) ──────────────────────
    public function mdmMap(Request $request)
    {
        $configs = $this->assignedConfigurations();

        $q = MdmDevice::with(['employee', 'locationLatest', 'hardware'])
            ->whereIn('configuration', $configs)
            ->where(function ($q) {
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
            'url'      => route('client.mdm-devices.show', $d),
            'employee' => $d->employee ? [
                'name'  => $d->employee->name,
                'code'  => $d->employee->employee_code,
                'desig' => $d->employee->designation,
                'phone' => $d->employee->phone,
            ] : null,
        ])->filter(fn ($d) => $d['lat'] && $d['lng'])->values();

        $groups = MdmDevice::whereIn('configuration', $configs)
            ->whereNotNull('mdm_group')->distinct()->orderBy('mdm_group')->pluck('mdm_group');
        $total  = $mapData->count();
        $online = $devices->filter(fn ($d) => $d->isOnline())->count();

        return view('client-portal.mdm-map', compact('mapData', 'groups', 'configs', 'total', 'online'));
    }

    // ── MDM Device List (scoped to assigned configurations) ─────────────────────
    public function mdmDevices(Request $request)
    {
        $configs = $this->assignedConfigurations();

        $q = MdmDevice::with(['employee', 'hardware', 'locationLatest'])
            ->whereIn('configuration', $configs);

        if ($request->filled('status')) $q->where('device_status', $request->status);
        if ($request->filled('group'))  $q->where('mdm_group', $request->group);
        if ($request->filled('q')) {
            $search = $request->q;
            $q->where(function ($sub) use ($search) {
                $sub->where('pg_number', 'LIKE', "%{$search}%")
                    ->orWhere('imei', 'LIKE', "%{$search}%")
                    ->orWhere('serial_number', 'LIKE', "%{$search}%")
                    ->orWhere('model', 'LIKE', "%{$search}%");
            });
        }

        $devices = $q->orderByDesc('sync_time')->paginate(20)->withQueryString();
        $groups  = MdmDevice::whereIn('configuration', $configs)
            ->whereNotNull('mdm_group')->distinct()->orderBy('mdm_group')->pluck('mdm_group');

        return view('client-portal.mdm-devices', compact('devices', 'groups', 'configs'));
    }

    // ── MDM Device Detail (scoped to assigned configurations) ───────────────────
    public function mdmShow(MdmDevice $mdm)
    {
        if (! in_array($mdm->configuration, $this->assignedConfigurations())) {
            abort(403, 'Device does not belong to your account.');
        }

        $mdm->load(['hardware', 'locationLatest', 'employee']);

        return view('client-portal.mdm-show', compact('mdm'));
    }
}
