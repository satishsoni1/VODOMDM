<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ClientUserController extends Controller
{
    public function index()
    {
        $clientRole = Role::where('slug', 'client')->first();
        $users = User::with(['role', 'client'])
            ->where('role_id', $clientRole?->id)
            ->orderBy('name')
            ->paginate(20);

        return view('client-users.index', compact('users'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('client-users.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'client_id' => 'required|exists:clients,id',
            'password'  => ['required', Password::min(8)->mixedCase()->numbers()],
        ]);

        $clientRole = Role::where('slug', 'client')->firstOrFail();

        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'client_id' => $request->client_id,
            'role_id'   => $clientRole->id,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        return redirect()->route('client-users.index')
            ->with('success', "Client login created for {$request->name}.");
    }

    public function edit(User $clientUser)
    {
        $clients = Client::orderBy('name')->get();
        return view('client-users.edit', compact('clientUser', 'clients'));
    }

    public function update(Request $request, User $clientUser)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $clientUser->id,
            'client_id' => 'required|exists:clients,id',
            'password'  => ['nullable', Password::min(8)->mixedCase()->numbers()],
            'is_active' => 'boolean',
        ]);

        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
            'client_id' => $request->client_id,
            'is_active' => $request->boolean('is_active'),
        ];
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $clientUser->update($data);

        return redirect()->route('client-users.index')
            ->with('success', "Client user updated.");
    }

    public function destroy(User $clientUser)
    {
        $clientUser->delete();
        return back()->with('success', 'Client user removed.');
    }
}
