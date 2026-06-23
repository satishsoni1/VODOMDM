<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CourierPartner;
use App\Models\Device;
use App\Models\DispatchBatch;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DispatchController extends Controller
{
    public function index(Request $request)
    {
        $query = DispatchBatch::with(['client', 'courierPartner', 'fromLocation', 'items'])
            ->latest('dispatch_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $batches = $query->paginate(20)->withQueryString();
        $clients = Client::where('status', 'active')->orderBy('name')->get();

        $stats = [
            'total'       => DispatchBatch::count(),
            'ready'       => DispatchBatch::where('status', 'ready')->count(),
            'in_transit'  => DispatchBatch::where('status', 'in_transit')->count(),
            'delivered'   => DispatchBatch::where('status', 'delivered')->count(),
        ];

        return view('dispatches.index', compact('batches', 'clients', 'stats'));
    }

    public function create(Request $request)
    {
        $clients        = Client::with('projects')->where('status', 'active')->orderBy('name')->get();
        $couriers       = CourierPartner::where('is_active', true)->orderBy('name')->get();
        $locations      = Location::orderBy('name')->get();
        $availableDevices = Device::with('model.brand')
            ->whereIn('lifecycle_status', ['in_stock', 'qc_done', 'configured'])
            ->orderBy('asset_tag')->get();

        $selectedClient = $request->filled('client_id') ? Client::with('projects')->find($request->client_id) : null;

        return view('dispatches.create', compact('clients', 'couriers', 'locations', 'availableDevices', 'selectedClient'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'               => 'required|exists:clients,id',
            'client_project_id'       => 'nullable|exists:client_projects,id',
            'courier_partner_id'      => 'nullable|exists:courier_partners,id',
            'from_location_id'        => 'required|exists:locations,id',
            'dispatch_date'           => 'required|date',
            'expected_delivery_date'  => 'nullable|date|after:dispatch_date',
            'awb_number'              => 'nullable|string|max:100',
            'tracking_number'         => 'nullable|string|max:100',
            'destination_address'     => 'nullable|string',
            'destination_city'        => 'nullable|string|max:100',
            'destination_state'       => 'nullable|string|max:100',
            'receiver_name'           => 'required|string|max:200',
            'receiver_phone'          => 'nullable|string|max:20',
            'freight_cost'            => 'nullable|numeric|min:0',
            'remarks'                 => 'nullable|string',
            'device_ids'              => 'required|array|min:1',
            'device_ids.*'            => 'exists:devices,id',
        ]);

        $batch = DispatchBatch::create([
            'dispatch_number'         => 'DISP-' . strtoupper(Str::random(8)),
            'client_id'               => $validated['client_id'],
            'client_project_id'       => $validated['client_project_id'] ?? null,
            'courier_partner_id'      => $validated['courier_partner_id'] ?? null,
            'from_location_id'        => $validated['from_location_id'],
            'dispatched_by'           => auth()->id(),
            'dispatch_date'           => $validated['dispatch_date'],
            'expected_delivery_date'  => $validated['expected_delivery_date'] ?? null,
            'awb_number'              => $validated['awb_number'] ?? null,
            'tracking_number'         => $validated['tracking_number'] ?? null,
            'destination_address'     => $validated['destination_address'] ?? null,
            'destination_city'        => $validated['destination_city'] ?? null,
            'destination_state'       => $validated['destination_state'] ?? null,
            'receiver_name'           => $validated['receiver_name'],
            'receiver_phone'          => $validated['receiver_phone'] ?? null,
            'freight_cost'            => $validated['freight_cost'] ?? null,
            'remarks'                 => $validated['remarks'] ?? null,
            'status'                  => 'in_transit',
        ]);

        foreach ($validated['device_ids'] as $deviceId) {
            $batch->items()->create([
                'device_id' => $deviceId,
                'status'    => 'dispatched',
            ]);
            Device::find($deviceId)->update([
                'lifecycle_status' => 'dispatched',
                'client_id'        => $validated['client_id'],
            ]);
        }

        return redirect()->route('dispatches.show', $batch)
            ->with('success', 'Dispatch batch ' . $batch->dispatch_number . ' created with ' . count($validated['device_ids']) . ' device(s).');
    }

    public function show(DispatchBatch $dispatch)
    {
        $dispatch->load(['client', 'project', 'courierPartner', 'fromLocation', 'dispatchedBy', 'items.device.model.brand']);

        return view('dispatches.show', compact('dispatch'));
    }

    public function edit(DispatchBatch $dispatch)
    {
        return view('dispatches.edit', compact('dispatch'));
    }

    public function update(Request $request, DispatchBatch $dispatch)
    {
        $request->validate([
            'status'                 => 'required|in:ready,in_transit,delivered,returned,lost',
            'actual_delivery_date'   => 'nullable|date',
            'awb_number'             => 'nullable|string|max:100',
            'tracking_number'        => 'nullable|string|max:100',
            'remarks'                => 'nullable|string',
        ]);

        $dispatch->update($request->only(['status', 'actual_delivery_date', 'awb_number', 'tracking_number', 'remarks']));

        if ($request->status === 'delivered') {
            $dispatch->items()->update(['status' => 'delivered']);
            foreach ($dispatch->items as $item) {
                Device::find($item->device_id)?->update(['lifecycle_status' => 'delivered']);
            }
        }

        return back()->with('success', 'Dispatch status updated to ' . ucfirst($request->status) . '.');
    }

    public function destroy(DispatchBatch $dispatch)
    {
        abort(403, 'Dispatch records cannot be deleted.');
    }
}
