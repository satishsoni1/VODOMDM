<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['client', 'project'])
            ->orderBy('name');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('employee_code', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%");
            });
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('region')) {
            $query->where('region', 'like', '%' . $request->region . '%');
        }

        $employees = $query->paginate(25)->withQueryString();
        $clients   = Client::where('status', 'active')->orderBy('name')->get();

        return view('employees.index', compact('employees', 'clients'));
    }

    public function create()
    {
        $clients   = Client::with('projects')->where('status', 'active')->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return view('employees.create', compact('clients', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_code'    => 'required|string|unique:employees|max:50',
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:20',
            'email'            => 'nullable|email|max:255',
            'alternate_phone'  => 'nullable|string|max:20',
            'client_id'        => 'nullable|exists:clients,id',
            'client_project_id'=> 'nullable|exists:client_projects,id',
            'designation'      => 'nullable|string|max:100',
            'department'       => 'nullable|string|max:100',
            'region'           => 'nullable|string|max:100',
            'hq'               => 'nullable|string|max:100',
            'abm'              => 'nullable|string|max:100',
            'manager_name'     => 'nullable|string|max:255',
            'manager_phone'    => 'nullable|string|max:20',
            'manager_email'    => 'nullable|email|max:255',
            'location_id'      => 'nullable|exists:locations,id',
            'joining_date'     => 'nullable|date',
            'address'          => 'nullable|string',
            'city'             => 'nullable|string|max:100',
            'state'            => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
        ]);

        $employee = Employee::create($validated);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee created.');
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'client', 'project', 'location',
            'currentDevices.model.brand',
            'handovers.device',
            'recoveryCases',
            'callLogs' => fn ($q) => $q->latest()->limit(10),
        ]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $clients   = Client::with('projects')->where('status', 'active')->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return view('employees.edit', compact('employee', 'clients', 'locations'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_code'    => 'required|string|max:50|unique:employees,employee_code,' . $employee->id,
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:20',
            'email'            => 'nullable|email|max:255',
            'alternate_phone'  => 'nullable|string|max:20',
            'client_id'        => 'nullable|exists:clients,id',
            'client_project_id'=> 'nullable|exists:client_projects,id',
            'designation'      => 'nullable|string|max:100',
            'department'       => 'nullable|string|max:100',
            'region'           => 'nullable|string|max:100',
            'hq'               => 'nullable|string|max:100',
            'abm'              => 'nullable|string|max:100',
            'manager_name'     => 'nullable|string|max:255',
            'manager_phone'    => 'nullable|string|max:20',
            'manager_email'    => 'nullable|email|max:255',
            'location_id'      => 'nullable|exists:locations,id',
            'joining_date'     => 'nullable|date',
            'exit_date'        => 'nullable|date',
            'status'           => 'required|in:active,inactive,resigned,terminated,on_leave',
            'address'          => 'nullable|string',
            'city'             => 'nullable|string|max:100',
            'state'            => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted.');
    }
}
