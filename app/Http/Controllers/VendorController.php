<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorContact;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::withCount(['purchaseOrders', 'devices'])
            ->orderBy('name');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('code', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vendors = $query->paginate(20)->withQueryString();

        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'required|string|unique:vendors|max:50',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:20',
            'alternate_phone'=> 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string|max:100',
            'state'          => 'nullable|string|max:100',
            'pincode'        => 'nullable|string|max:10',
            'gstin'          => 'nullable|string|max:20',
            'pan'            => 'nullable|string|max:10',
            'payment_terms'  => 'nullable|string|max:100',
            'credit_limit'   => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        $vendor = Vendor::create($validated);

        // Save contacts if provided
        if ($request->filled('contacts')) {
            foreach ($request->contacts as $contact) {
                if (!empty($contact['name'])) {
                    $vendor->contacts()->create($contact);
                }
            }
        }

        return redirect()->route('vendors.show', $vendor)->with('success', 'Vendor created successfully.');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load(['contacts', 'purchaseOrders' => fn ($q) => $q->latest()->limit(10), 'devices' => fn ($q) => $q->limit(10)]);

        return view('vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor)
    {
        $vendor->load('contacts');

        return view('vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'required|string|max:50|unique:vendors,code,' . $vendor->id,
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:20',
            'alternate_phone'=> 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string|max:100',
            'state'          => 'nullable|string|max:100',
            'pincode'        => 'nullable|string|max:10',
            'gstin'          => 'nullable|string|max:20',
            'pan'            => 'nullable|string|max:10',
            'payment_terms'  => 'nullable|string|max:100',
            'credit_limit'   => 'nullable|numeric|min:0',
            'status'         => 'required|in:active,inactive,blacklisted',
            'notes'          => 'nullable|string',
        ]);

        $vendor->update($validated);

        return redirect()->route('vendors.show', $vendor)->with('success', 'Vendor updated.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted.');
    }

    public function storeContact(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'designation' => 'nullable|string|max:100',
            'email'       => 'nullable|email',
            'phone'       => 'nullable|string|max:20',
            'is_primary'  => 'boolean',
        ]);

        if (!empty($validated['is_primary'])) {
            $vendor->contacts()->update(['is_primary' => false]);
        }

        $vendor->contacts()->create($validated);

        return back()->with('success', 'Contact added.');
    }

    public function destroyContact(Vendor $vendor, VendorContact $contact)
    {
        $contact->delete();

        return back()->with('success', 'Contact removed.');
    }
}
