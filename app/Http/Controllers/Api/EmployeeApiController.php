<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeApiController extends GlobalApiController
{
    /**
     * GET /api/employees
     * Optional query params: client_code, designation, status, page
     */
    public function index(Request $request): JsonResponse
    {
        $log = $this->startLog($request, 'employee_api', 'list');

        $query = Employee::with(['client:id,name,code', 'project:id,name'])
            ->select([
                'id', 'employee_code', 'name', 'email', 'phone',
                'client_id', 'client_project_id', 'designation', 'department',
                'city', 'state', 'region', 'hq', 'status', 'joining_date',
            ]);

        if ($request->filled('client_code')) {
            $client = Client::where('code', $request->client_code)->first();
            if (! $client) {
                $log->finish('failed', "Client code not found: {$request->client_code}");
                return $this->notFound("Client '{$request->client_code}' not found.");
            }
            $query->where('client_id', $client->id);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('designation')) {
            $query->where('designation', $request->designation);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($sq) =>
                $sq->where('name', 'LIKE', "%{$q}%")
                   ->orWhere('employee_code', 'LIKE', "%{$q}%")
                   ->orWhere('phone', 'LIKE', "%{$q}%")
            );
        }

        $employees = $query->orderBy('name')->paginate(100);

        $log->finish('success', "Returned {$employees->total()} employees", [
            'records_out' => $employees->count(),
        ]);

        return $this->ok(
            $employees->items(),
            'OK',
            [
                'total'        => $employees->total(),
                'per_page'     => $employees->perPage(),
                'current_page' => $employees->currentPage(),
                'last_page'    => $employees->lastPage(),
            ]
        );
    }

    /**
     * POST /api/employees/sync
     * Body: { "client_code": "ACME", "employees": [...] }
     * Upserts employees for the given client.
     */
    public function sync(Request $request): JsonResponse
    {
        $log = $this->startLog($request, 'employee_api', 'sync');

        $validated = $request->validate([
            'client_code'          => 'required|string',
            'employees'            => 'required|array|min:1|max:1000',
            'employees.*.employee_code' => 'required|string',
            'employees.*.name'          => 'required|string',
            'employees.*.phone'         => 'nullable|string',
            'employees.*.designation'   => 'nullable|string',
            'employees.*.city'          => 'nullable|string',
            'employees.*.state'         => 'nullable|string',
        ]);

        $client = Client::where('code', $validated['client_code'])->first();
        if (! $client) {
            $log->finish('failed', "Client not found: {$validated['client_code']}");
            return $this->notFound("Client '{$validated['client_code']}' not found.");
        }

        $upserted = 0;
        $created  = 0;
        $errors   = [];

        foreach ($validated['employees'] as $i => $row) {
            try {
                $existed = Employee::where('employee_code', $row['employee_code'])->exists();
                Employee::updateOrCreate(
                    ['employee_code' => $row['employee_code']],
                    array_merge($row, ['client_id' => $client->id])
                );
                $existed ? $upserted++ : $created++;
            } catch (\Throwable $e) {
                $errors[] = ['index' => $i, 'code' => $row['employee_code'], 'error' => $e->getMessage()];
            }
        }

        $log->finish('success',
            "Sync for {$client->name}: {$created} created, {$upserted} updated, " . count($errors) . " errors", [
                'records_in'   => count($validated['employees']),
                'records_out'  => $created + $upserted,
                'response_data'=> compact('created', 'upserted', 'errors'),
            ]);

        return $this->ok(compact('created', 'upserted', 'errors'),
            "{$created} created, {$upserted} updated.");
    }

    /**
     * GET /api/employees/{code}
     */
    public function show(Request $request, string $code): JsonResponse
    {
        $log = $this->startLog($request, 'employee_api', 'show');

        $employee = Employee::with(['client:id,name,code', 'project:id,name', 'mdmDevice'])
            ->where('employee_code', $code)->first();

        if (! $employee) {
            $log->finish('failed', "Employee not found: {$code}");
            return $this->notFound("Employee '{$code}' not found.");
        }

        $log->finish('success', "Returned employee {$code}");
        return $this->ok($employee);
    }
}
