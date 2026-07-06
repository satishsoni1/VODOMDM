<?php

namespace App\Http\Controllers;

use App\Imports\DevicesImport;
use App\Models\Device;
use App\Models\DeviceEvent;
use App\Models\DeviceModel;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = Device::with(['model.brand', 'currentEmployee', 'client', 'currentLocation', 'mdmDevice'])
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

    // ── Bulk Import ─────────────────────────────────────────────────────────

    public function importForm()
    {
        return view('devices.import');
    }

    public function import(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new DevicesImport();
        Excel::import($import, $request->file('file'));

        $summary = [
            'imported'    => $import->importedCount,
            'updated'     => $import->updatedCount,
            'skipped'     => $import->skippedCount,
            'mdm_matched' => $import->mdmMatchedCount,
            'errors'      => $import->errors,
        ];

        if ($request->ajax()) {
            session(['device_import_summary' => $summary]);
            return response()->json(['redirect' => route('devices.index')]);
        }

        return redirect()->route('devices.index')->with('device_import_summary', $summary);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="device_import_template.csv"',
        ];

        $columns = [
            'asset_tag', 'serial_number', 'imei1', 'imei2', 'client_code',
            'brand', 'model_name', 'model_number', 'category',
            'warehouse_code', 'warehouse_name', 'vendor_code', 'vendor_name',
            'condition', 'purchase_date', 'purchase_price', 'warranty_months',
            'box_number', 'color', 'notes',
        ];

        $sample = [
            'AST-00500', 'SN1234567890', '351234567890123', '', 'CLI-001',
            'Samsung', 'Galaxy A15', 'SM-A155F', 'Smartphone',
            'WH-MUM', 'Mumbai Warehouse', 'VEND001', 'Acme Distributors',
            'new', '2026-06-01', '12000', '12',
            'BOX-045', 'Black', 'Bulk import batch 1',
        ];

        $callback = function () use ($columns, $sample) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            fputcsv($handle, $sample);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
