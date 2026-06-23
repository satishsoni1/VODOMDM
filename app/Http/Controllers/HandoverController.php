<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Device;
use App\Models\DeviceHandover;
use App\Models\DispatchBatch;
use App\Models\Employee;
use App\Models\OwnershipHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HandoverController extends Controller
{
    public function index(Request $request)
    {
        $query = DeviceHandover::with(['device.model.brand', 'employee', 'client', 'handedOverBy'])->latest('handover_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $handovers = $query->paginate(20)->withQueryString();
        $clients   = Client::where('status', 'active')->orderBy('name')->get();

        return view('handovers.index', compact('handovers', 'clients'));
    }

    public function create(Request $request)
    {
        $clients   = Client::with('projects')->where('status', 'active')->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        $devices   = Device::with('model.brand')
            ->whereIn('lifecycle_status', ['in_stock', 'delivered', 'dispatched', 'qc_done', 'configured'])
            ->orderBy('asset_tag')->get();
        $batches   = DispatchBatch::with('client')->whereIn('status', ['in_transit', 'delivered'])->latest()->get();

        return view('handovers.create', compact('clients', 'employees', 'devices', 'batches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id'             => 'required|exists:devices,id',
            'employee_id'           => 'required|exists:employees,id',
            'client_id'             => 'required|exists:clients,id',
            'client_project_id'     => 'nullable|exists:client_projects,id',
            'dispatch_batch_id'     => 'nullable|exists:dispatch_batches,id',
            'handover_date'         => 'required|date',
            'handover_location'     => 'nullable|string|max:200',
            'handover_city'         => 'nullable|string|max:100',
            'condition_at_handover' => 'required|in:new,good,fair,poor',
            'accessories_handed'    => 'nullable|string',
            'remarks'               => 'nullable|string',
        ]);

        $handover = DeviceHandover::create([
            'handover_number'       => 'HO-' . strtoupper(Str::random(8)),
            'device_id'             => $validated['device_id'],
            'employee_id'           => $validated['employee_id'],
            'client_id'             => $validated['client_id'],
            'client_project_id'     => $validated['client_project_id'] ?? null,
            'dispatch_batch_id'     => $validated['dispatch_batch_id'] ?? null,
            'handed_over_by'        => auth()->id(),
            'handover_date'         => $validated['handover_date'],
            'handover_location'     => $validated['handover_location'] ?? null,
            'handover_city'         => $validated['handover_city'] ?? null,
            'condition_at_handover' => $validated['condition_at_handover'],
            'accessories_handed'    => $validated['accessories_handed'] ?? null,
            'remarks'               => $validated['remarks'] ?? null,
            'status'                => 'assigned',
        ]);

        Device::find($validated['device_id'])->update([
            'lifecycle_status'    => 'assigned',
            'client_id'           => $validated['client_id'],
            'current_employee_id' => $validated['employee_id'],
        ]);

        OwnershipHistory::create([
            'device_id'       => $validated['device_id'],
            'employee_id'     => $validated['employee_id'],
            'client_id'       => $validated['client_id'],
            'ownership_type'  => 'employee',
            'from_date'       => now(),
            'transfer_reason' => 'Device handover',
            'transferred_by'  => auth()->id(),
        ]);

        return redirect()->route('handovers.show', $handover)
            ->with('success', 'Handover ' . $handover->handover_number . ' recorded.');
    }

    public function show(DeviceHandover $handover)
    {
        $handover->load(['device.model.brand', 'employee', 'client', 'project', 'handedOverBy', 'dispatchBatch']);

        return view('handovers.show', compact('handover'));
    }

    public function edit(DeviceHandover $handover)
    {
        return view('handovers.edit', compact('handover'));
    }

    public function update(Request $request, DeviceHandover $handover)
    {
        $request->validate([
            'status'  => 'required|in:assigned,activated,returned',
            'remarks' => 'nullable|string',
        ]);

        $data = ['status' => $request->status, 'remarks' => $request->remarks];

        if ($request->boolean('acknowledgement_received')) {
            $data['acknowledgement_received'] = true;
            $data['acknowledged_at']          = now();
        }

        if ($request->status === 'activated') {
            $handover->device?->update(['lifecycle_status' => 'active']);
        }

        $handover->update($data);

        return back()->with('success', 'Handover status updated.');
    }

    public function destroy(DeviceHandover $handover)
    {
        abort(403, 'Handover records cannot be deleted.');
    }
}
