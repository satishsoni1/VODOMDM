<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceLinkRequest;
use App\Models\Employee;
use Illuminate\Http\Request;

class DeviceScanController extends Controller
{
    public function show(Device $device)
    {
        $device->load([
            'model.brand', 'client', 'currentEmployee',
            'handovers' => fn ($q) => $q->with('employee')->orderBy('handover_date', 'desc')->limit(10),
        ]);

        $pendingRequest = $device->linkRequests()->where('status', 'pending')->latest()->first();

        return view('scan.show', compact('device', 'pendingRequest'));
    }

    public function lookupEmployee(Request $request, Device $device)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:100',
        ]);

        $employee = Employee::where('employee_code', $validated['employee_code'])->first();

        if (! $employee) {
            return response()->json(['found' => false, 'message' => 'No employee found with that code.'], 404);
        }

        return response()->json([
            'found' => true,
            'employee' => [
                'name' => $employee->name,
                'designation' => $employee->designation,
                'department' => $employee->department,
                'client' => $employee->client?->name,
            ],
        ]);
    }

    public function requestLink(Request $request, Device $device)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:100',
        ]);

        if ($device->current_employee_id) {
            return response()->json(['message' => 'This device is already assigned to someone.'], 422);
        }

        if ($device->linkRequests()->where('status', 'pending')->exists()) {
            return response()->json(['message' => 'A link request for this device is already pending approval.'], 422);
        }

        $employee = Employee::where('employee_code', $validated['employee_code'])->first();

        if (! $employee) {
            return response()->json(['message' => 'No employee found with that code.'], 404);
        }

        DeviceLinkRequest::create([
            'device_id' => $device->id,
            'employee_id' => $employee->id,
            'employee_code_entered' => $validated['employee_code'],
            'status' => 'pending',
            'requested_ip' => $request->ip(),
            'requested_user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return response()->json(['message' => 'Your request has been submitted for admin approval.']);
    }
}
