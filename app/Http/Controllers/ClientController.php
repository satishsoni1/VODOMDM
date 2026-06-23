<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientProject;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::withCount(['employees', 'devices', 'projects'])
            ->orderBy('name');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('code', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $clients = $query->paginate(20)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
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

        return redirect()->route('clients.show', $client)->with('success', 'Client created successfully.');
    }

    public function show(Client $client)
    {
        $client->load([
            'projects',
            'employees' => fn ($q) => $q->where('status', 'active')->limit(10),
            'devices'   => fn ($q) => $q->limit(10),
        ]);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'required|string|max:50|unique:clients,code,' . $client->id,
            'industry'       => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string|max:100',
            'state'          => 'nullable|string|max:100',
            'pincode'        => 'nullable|string|max:10',
            'gstin'          => 'nullable|string|max:20',
            'status'         => 'required|in:active,inactive',
            'notes'          => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)->with('success', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted.');
    }

    public function storeProject(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|unique:client_projects|max:50',
            'description'=> 'nullable|string',
            'region'     => 'nullable|string|max:100',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $client->projects()->create($validated);

        return back()->with('success', 'Project added.');
    }
}
