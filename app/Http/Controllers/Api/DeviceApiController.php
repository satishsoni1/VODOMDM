<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\MdmSyncLog;
use Illuminate\Http\Request;

class DeviceApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Device::with(['model.brand', 'currentEmployee', 'client'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('q')) {
            $like = '%' . $request->q . '%';
            $query->where(function ($sub) use ($like) {
                $sub->where('serial_number', 'like', $like)
                    ->orWhere('asset_tag', 'like', $like)
                    ->orWhere('imei1', 'like', $like)
                    ->orWhere('imei2', 'like', $like);
            });
        }

        return response()->json($query->paginate(25));
    }

    public function show(Device $device)
    {
        $device->load([
            'model.brand', 'vendor', 'client', 'currentEmployee',
            'latestMdmSync', 'mdmEnrollment', 'events' => fn ($q) => $q->limit(20),
        ]);

        return response()->json($device);
    }

    public function timeline(Device $device)
    {
        $events = $device->events()->orderBy('event_at', 'desc')->get();

        return response()->json($events);
    }

    public function updateStatus(Request $request, Device $device)
    {
        $validated = $request->validate([
            'status'  => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $old = $device->lifecycle_status;
        $device->update(['lifecycle_status' => $validated['status']]);

        $device->events()->create([
            'user_id'     => auth()->id(),
            'event_type'  => 'status_changed',
            'from_status' => $old,
            'to_status'   => $validated['status'],
            'description' => $validated['remarks'] ?? 'Status updated via API',
            'ip_address'  => $request->ip(),
            'event_at'    => now(),
        ]);

        return response()->json(['message' => 'Status updated', 'device' => $device->refresh()]);
    }

    public function mdmSync(Request $request)
    {
        $data = $request->validate([
            'serial_number' => 'required|string',
            'battery_level' => 'nullable|string',
            'os_version'    => 'nullable|string',
            'sim_operator'  => 'nullable|string',
            'sim_number'    => 'nullable|string',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'is_rooted'     => 'nullable|boolean',
            'sim_changed'   => 'nullable|boolean',
        ]);

        $device = Device::where('serial_number', $data['serial_number'])->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        MdmSyncLog::create(array_merge($data, [
            'device_id' => $device->id,
            'synced_at' => now(),
        ]));

        if (!empty($data['is_rooted']) || !empty($data['sim_changed'])) {
            $device->events()->create([
                'event_type'  => !empty($data['is_rooted']) ? 'rooted_detected' : 'sim_changed',
                'description' => !empty($data['is_rooted']) ? 'Rooted device detected via MDM sync' : 'SIM change detected',
                'event_at'    => now(),
            ]);
        }

        return response()->json(['message' => 'Sync recorded']);
    }
}
