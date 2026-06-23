<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Employee;
use App\Models\MdmPortalDevice;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientPortalController extends Controller
{
    private function clientId(): int
    {
        return (int) request()->user()->client_id;
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────
    public function dashboard()
    {
        $clientId = $this->clientId();

        $totalEmployees = Employee::where('client_id', $clientId)->count();

        // Devices via employees
        $empIds = Employee::where('client_id', $clientId)->pluck('id');

        $totalDevices  = Device::whereIn('current_employee_id', $empIds)->count();
        $activeDevices = Device::whereIn('current_employee_id', $empIds)
            ->where('lifecycle_status', 'activated')->count();
        $inRepair      = Device::whereIn('current_employee_id', $empIds)
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
        $lifecycleStats = Device::whereIn('current_employee_id', $empIds)
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

        $query = Device::with(['model.brand', 'currentEmployee', 'mdmPortalDevice'])
            ->whereIn('current_employee_id', $empIds);

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

        $statusList = Device::whereIn('current_employee_id', $empIds)
            ->distinct()->pluck('lifecycle_status');

        return view('client-portal.devices', compact('devices', 'statusList'));
    }

    // ── Device Detail ─────────────────────────────────────────────────────────
    public function show(Device $device)
    {
        $clientId = $this->clientId();
        $empIds   = Employee::where('client_id', $clientId)->pluck('id');

        // Ensure device belongs to this client
        if (!in_array($device->current_employee_id, $empIds->toArray())) {
            abort(403, 'Device does not belong to your account.');
        }

        $device->load(['model.brand', 'currentEmployee', 'mdmDevice', 'handovers.employee', 'tickets']);

        return view('client-portal.show', compact('device'));
    }

    // ── Employees ─────────────────────────────────────────────────────────────
    public function employees(Request $request)
    {
        $clientId = $this->clientId();

        $query = Employee::with(['mdmDevice'])
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
}
