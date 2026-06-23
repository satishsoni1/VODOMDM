<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\MdmImportLog;
use App\Models\MdmPortalDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MdmController extends Controller
{
    // ── Analytics Dashboard ──────────────────────────────────────────────────
    public function index()
    {
        $total   = MdmPortalDevice::count();
        $online  = MdmPortalDevice::where('device_status', 'on')->count();
        $offline = MdmPortalDevice::where('device_status', 'off')->count();

        $compliant     = MdmPortalDevice::where('permission_status', 'LIKE', '%All permissions are granted%')->count();
        $mdmModeOn     = MdmPortalDevice::where('mdm_mode', true)->count();
        $kioskModeOn   = MdmPortalDevice::where('kiosk_mode', true)->count();
        $linked        = MdmPortalDevice::whereNotNull('employee_id')->count();
        $unlinked      = $total - $linked;

        // Sync freshness buckets
        $now = now();
        $syncFreshness = [
            'fresh'   => MdmPortalDevice::where('sync_time', '>=', $now->copy()->subHour())->count(),
            'today'   => MdmPortalDevice::whereBetween('sync_time', [$now->copy()->subDay(), $now->copy()->subHour()])->count(),
            'week'    => MdmPortalDevice::whereBetween('sync_time', [$now->copy()->subWeek(), $now->copy()->subDay()])->count(),
            'stale'   => MdmPortalDevice::where('sync_time', '<', $now->copy()->subWeek())->orWhereNull('sync_time')->count(),
        ];

        // Android version breakdown
        $androidVersions = MdmPortalDevice::select('android_version', DB::raw('count(*) as cnt'))
            ->whereNotNull('android_version')
            ->groupBy('android_version')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'android_version');

        // Model distribution
        $models = MdmPortalDevice::select('model', DB::raw('count(*) as cnt'))
            ->whereNotNull('model')
            ->groupBy('model')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'model');

        // Group distribution
        $groups = MdmPortalDevice::select('mdm_group', DB::raw('count(*) as cnt'))
            ->whereNotNull('mdm_group')
            ->groupBy('mdm_group')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'mdm_group');

        // Enrollment by month (last 12 months)
        $enrollmentByMonth = MdmPortalDevice::select(
                DB::raw("DATE_FORMAT(enrollment_date, '%Y-%m') as month"),
                DB::raw('count(*) as cnt')
            )
            ->whereNotNull('enrollment_date')
            ->where('enrollment_date', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('cnt', 'month');

        // Designation-wise compliance
        $designationStats = MdmPortalDevice::join('employees', 'mdm_portal_devices.employee_id', '=', 'employees.id')
            ->select(
                'employees.designation',
                DB::raw('count(*) as total'),
                DB::raw("sum(case when mdm_portal_devices.device_status='on' then 1 else 0 end) as online"),
                DB::raw("sum(case when mdm_portal_devices.permission_status LIKE '%All permissions%' then 1 else 0 end) as compliant")
            )
            ->groupBy('employees.designation')
            ->orderByDesc('total')
            ->get();

        // Devices needing attention
        $attention = MdmPortalDevice::with('employee')
            ->where(function ($q) use ($now) {
                $q->where('device_status', 'off')
                  ->orWhere('permission_status', 'NOT LIKE', '%All permissions are granted%')
                  ->orWhere('sync_time', '<', $now->copy()->subDay())
                  ->orWhereNull('sync_time');
            })
            ->latest('sync_time')
            ->limit(10)
            ->get();

        // Recent sync activity
        $recentSyncs = MdmPortalDevice::with('employee')
            ->whereNotNull('sync_time')
            ->orderByDesc('sync_time')
            ->limit(8)
            ->get();

        return view('mdm.index', compact(
            'total', 'online', 'offline', 'compliant', 'mdmModeOn', 'kioskModeOn',
            'linked', 'unlinked', 'syncFreshness', 'androidVersions', 'models',
            'groups', 'enrollmentByMonth', 'designationStats', 'attention', 'recentSyncs'
        ));
    }

    // ── Device List ──────────────────────────────────────────────────────────
    public function devices(Request $request)
    {
        $query = MdmPortalDevice::with(['employee', 'device']);

        if ($request->filled('status'))   $query->where('device_status', $request->status);
        if ($request->filled('group'))    $query->where('mdm_group', $request->group);
        if ($request->filled('model'))    $query->where('model', $request->model);
        if ($request->filled('linked'))   $request->linked === 'yes'
            ? $query->whereNotNull('employee_id')
            : $query->whereNull('employee_id');
        if ($request->filled('q'))        $query->where(function ($q) use ($request) {
            $q->where('mdm_number', 'LIKE', "%{$request->q}%")
              ->orWhere('serial_number', 'LIKE', "%{$request->q}%")
              ->orWhere('imei', 'LIKE', "%{$request->q}%")
              ->orWhere('model', 'LIKE', "%{$request->q}%");
        });

        $devices = $query->orderByDesc('sync_time')->paginate(25)->withQueryString();

        $groups  = MdmPortalDevice::whereNotNull('mdm_group')->distinct()->pluck('mdm_group');
        $modelList = MdmPortalDevice::whereNotNull('model')->distinct()->pluck('model');

        $stats = [
            'total'   => MdmPortalDevice::count(),
            'online'  => MdmPortalDevice::where('device_status', 'on')->count(),
            'offline' => MdmPortalDevice::where('device_status', 'off')->count(),
            'linked'  => MdmPortalDevice::whereNotNull('employee_id')->count(),
        ];

        return view('mdm.devices', compact('devices', 'groups', 'modelList', 'stats'));
    }

    // ── Employee-Device (Designation-wise) ───────────────────────────────────
    public function employees(Request $request)
    {
        $empQuery = Employee::with(['mdmDevice', 'project', 'client'])
            ->withCount('mdmDevice as has_device')
            ->orderBy('designation')
            ->orderBy('name');

        if ($request->filled('designation')) $empQuery->where('designation', $request->designation);
        if ($request->filled('client_id'))   $empQuery->where('client_id', $request->client_id);
        if ($request->filled('linked'))      $request->linked === 'yes'
            ? $empQuery->has('mdmDevice')
            : $empQuery->doesntHave('mdmDevice');

        $employees = $empQuery->paginate(30)->withQueryString();

        $designations = Employee::distinct()->orderBy('designation')->pluck('designation');
        $clients      = \App\Models\Client::orderBy('name')->get();

        // Summary by designation — LEFT JOIN avoids ONLY_FULL_GROUP_BY issues from withCount()
        $designationSummary = Employee::select(
                'employees.designation',
                DB::raw('COUNT(employees.id) as total'),
                DB::raw('COUNT(mdm_portal_devices.id) as device_count')
            )
            ->leftJoin('mdm_portal_devices', 'mdm_portal_devices.employee_id', '=', 'employees.id')
            ->groupBy('employees.designation')
            ->orderBy('employees.designation')
            ->get();

        return view('mdm.employees', compact('employees', 'designations', 'clients', 'designationSummary'));
    }

    // ── Device Detail ────────────────────────────────────────────────────────
    public function show(MdmPortalDevice $mdm)
    {
        $mdm->load(['employee.client', 'device.model.brand']);
        $employees = Employee::with('client')->orderBy('name')->get();
        $apps      = $mdm->parsedApps();
        return view('mdm.show', compact('mdm', 'employees', 'apps'));
    }

    // ── Import Form + Log ─────────────────────────────────────────────────────
    public function import()
    {
        $logs = MdmImportLog::with('importedBy')->orderByDesc('created_at')->paginate(15);
        $lastLog = MdmImportLog::latest()->first();
        $totalDevices = MdmPortalDevice::count();
        return view('mdm.import', compact('logs', 'lastLog', 'totalDevices'));
    }

    // ── Process CSV Import with Logging ───────────────────────────────────────
    public function processImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:20480',
        ]);

        $file   = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            MdmImportLog::create([
                'imported_by' => Auth::id(),
                'filename'    => $file->getClientOriginalName(),
                'status'      => 'failed',
                'notes'       => 'Could not open uploaded file.',
            ]);
            return back()->with('error', 'Could not open file.');
        }

        // Skip header row
        fgetcsv($handle);

        $imported = 0;
        $updated  = 0;
        $skipped  = 0;
        $total    = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $total++;
            if (empty(trim($row[0] ?? ''))) { $skipped++; continue; }
            try {
                $data = MdmPortalDevice::fromCsvRow($row);
                $exists = MdmPortalDevice::where('mdm_number', $data['mdm_number'])->exists();
                MdmPortalDevice::updateOrCreate(['mdm_number' => $data['mdm_number']], $data);
                $exists ? $updated++ : $imported++;
            } catch (\Throwable) {
                $skipped++;
            }
        }

        fclose($handle);

        $matched = $this->runAutoMatch();

        MdmImportLog::create([
            'imported_by'  => Auth::id(),
            'filename'     => $file->getClientOriginalName(),
            'total_rows'   => $total,
            'imported'     => $imported,
            'updated'      => $updated,
            'skipped'      => $skipped,
            'auto_matched' => $matched,
            'status'       => 'completed',
            'notes'        => "New: {$imported} | Updated: {$updated} | Skipped: {$skipped} | IMEI matched: {$matched}",
        ]);

        return redirect()->route('mdm.import')
            ->with('success', "Import complete — {$imported} new, {$updated} updated, {$skipped} skipped. Auto-matched {$matched} devices.");
    }

    // ── Link Device to Employee ───────────────────────────────────────────────
    public function linkEmployee(Request $request, MdmPortalDevice $mdm)
    {
        $request->validate(['employee_id' => 'nullable|exists:employees,id']);
        $mdm->update(['employee_id' => $request->employee_id ?: null]);
        return back()->with('success', $request->employee_id
            ? 'MDM device linked to employee.'
            : 'Employee link removed.');
    }

    // ── Auto-match by IMEI / Serial ───────────────────────────────────────────
    public function autoMatch()
    {
        $matched = $this->runAutoMatch();
        return back()->with('success', "Auto-matched {$matched} devices by IMEI / Serial Number.");
    }

    private function runAutoMatch(): int
    {
        $matched = 0;
        MdmPortalDevice::whereNull('device_id')->each(function ($mdm) use (&$matched) {
            $device = null;
            if ($mdm->imei) {
                $device = \App\Models\Device::where('imei1', $mdm->imei)
                    ->orWhere('imei2', $mdm->imei)->first();
            }
            if (!$device && $mdm->serial_number) {
                $device = \App\Models\Device::where('serial_number', $mdm->serial_number)->first();
            }
            if ($device) {
                $update = ['device_id' => $device->id];
                if (!$mdm->employee_id && $device->current_employee_id) {
                    $update['employee_id'] = $device->current_employee_id;
                }
                $mdm->update($update);
                $matched++;
            }
        });
        return $matched;
    }

    // ── Device Map ────────────────────────────────────────────────────────────
    public function map(Request $request)
    {
        $query = MdmPortalDevice::with('employee')
            ->whereNotNull('employee_id');

        if ($request->filled('state')) {
            $query->whereHas('employee', fn ($q) => $q->where('state', $request->state));
        }
        if ($request->filled('city')) {
            $query->whereHas('employee', fn ($q) => $q->where('city', $request->city));
        }
        if ($request->filled('status')) {
            $query->where('device_status', $request->status);
        }
        if ($request->filled('group')) {
            $query->where('mdm_group', $request->group);
        }

        $devices = $query->get();

        $mapData = $devices->map(fn ($d) => [
            'id'         => $d->id,
            'mdm_number' => $d->mdm_number,
            'model'      => $d->model,
            'serial'     => $d->serial_number,
            'status'     => $d->device_status,
            'online'     => $d->isOnline(),
            'compliant'  => $d->isPermissionCompliant(),
            'sync_age'   => $d->syncAgeLabel(),
            'freshness'  => $d->syncFreshnessClass(),
            'group'      => $d->mdm_group,
            'ip'         => $d->ip_address,
            'lat'        => $d->latitude  ? (float) $d->latitude  : null,
            'lng'        => $d->longitude ? (float) $d->longitude : null,
            'location'   => $d->location_raw,
            'employee'   => $d->employee ? [
                'name'  => $d->employee->name,
                'code'  => $d->employee->employee_code,
                'city'  => $d->employee->city,
                'state' => $d->employee->state,
                'phone' => $d->employee->phone,
                'desig' => $d->employee->designation,
            ] : null,
        ])->values();

        $states = Employee::whereHas('mdmDevice')
            ->whereNotNull('state')->distinct()->orderBy('state')->pluck('state');

        $cities = Employee::whereHas('mdmDevice')
            ->when($request->filled('state'), fn ($q) => $q->where('state', $request->state))
            ->whereNotNull('city')->distinct()->orderBy('city')->pluck('city');

        $groups = MdmPortalDevice::whereNotNull('mdm_group')
            ->distinct()->orderBy('mdm_group')->pluck('mdm_group');

        $total   = $devices->count();
        $online  = $devices->filter(fn ($d) => $d->isOnline())->count();
        $located = $devices->filter(fn ($d) => $d->latitude !== null)->count();

        return view('mdm.map', compact(
            'mapData', 'states', 'cities', 'groups',
            'total', 'online', 'located'
        ));
    }
}
