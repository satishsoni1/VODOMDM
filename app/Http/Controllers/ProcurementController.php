<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DemandRequest;
use App\Models\DeviceModel;
use App\Models\PurchaseOrder;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProcurementController extends Controller
{
    // ─── Dashboard ──────────────────────────────────────────────
    public function index()
    {
        $stats = [
            'pending_approval' => DemandRequest::where('status', 'submitted')->count(),
            'open_rfqs'        => Rfq::where('status', 'sent')->count(),
            'pending_pos'      => PurchaseOrder::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'total_po_value'   => PurchaseOrder::whereNotIn('status', ['cancelled'])->sum('grand_total'),
        ];

        $recentPOs        = PurchaseOrder::with('vendor')->latest()->limit(5)->get();
        $pendingApprovals = DemandRequest::with(['client', 'requestedBy'])->where('status', 'submitted')->latest()->limit(5)->get();
        $recentRfqs       = Rfq::with('demandRequest')->latest()->limit(5)->get();

        return view('procurement.index', compact('stats', 'recentPOs', 'pendingApprovals', 'recentRfqs'));
    }

    // ─── Demand Requests ────────────────────────────────────────
    public function demandRequests(Request $request)
    {
        $query = DemandRequest::with(['client', 'requestedBy', 'deviceModel'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $demands = $query->paginate(20)->withQueryString();
        $clients = Client::where('status', 'active')->orderBy('name')->get();

        return view('procurement.demand_requests.index', compact('demands', 'clients'));
    }

    public function createDemandRequest()
    {
        $clients      = Client::with('projects')->where('status', 'active')->orderBy('name')->get();
        $deviceModels = DeviceModel::with('brand')->where('is_active', true)->orderBy('model_name')->get();

        return view('procurement.demand_requests.create', compact('clients', 'deviceModels'));
    }

    public function storeDemandRequest(Request $request)
    {
        $validated = $request->validate([
            'client_id'           => 'required|exists:clients,id',
            'client_project_id'   => 'nullable|exists:client_projects,id',
            'device_model_id'     => 'nullable|exists:device_models,id',
            'device_specification'=> 'required|string',
            'quantity'            => 'required|integer|min:1',
            'required_date'       => 'nullable|date',
            'budget_amount'       => 'nullable|numeric|min:0',
            'division'            => 'nullable|string|max:100',
            'region'              => 'nullable|string|max:100',
            'justification'       => 'nullable|string',
        ]);

        $validated['request_number'] = 'DR-' . strtoupper(Str::random(8));
        $validated['requested_by']   = auth()->id();
        $validated['status']         = $request->input('action') === 'submit' ? 'submitted' : 'draft';

        $demand = DemandRequest::create($validated);

        return redirect()->route('procurement.demand-requests.show', $demand)
            ->with('success', 'Demand request ' . ($validated['status'] === 'submitted' ? 'submitted' : 'saved as draft') . '.');
    }

    public function showDemandRequest(DemandRequest $demandRequest)
    {
        $demandRequest->load(['client', 'project', 'requestedBy', 'approvedBy', 'deviceModel.brand', 'rfqs']);

        return view('procurement.demand_requests.show', compact('demandRequest'));
    }

    public function approveDemandRequest(Request $request, DemandRequest $demandRequest)
    {
        $action = $request->input('action');

        if ($action === 'approve') {
            $demandRequest->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            return back()->with('success', 'Demand request approved.');
        }

        $demandRequest->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->input('reason'),
        ]);

        return back()->with('success', 'Demand request rejected.');
    }

    // ─── RFQs ───────────────────────────────────────────────────
    public function rfqs(Request $request)
    {
        $query = Rfq::with(['demandRequest.client', 'createdBy', 'vendors', 'quotations'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rfqs = $query->paginate(20)->withQueryString();

        return view('procurement.rfqs.index', compact('rfqs'));
    }

    public function createRfq(Request $request)
    {
        $demands = DemandRequest::with('client')
            ->whereIn('status', ['approved'])
            ->orderBy('created_at', 'desc')->get();
        $vendors = Vendor::where('status', 'active')->orderBy('name')->get();

        $selectedDemand = $request->filled('demand_request_id')
            ? DemandRequest::find($request->demand_request_id)
            : null;

        return view('procurement.rfqs.create', [
            'demands'        => $demands,
            'vendors'        => $vendors,
            'selectedDemand' => $selectedDemand,
        ]);
    }

    public function storeRfq(Request $request)
    {
        $validated = $request->validate([
            'demand_request_id'   => 'nullable|exists:demand_requests,id',
            'device_specification'=> 'required|string',
            'quantity'            => 'required|integer|min:1',
            'response_deadline'   => 'nullable|date',
            'terms'               => 'nullable|string',
            'vendor_ids'          => 'required|array|min:1',
            'vendor_ids.*'        => 'exists:vendors,id',
        ]);

        $action = $request->input('action', 'send');
        $status = $action === 'send' ? 'sent' : 'draft';

        $rfq = Rfq::create([
            'rfq_number'           => 'RFQ-' . strtoupper(Str::random(8)),
            'demand_request_id'    => $validated['demand_request_id'] ?? null,
            'created_by'           => auth()->id(),
            'device_specification' => $validated['device_specification'],
            'quantity'             => $validated['quantity'],
            'response_deadline'    => $validated['response_deadline'] ?? null,
            'terms'                => $validated['terms'] ?? null,
            'status'               => $status,
        ]);

        foreach ($validated['vendor_ids'] as $vendorId) {
            $rfq->vendors()->create([
                'vendor_id' => $vendorId,
                'sent_at'   => $status === 'sent' ? now() : null,
                'status'    => 'pending',
            ]);
        }

        return redirect()->route('procurement.rfqs.show', $rfq)
            ->with('success', 'RFQ created' . ($status === 'sent' ? ' and sent to ' . count($validated['vendor_ids']) . ' vendor(s).' : ' as draft.'));
    }

    public function showRfq(Rfq $rfq)
    {
        $rfq->load(['demandRequest.client', 'createdBy', 'vendors.vendor', 'quotations.vendor']);

        return view('procurement.rfqs.show', compact('rfq'));
    }

    public function storeQuotation(Request $request, Rfq $rfq)
    {
        $validated = $request->validate([
            'vendor_id'        => 'required|exists:vendors,id',
            'quotation_number' => 'nullable|string|max:100',
            'quotation_date'   => 'required|date',
            'valid_until'      => 'nullable|date',
            'quantity'         => 'required|integer|min:1',
            'unit_price'       => 'required|numeric|min:0',
            'delivery_days'    => 'nullable|integer|min:0',
            'warranty_months'  => 'nullable|string|max:10',
            'terms'            => 'nullable|string',
            'negotiation_notes'=> 'nullable|string',
        ]);

        $validated['total_amount'] = $validated['quantity'] * $validated['unit_price'];

        $rfq->quotations()->create($validated);

        // Mark vendor response
        $rfq->vendors()->where('vendor_id', $validated['vendor_id'])->update(['status' => 'responded']);

        return back()->with('success', 'Quotation recorded.');
    }

    // ─── Purchase Orders ─────────────────────────────────────────
    public function purchaseOrders(Request $request)
    {
        $query = PurchaseOrder::with(['vendor', 'createdBy'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $orders  = $query->paginate(20)->withQueryString();
        $vendors = Vendor::where('status', 'active')->orderBy('name')->get();

        return view('procurement.purchase_orders.index', compact('orders', 'vendors'));
    }

    public function createPo(Request $request)
    {
        $vendors  = Vendor::where('status', 'active')->orderBy('name')->get();
        $rfqs     = Rfq::with('demandRequest.client')->whereIn('status', ['sent', 'closed'])->latest()->get();
        $demands  = DemandRequest::with('client')->whereIn('status', ['approved', 'converted_to_po'])->latest()->get();

        $selectedQuotation = $request->filled('quotation_id')
            ? VendorQuotation::with(['rfq', 'vendor'])->find($request->quotation_id)
            : null;

        return view('procurement.purchase_orders.create', [
            'vendors'           => $vendors,
            'rfqs'              => $rfqs,
            'demands'           => $demands,
            'selectedQuotation' => $selectedQuotation,
        ]);
    }

    public function storePo(Request $request)
    {
        $validated = $request->validate([
            'vendor_id'             => 'required|exists:vendors,id',
            'rfq_id'                => 'nullable|exists:rfqs,id',
            'demand_request_id'     => 'nullable|exists:demand_requests,id',
            'vendor_quotation_id'   => 'nullable|exists:vendor_quotations,id',
            'po_date'               => 'required|date',
            'expected_delivery_date'=> 'nullable|date',
            'quantity'              => 'required|integer|min:1',
            'unit_price'            => 'required|numeric|min:0',
            'tax_amount'            => 'nullable|numeric|min:0',
            'grand_total'           => 'nullable|numeric|min:0',
            'payment_terms'         => 'nullable|string|max:200',
            'delivery_address'      => 'nullable|string',
            'warranty_months'       => 'nullable|string|max:20',
            'special_instructions'  => 'nullable|string',
        ]);

        $taxAmount   = (float) ($validated['tax_amount'] ?? 0);
        $totalAmount = (float) $validated['quantity'] * (float) $validated['unit_price'];

        $po = PurchaseOrder::create(array_merge($validated, [
            'po_number'    => 'PO-' . strtoupper(Str::random(8)),
            'created_by'   => auth()->id(),
            'total_amount' => $totalAmount,
            'tax_amount'   => $taxAmount,
            'grand_total'  => $totalAmount + $taxAmount,
            'status'       => $request->input('action') === 'approve' ? 'approved' : 'draft',
            'approved_by'  => $request->input('action') === 'approve' ? auth()->id() : null,
            'approved_at'  => $request->input('action') === 'approve' ? now() : null,
        ]));

        if (!empty($validated['demand_request_id'])) {
            DemandRequest::find($validated['demand_request_id'])->update(['status' => 'converted_to_po']);
        }

        return redirect()->route('procurement.purchase-orders.show', $po)
            ->with('success', 'Purchase Order ' . ($po->status === 'approved' ? 'created and approved.' : 'saved as draft.'));
    }

    public function showPo(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['vendor', 'demandRequest.client', 'rfq', 'createdBy', 'approvedBy', 'grns', 'invoices']);

        return view('procurement.purchase_orders.show', compact('purchaseOrder'));
    }

    public function approvePo(Request $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Purchase Order approved.');
    }

    public function storeInvoice(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'invoice_number' => 'required|string|max:100',
            'invoice_date'   => 'required|date',
            'invoice_amount' => 'required|numeric|min:0',
            'due_date'       => 'nullable|date',
            'notes'          => 'nullable|string',
        ]);

        $purchaseOrder->invoices()->create($request->only([
            'invoice_number', 'invoice_date', 'invoice_amount', 'due_date', 'notes',
        ]));

        return back()->with('success', 'Invoice recorded.');
    }
}
