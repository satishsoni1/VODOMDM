<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\RepairOrder;
use App\Models\ServiceCenter;
use App\Models\Ticket;
use App\Models\InsuranceClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RepairController extends Controller
{
    public function index(Request $request)
    {
        $query = RepairOrder::with(['device.model.brand', 'serviceCenter', 'createdBy'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('repair_type')) {
            $query->where('repair_type', $request->repair_type);
        }

        $repairs = $query->paginate(20)->withQueryString();

        $stats = [
            'active'     => RepairOrder::whereNotIn('status', ['returned', 'unrepairable'])->count(),
            'at_sc'      => RepairOrder::where('status', 'received_at_sc')->count(),
            'under_rep'  => RepairOrder::where('status', 'under_repair')->count(),
            'overdue'    => RepairOrder::whereNotIn('status', ['returned', 'unrepairable'])->where('estimated_return_date', '<', today())->count(),
        ];

        return view('repairs.index', compact('repairs', 'stats'));
    }

    public function create(Request $request)
    {
        $serviceCenters   = ServiceCenter::where('is_active', true)->orderBy('name')->get();
        $devices          = Device::with('model.brand')->whereNotIn('lifecycle_status', ['disposed'])->orderBy('asset_tag')->get();
        $openTickets      = Ticket::whereNotIn('status', ['resolved', 'closed', 'cancelled'])->latest()->get();
        $openClaims       = InsuranceClaim::whereNotIn('status', ['settled', 'closed', 'rejected'])->latest()->get();

        $selectedDevice = $request->filled('device_id')
            ? Device::with('model.brand')->find($request->device_id)
            : null;
        $selectedTicket = $request->filled('ticket_id')
            ? Ticket::find($request->ticket_id)
            : null;

        return view('repairs.create', compact('serviceCenters', 'devices', 'openTickets', 'openClaims', 'selectedDevice', 'selectedTicket'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id'             => 'required|exists:devices,id',
            'service_center_id'     => 'required|exists:service_centers,id',
            'ticket_id'             => 'nullable|exists:tickets,id',
            'insurance_claim_id'    => 'nullable|exists:insurance_claims,id',
            'fault_description'     => 'required|string',
            'detailed_notes'        => 'nullable|string',
            'sent_date'             => 'required|date',
            'estimated_return_date' => 'nullable|date|after:sent_date',
            'estimated_cost'        => 'nullable|numeric|min:0',
            'repair_type'           => 'required|in:warranty,paid,insurance',
            'under_warranty'        => 'sometimes|boolean',
        ]);

        $repair = RepairOrder::create([
            'rma_number'            => 'RMA-' . strtoupper(Str::random(8)),
            'device_id'             => $validated['device_id'],
            'service_center_id'     => $validated['service_center_id'],
            'ticket_id'             => $validated['ticket_id'] ?? null,
            'insurance_claim_id'    => $validated['insurance_claim_id'] ?? null,
            'created_by'            => auth()->id(),
            'fault_description'     => $validated['fault_description'],
            'detailed_notes'        => $validated['detailed_notes'] ?? null,
            'sent_date'             => $validated['sent_date'],
            'estimated_return_date' => $validated['estimated_return_date'] ?? null,
            'estimated_cost'        => $validated['estimated_cost'] ?? null,
            'repair_type'           => $validated['repair_type'],
            'under_warranty'        => $request->boolean('under_warranty'),
            'status'                => 'sent',
        ]);

        Device::find($validated['device_id'])->update(['lifecycle_status' => 'under_repair']);

        return redirect()->route('repairs.show', $repair)
            ->with('success', 'Repair order ' . $repair->rma_number . ' created.');
    }

    public function show(RepairOrder $repair)
    {
        $repair->load(['device.model.brand', 'serviceCenter', 'ticket', 'insuranceClaim', 'createdBy', 'replacementDevice.model.brand']);

        return view('repairs.show', compact('repair'));
    }

    public function edit(RepairOrder $repair)
    {
        $serviceCenters = ServiceCenter::where('is_active', true)->orderBy('name')->get();
        return view('repairs.edit', compact('repair', 'serviceCenters'));
    }

    public function update(Request $request, RepairOrder $repair)
    {
        $request->validate([
            'status'                => 'required|in:sent,received_at_sc,under_repair,awaiting_parts,repaired,replaced,unrepairable,returned',
            'actual_return_date'    => 'nullable|date',
            'actual_cost'           => 'nullable|numeric|min:0',
            'outcome'               => 'nullable|in:repaired,replaced,unrepairable',
            'repair_notes'          => 'nullable|string',
            'replacement_device_id' => 'nullable|exists:devices,id',
        ]);

        $data = $request->only(['status', 'actual_return_date', 'actual_cost', 'outcome', 'repair_notes', 'replacement_device_id']);

        if ($request->status === 'returned') {
            $outcome = $request->outcome ?? 'repaired';
            $newStatus = $outcome === 'repaired' ? 'in_stock' : 'disposed';
            $repair->device?->update(['lifecycle_status' => $newStatus]);
        }

        $repair->update($data);

        return back()->with('success', 'Repair order updated to ' . ucwords(str_replace('_', ' ', $request->status)) . '.');
    }

    public function destroy(RepairOrder $repair)
    {
        abort(403, 'Repair orders cannot be deleted.');
    }
}
