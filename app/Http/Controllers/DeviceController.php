<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceEvent;
use App\Models\DeviceModel;
use App\Models\Vendor;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = Device::with(['model.brand', 'currentEmployee', 'client', 'currentLocation'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('serial_number', 'like', "%$q%")
                    ->orWhere('asset_tag', 'like', "%$q%")
                    ->orWhere('imei1', 'like', "%$q%")
                    ->orWhere('imei2', 'like', "%$q%");
            });
        }

        if ($request->filled('status')) {
            $query->where('lifecycle_status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $devices = $query->paginate(25)->withQueryString();

        return view('devices.index', compact('devices'));
    }

    public function create()
    {
        $models  = DeviceModel::with('brand')->where('is_active', true)->get();
        $vendors = Vendor::where('status', 'active')->get();

        return view('devices.create', compact('models', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'serial_number'   => 'required|unique:devices',
            'asset_tag'       => 'required|unique:devices',
            'imei1'           => 'nullable|unique:devices',
            'imei2'           => 'nullable|unique:devices',
            'device_model_id' => 'required|exists:device_models,id',
            'vendor_id'       => 'nullable|exists:vendors,id',
            'purchase_price'  => 'nullable|numeric|min:0',
            'purchase_date'   => 'nullable|date',
            'warranty_months' => 'nullable|integer|min:0',
        ]);

        $device = Device::create(array_merge($validated, ['lifecycle_status' => 'received']));

        DeviceEvent::create([
            'device_id'   => $device->id,
            'user_id'     => auth()->id(),
            'event_type'  => 'created',
            'to_status'   => 'received',
            'description' => 'Device registered in system',
            'event_at'    => now(),
        ]);

        return redirect()->route('devices.show', $device)->with('success', 'Device registered successfully.');
    }

    public function show(Device $device)
    {
        $device->load([
            'model.brand', 'vendor', 'client', 'currentEmployee', 'currentLocation',
            'handovers.employee', 'tickets', 'recoveryCases', 'repairOrders.serviceCenter',
            'insuranceClaims', 'events', 'ownershipHistory',
            'latestMdmSync', 'mdmEnrollment.profile',
        ]);

        return view('devices.show', compact('device'));
    }

    public function edit(Device $device)
    {
        $models  = DeviceModel::with('brand')->where('is_active', true)->get();
        $vendors = Vendor::where('status', 'active')->get();

        return view('devices.edit', compact('device', 'models', 'vendors'));
    }

    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'device_model_id' => 'required|exists:device_models,id',
            'purchase_price'  => 'nullable|numeric|min:0',
            'warranty_months' => 'nullable|integer|min:0',
            'condition'       => 'required|in:new,good,fair,poor,damaged',
            'notes'           => 'nullable|string',
        ]);

        $device->update($validated);

        return redirect()->route('devices.show', $device)->with('success', 'Device updated.');
    }

    public function destroy(Device $device)
    {
        $device->delete();

        return redirect()->route('devices.index')->with('success', 'Device removed.');
    }
}
