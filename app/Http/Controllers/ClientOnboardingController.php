<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Device;
use App\Models\Employee;
use App\Services\DeviceAssignmentService;
use Illuminate\Http\Request;

class ClientOnboardingController extends Controller
{
    public function start()
    {
        return view('onboarding.start');
    }

    public function storeClient(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'required|string|unique:clients|max:50',
            'industry'       => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string|max:100',
            'state'          => 'nullable|string|max:100',
            'pincode'        => 'nullable|string|max:10',
            'gstin'          => 'nullable|string|max:20',
            'notes'          => 'nullable|string',
        ]);

        $client = Client::create($validated);

        return redirect()->route('onboarding.employees', $client)
            ->with('success', "Client \"{$client->name}\" created. Now add employees.");
    }

    public function employees(Client $client)
    {
        $client->load(['projects', 'employees' => fn ($q) => $q->orderBy('name')]);

        return view('onboarding.employees', compact('client'));
    }

    public function storeEmployee(Request $request, Client $client)
    {
        $validated = $request->validate([
            'employee_code'     => 'required|string|max:50|unique:employees',
            'name'               => 'required|string|max:255',
            'email'              => 'nullable|email|max:255',
            'phone'              => 'required|string|max:20',
            'designation'        => 'nullable|string|max:150',
            'client_project_id'  => 'nullable|exists:client_projects,id',
        ]);

        $client->employees()->create($validated + ['status' => 'active']);

        return redirect()->route('onboarding.employees', $client)
            ->with('success', 'Employee added.');
    }

    public function devices(Client $client)
    {
        $client->load(['employees' => fn ($q) => $q->orderBy('name'), 'employees.currentDevices']);
        $availableDevices = Device::with('model.brand')
            ->whereNull('current_employee_id')
            ->orderBy('asset_tag')
            ->get();

        return view('onboarding.devices', compact('client', 'availableDevices'));
    }

    public function assignDevices(Request $request, Client $client)
    {
        $validated = $request->validate([
            'employee_id'   => 'array',
            'employee_id.*' => 'nullable|exists:employees,id',
            'device_id'     => 'array',
            'device_id.*'   => 'nullable|exists:devices,id',
            'group'         => 'array',
            'group.*'       => 'nullable|string|max:150',
        ]);

        $assignedCount = 0;
        $skippedCount  = 0;

        foreach ($validated['employee_id'] ?? [] as $index => $employeeId) {
            $deviceId = $validated['device_id'][$index] ?? null;
            if (!$employeeId || !$deviceId) {
                continue;
            }

            $employee = Employee::find($employeeId);
            $device   = Device::whereNull('current_employee_id')->find($deviceId);

            if (!$employee || !$device) {
                $skippedCount++;
                continue;
            }

            DeviceAssignmentService::assign($device, $employee, [
                'client_id'       => $client->id,
                'handed_over_by'  => auth()->id(),
                'group'           => trim($validated['group'][$index] ?? '') ?: null,
                'transfer_reason' => 'Client onboarding',
            ]);

            $assignedCount++;
        }

        return redirect()->route('onboarding.finish', $client)
            ->with('assign_summary', ['assigned' => $assignedCount, 'skipped' => $skippedCount]);
    }

    public function finish(Client $client)
    {
        $client->loadCount(['employees', 'devices']);

        return view('onboarding.finish', compact('client'));
    }
}
