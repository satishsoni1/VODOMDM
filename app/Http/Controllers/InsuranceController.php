<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Device;
use App\Models\InsuranceClaim;
use App\Models\InsurancePolicy;
use App\Models\InsuranceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InsuranceController extends Controller
{
    public function index(Request $request)
    {
        $query = InsurancePolicy::with(['provider', 'client', 'deviceInsurances', 'claims'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $policies = $query->paginate(20)->withQueryString();
        $clients  = Client::where('status', 'active')->orderBy('name')->get();

        $stats = [
            'active'    => InsurancePolicy::where('status', 'active')->count(),
            'expiring'  => InsurancePolicy::where('status', 'active')->where('expiry_date', '<=', now()->addDays(30))->count(),
            'claims'    => InsuranceClaim::whereNotIn('status', ['settled', 'closed', 'rejected'])->count(),
            'total_sum' => InsurancePolicy::where('status', 'active')->sum('sum_insured'),
        ];

        return view('insurance.index', compact('policies', 'clients', 'stats'));
    }

    public function create()
    {
        $providers = InsuranceProvider::where('is_active', true)->orderBy('name')->get();
        $clients   = Client::where('status', 'active')->orderBy('name')->get();

        return view('insurance.create', compact('providers', 'clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'insurance_provider_id' => 'required|exists:insurance_providers,id',
            'client_id'             => 'nullable|exists:clients,id',
            'coverage_type'         => 'required|string|max:100',
            'coverage_details'      => 'nullable|string',
            'premium_amount'        => 'required|numeric|min:0',
            'sum_insured'           => 'required|numeric|min:0',
            'start_date'            => 'required|date',
            'expiry_date'           => 'required|date|after:start_date',
            'terms'                 => 'nullable|string',
        ]);

        $policy = InsurancePolicy::create([
            'policy_number'         => 'POL-' . strtoupper(Str::random(8)),
            'insurance_provider_id' => $validated['insurance_provider_id'],
            'client_id'             => $validated['client_id'] ?? null,
            'coverage_type'         => $validated['coverage_type'],
            'coverage_details'      => $validated['coverage_details'] ?? null,
            'premium_amount'        => $validated['premium_amount'],
            'sum_insured'           => $validated['sum_insured'],
            'start_date'            => $validated['start_date'],
            'expiry_date'           => $validated['expiry_date'],
            'terms'                 => $validated['terms'] ?? null,
            'status'                => 'active',
        ]);

        return redirect()->route('insurance.show', $policy)
            ->with('success', 'Insurance policy ' . $policy->policy_number . ' created.');
    }

    public function show(InsurancePolicy $insurance)
    {
        $insurance->load(['provider', 'client', 'deviceInsurances.device.model.brand', 'claims.device', 'claims.raisedBy']);

        $availableDevices = Device::with('model.brand')
            ->whereNotIn('lifecycle_status', ['disposed', 'lost'])
            ->orderBy('asset_tag')->get();

        return view('insurance.show', compact('insurance', 'availableDevices'));
    }

    public function edit(InsurancePolicy $insurance)
    {
        $providers = InsuranceProvider::where('is_active', true)->orderBy('name')->get();
        $clients   = Client::where('status', 'active')->orderBy('name')->get();

        return view('insurance.edit', compact('insurance', 'providers', 'clients'));
    }

    public function update(Request $request, InsurancePolicy $insurance)
    {
        $request->validate([
            'status'                => 'required|in:active,expiring,expired,cancelled',
            'insurance_provider_id' => 'sometimes|exists:insurance_providers,id',
            'client_id'             => 'nullable|exists:clients,id',
            'expiry_date'           => 'nullable|date',
            'terms'                 => 'nullable|string',
        ]);

        $insurance->update($request->only(['status', 'insurance_provider_id', 'client_id', 'expiry_date', 'terms']));

        return redirect()->route('insurance.show', $insurance)->with('success', 'Policy updated.');
    }

    public function destroy(InsurancePolicy $insurance)
    {
        abort(403, 'Insurance policies cannot be deleted.');
    }

    public function claims(Request $request)
    {
        $query = InsuranceClaim::with(['device.model.brand', 'policy.provider', 'raisedBy'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $claims = $query->paginate(20)->withQueryString();

        return view('insurance.claims', compact('claims'));
    }

    public function storeClaim(Request $request, InsurancePolicy $insurancePolicy)
    {
        $request->validate([
            'device_id'            => 'required|exists:devices,id',
            'incident_date'        => 'required|date',
            'incident_type'        => 'required|string|max:100',
            'incident_description' => 'required|string',
            'claimed_amount'       => 'required|numeric|min:0',
            'claim_date'           => 'required|date',
            'remarks'              => 'nullable|string',
        ]);

        InsuranceClaim::create([
            'claim_number'         => 'CLM-' . strtoupper(Str::random(8)),
            'device_id'            => $request->device_id,
            'insurance_policy_id'  => $insurancePolicy->id,
            'raised_by'            => auth()->id(),
            'incident_date'        => $request->incident_date,
            'incident_type'        => $request->incident_type,
            'incident_description' => $request->incident_description,
            'claimed_amount'       => $request->claimed_amount,
            'claim_date'           => $request->claim_date,
            'remarks'              => $request->remarks,
            'status'               => 'submitted',
        ]);

        return back()->with('success', 'Insurance claim filed.');
    }
}
