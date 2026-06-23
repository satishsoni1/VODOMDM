<?php

namespace App\Http\Controllers;

use App\Models\CallLog;
use App\Models\Client;
use App\Models\Device;
use App\Models\Employee;
use App\Models\RecoveryCase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecoveryController extends Controller
{
    public function index(Request $request)
    {
        $query = RecoveryCase::with(['device.model.brand', 'employee', 'client', 'assignedTo'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $cases   = $query->paginate(20)->withQueryString();
        $clients = Client::where('status', 'active')->orderBy('name')->get();

        $stats = [
            'open'      => RecoveryCase::whereIn('status', ['open', 'contacted', 'pickup_scheduled'])->count(),
            'overdue'   => RecoveryCase::whereIn('status', ['open', 'contacted', 'pickup_scheduled'])->where('recovery_due_date', '<', today())->count(),
            'recovered' => RecoveryCase::where('status', 'recovered')->count(),
            'escalated' => RecoveryCase::where('status', 'escalated')->count(),
        ];

        return view('recovery.index', compact('cases', 'clients', 'stats'));
    }

    public function create(Request $request)
    {
        $clients   = Client::where('status', 'active')->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        $agents    = User::orderBy('name')->get();
        $devices   = Device::with('model.brand')
            ->whereIn('lifecycle_status', ['assigned', 'active'])
            ->orderBy('asset_tag')->get();

        $selectedEmployee = $request->filled('employee_id')
            ? Employee::with(['currentDevices.model.brand'])->find($request->employee_id)
            : null;

        return view('recovery.create', compact('clients', 'employees', 'agents', 'devices', 'selectedEmployee'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id'            => 'required|exists:devices,id',
            'employee_id'          => 'required|exists:employees,id',
            'client_id'            => 'required|exists:clients,id',
            'assigned_to'          => 'nullable|exists:users,id',
            'trigger_reason'       => 'required|in:resignation,termination,transfer,long_leave,other',
            'exit_date'            => 'nullable|date',
            'recovery_due_date'    => 'nullable|date',
            'pickup_address'       => 'nullable|string',
            'remarks'              => 'nullable|string',
        ]);

        $case = RecoveryCase::create([
            'case_number'       => 'RCV-' . strtoupper(Str::random(8)),
            'device_id'         => $validated['device_id'],
            'employee_id'       => $validated['employee_id'],
            'client_id'         => $validated['client_id'],
            'assigned_to'       => $validated['assigned_to'] ?? null,
            'created_by'        => auth()->id(),
            'trigger_reason'    => $validated['trigger_reason'],
            'exit_date'         => $validated['exit_date'] ?? null,
            'recovery_due_date' => $validated['recovery_due_date'] ?? null,
            'pickup_address'    => $validated['pickup_address'] ?? null,
            'remarks'           => $validated['remarks'] ?? null,
            'status'            => 'open',
            'follow_up_count'   => 0,
        ]);

        Device::find($validated['device_id'])->update(['lifecycle_status' => 'recovery_pending']);

        return redirect()->route('recovery.show', $case)
            ->with('success', 'Recovery case ' . $case->case_number . ' opened.');
    }

    public function show(RecoveryCase $recovery)
    {
        $recovery->load(['device.model.brand', 'employee', 'client', 'assignedTo', 'createdBy', 'callLogs.calledBy']);

        return view('recovery.show', compact('recovery'));
    }

    public function edit(RecoveryCase $recovery)
    {
        $agents = User::orderBy('name')->get();
        return view('recovery.edit', compact('recovery', 'agents'));
    }

    public function update(Request $request, RecoveryCase $recovery)
    {
        $request->validate([
            'status'               => 'required|in:open,contacted,pickup_scheduled,recovered,escalated,closed,written_off',
            'assigned_to'          => 'nullable|exists:users,id',
            'pickup_scheduled_date'=> 'nullable|date',
            'recovery_due_date'    => 'nullable|date',
            'pickup_address'       => 'nullable|string',
            'remarks'              => 'nullable|string',
        ]);

        $data = $request->only(['status', 'assigned_to', 'pickup_scheduled_date', 'recovery_due_date', 'pickup_address', 'remarks']);

        if ($request->status === 'recovered') {
            $data['recovered_date'] = now()->toDateString();
            $recovery->device?->update(['lifecycle_status' => 'returned']);
        }

        $recovery->update($data);

        return back()->with('success', 'Recovery case updated.');
    }

    public function destroy(RecoveryCase $recovery)
    {
        abort(403, 'Recovery cases cannot be deleted.');
    }

    public function addCallLog(Request $request, RecoveryCase $recoveryCase)
    {
        $request->validate([
            'phone_number'       => 'required|string|max:20',
            'call_datetime'      => 'required|date',
            'duration_seconds'   => 'nullable|integer|min:0',
            'outcome'            => 'required|in:connected,no_answer,switched_off,invalid_number,refused,agreed_to_return,promised,call_back_later',
            'promise_date'       => 'nullable|date',
            'next_follow_up_date'=> 'nullable|date',
            'remarks'            => 'nullable|string',
        ]);

        CallLog::create([
            'recovery_case_id'    => $recoveryCase->id,
            'device_id'           => $recoveryCase->device_id,
            'employee_id'         => $recoveryCase->employee_id,
            'called_by'           => auth()->id(),
            'phone_number'        => $request->phone_number,
            'call_datetime'       => $request->call_datetime,
            'duration_seconds'    => $request->duration_seconds,
            'outcome'             => $request->outcome,
            'promise_date'        => $request->promise_date,
            'next_follow_up_date' => $request->next_follow_up_date,
            'remarks'             => $request->remarks,
        ]);

        $recoveryCase->increment('follow_up_count');
        $recoveryCase->update([
            'last_follow_up_at'   => now(),
            'next_follow_up_date' => $request->next_follow_up_date,
            'status'              => 'contacted',
        ]);

        return back()->with('success', 'Call log added.');
    }

    public function addFollowUp(Request $request, RecoveryCase $recoveryCase)
    {
        $request->validate([
            'next_follow_up_date' => 'required|date|after:today',
            'remarks'             => 'nullable|string',
        ]);

        $recoveryCase->update([
            'next_follow_up_date' => $request->next_follow_up_date,
            'last_follow_up_at'   => now(),
            'remarks'             => $request->filled('remarks') ? $request->remarks : $recoveryCase->remarks,
        ]);

        return back()->with('success', 'Follow-up scheduled for ' . $request->next_follow_up_date . '.');
    }
}
