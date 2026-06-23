<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceEvent;
use App\Models\DeviceModel;
use App\Models\Grn;
use App\Models\Location;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function index()
    {
        $stats = [
            'total'        => Device::count(),
            'in_stock'     => Device::where('lifecycle_status', 'in_stock')->count(),
            'received'     => Device::where('lifecycle_status', 'received')->count(),
            'qc_pending'   => Device::where('lifecycle_status', 'qc_pending')->count(),
            'config_pending'=> Device::where('lifecycle_status', 'config_pending')->count(),
        ];

        $locationStock = Device::with('currentLocation')
            ->whereIn('lifecycle_status', ['in_stock', 'received', 'qc_pending'])
            ->select('current_location_id', \DB::raw('count(*) as count'))
            ->groupBy('current_location_id')
            ->with('currentLocation')
            ->get();

        $recentGrns = Grn::with(['purchaseOrder', 'vendor', 'location'])->latest()->limit(10)->get();

        return view('inventory.index', compact('stats', 'recentGrns', 'locationStock'));
    }

    // ─── GRN ────────────────────────────────────────────────────
    public function grnList(Request $request)
    {
        $query = Grn::with(['purchaseOrder', 'vendor', 'location', 'receiver'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $grns = $query->paginate(20)->withQueryString();

        return view('inventory.grn.index', compact('grns'));
    }

    public function createGrn(Request $request)
    {
        $purchaseOrders = PurchaseOrder::with('vendor')
            ->whereIn('status', ['approved', 'sent', 'acknowledged', 'partial'])
            ->orderBy('po_date', 'desc')->get();
        $locations = Location::where('type', 'warehouse')->where('is_active', true)->orderBy('name')->get();

        $selectedPo = $request->filled('po_id') ? PurchaseOrder::with('vendor')->find($request->po_id) : null;

        return view('inventory.grn.create', compact('purchaseOrders', 'locations', 'selectedPo'));
    }

    public function storeGrn(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id'        => 'required|exists:purchase_orders,id',
            'location_id'              => 'required|exists:locations,id',
            'received_date'            => 'required|date',
            'quantity_received'        => 'required|integer|min:1',
            'quantity_accepted'        => 'required|integer|min:0',
            'quantity_rejected'        => 'nullable|integer|min:0',
            'delivery_challan_number'  => 'nullable|string|max:100',
            'invoice_number'           => 'nullable|string|max:100',
            'remarks'                  => 'nullable|string',
        ]);

        $po = PurchaseOrder::findOrFail($validated['purchase_order_id']);

        $grn = Grn::create(array_merge($validated, [
            'grn_number'        => 'GRN-' . strtoupper(Str::random(8)),
            'vendor_id'         => $po->vendor_id,
            'received_by'       => auth()->id(),
            'quantity_ordered'  => $po->quantity,
            'quantity_rejected' => $validated['quantity_rejected'] ?? 0,
            'status'            => 'pending_qc',
        ]));

        // Update PO status
        $po->update(['status' => 'partial']);

        return redirect()->route('inventory.grn.show', $grn)->with('success', 'GRN recorded. You can now register devices.');
    }

    public function showGrn(Grn $grn)
    {
        $grn->load(['purchaseOrder.vendor', 'location', 'receiver', 'devices.model.brand']);
        $deviceModels = DeviceModel::with('brand')->where('is_active', true)->get();

        return view('inventory.grn.show', compact('grn', 'deviceModels'));
    }

    // ─── Device Registration from GRN ───────────────────────────
    public function registerDevice(Request $request, Grn $grn)
    {
        $validated = $request->validate([
            'serial_number'   => 'required|string|unique:devices',
            'asset_tag'       => 'required|string|unique:devices',
            'imei1'           => 'nullable|string|unique:devices',
            'imei2'           => 'nullable|string|unique:devices',
            'device_model_id' => 'required|exists:device_models,id',
            'color'           => 'nullable|string|max:50',
            'box_number'      => 'nullable|string|max:50',
            'warranty_months' => 'nullable|integer|min:0',
        ]);

        $device = Device::create(array_merge($validated, [
            'grn_id'            => $grn->id,
            'purchase_order_id' => $grn->purchase_order_id,
            'vendor_id'         => $grn->vendor_id,
            'purchase_date'     => $grn->received_date,
            'purchase_price'    => $grn->purchaseOrder?->unit_price,
            'lifecycle_status'  => 'received',
            'current_location_id' => $grn->location_id,
        ]));

        DeviceEvent::create([
            'device_id'   => $device->id,
            'user_id'     => auth()->id(),
            'event_type'  => 'received',
            'to_status'   => 'received',
            'description' => 'Device received via GRN ' . $grn->grn_number,
            'event_at'    => now(),
        ]);

        return back()->with('success', "Device {$device->asset_tag} registered.");
    }

    public function bulkRegisterDevices(Request $request, Grn $grn)
    {
        $request->validate([
            'devices'               => 'required|array|min:1',
            'devices.*.serial_number' => 'required|string|distinct|unique:devices',
            'devices.*.asset_tag'    => 'required|string|distinct|unique:devices',
            'devices.*.imei1'        => 'nullable|string|distinct|unique:devices',
            'device_model_id'       => 'required|exists:device_models,id',
        ]);

        $count = 0;
        foreach ($request->devices as $row) {
            if (empty($row['serial_number']) || empty($row['asset_tag'])) {
                continue;
            }

            $device = Device::create([
                'serial_number'     => $row['serial_number'],
                'asset_tag'         => $row['asset_tag'],
                'imei1'             => $row['imei1'] ?? null,
                'imei2'             => $row['imei2'] ?? null,
                'device_model_id'   => $request->device_model_id,
                'grn_id'            => $grn->id,
                'purchase_order_id' => $grn->purchase_order_id,
                'vendor_id'         => $grn->vendor_id,
                'purchase_date'     => $grn->received_date,
                'purchase_price'    => $grn->purchaseOrder?->unit_price,
                'lifecycle_status'  => 'received',
                'current_location_id' => $grn->location_id,
            ]);

            DeviceEvent::create([
                'device_id'   => $device->id,
                'user_id'     => auth()->id(),
                'event_type'  => 'received',
                'to_status'   => 'received',
                'description' => 'Device received via GRN ' . $grn->grn_number,
                'event_at'    => now(),
            ]);

            $count++;
        }

        // Mark GRN as accepted when enough devices registered
        if ($count >= $grn->quantity_accepted) {
            $grn->update(['status' => 'accepted']);
            $grn->purchaseOrder?->update(['status' => 'completed']);
        }

        return back()->with('success', "$count device(s) registered successfully.");
    }
}
