<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['device.model.brand', 'employee', 'client', 'category', 'assignedTo'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $tickets = $query->paginate(20)->withQueryString();
        $clients = Client::where('status', 'active')->orderBy('name')->get();

        $stats = [
            'open'        => Ticket::whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
            'sla_breached'=> Ticket::whereNotIn('status', ['resolved', 'closed'])->where('sla_due_at', '<', now())->count(),
            'resolved'    => Ticket::whereIn('status', ['resolved', 'closed'])->whereDate('resolved_at', today())->count(),
            'critical'    => Ticket::where('priority', 'critical')->whereNotIn('status', ['resolved', 'closed', 'cancelled'])->count(),
        ];

        return view('tickets.index', compact('tickets', 'clients', 'stats'));
    }

    public function create(Request $request)
    {
        $clients    = Client::with('projects')->where('status', 'active')->orderBy('name')->get();
        $employees  = Employee::where('status', 'active')->orderBy('name')->get();
        $categories = TicketCategory::where('is_active', true)->orderBy('name')->get();
        $devices    = Device::with('model.brand')->whereNotIn('lifecycle_status', ['disposed', 'lost'])->orderBy('asset_tag')->get();
        $agents     = User::orderBy('name')->get();

        $selectedDevice = $request->filled('device_id') ? Device::with(['model.brand', 'currentEmployee.client'])->find($request->device_id) : null;

        return view('tickets.create', compact('clients', 'employees', 'categories', 'devices', 'agents', 'selectedDevice'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id'          => 'nullable|exists:devices,id',
            'employee_id'        => 'nullable|exists:employees,id',
            'client_id'          => 'nullable|exists:clients,id',
            'ticket_category_id' => 'required|exists:ticket_categories,id',
            'subject'            => 'required|string|max:300',
            'description'        => 'required|string',
            'priority'           => 'required|in:low,medium,high,critical',
            'assigned_to'        => 'nullable|exists:users,id',
        ]);

        $category = TicketCategory::find($validated['ticket_category_id']);
        $slaHours = $category?->sla_hours;

        $ticket = Ticket::create([
            'ticket_number'      => 'TKT-' . strtoupper(Str::random(8)),
            'device_id'          => $validated['device_id'] ?? null,
            'employee_id'        => $validated['employee_id'] ?? null,
            'client_id'          => $validated['client_id'] ?? null,
            'ticket_category_id' => $validated['ticket_category_id'],
            'raised_by'          => auth()->id(),
            'assigned_to'        => $validated['assigned_to'] ?? null,
            'subject'            => $validated['subject'],
            'description'        => $validated['description'],
            'priority'           => $validated['priority'],
            'status'             => $validated['assigned_to'] ? 'assigned' : 'open',
            'sla_due_at'         => $slaHours ? now()->addHours($slaHours) : null,
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket ' . $ticket->ticket_number . ' created.');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['device.model.brand', 'employee', 'client', 'category', 'raisedBy', 'assignedTo', 'comments.user', 'repairOrders']);

        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $agents = User::orderBy('name')->get();
        return view('tickets.edit', compact('ticket', 'agents'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'assigned_to'      => 'nullable|exists:users,id',
            'priority'         => 'required|in:low,medium,high,critical',
            'resolution_notes' => 'nullable|string',
        ]);

        $ticket->update($request->only(['assigned_to', 'priority', 'resolution_notes']));

        return back()->with('success', 'Ticket updated.');
    }

    public function destroy(Ticket $ticket)
    {
        abort(403, 'Tickets cannot be deleted.');
    }

    public function addComment(Request $request, Ticket $ticket)
    {
        $request->validate([
            'comment'     => 'required|string',
            'is_internal' => 'sometimes|boolean',
        ]);

        $ticket->comments()->create([
            'user_id'     => auth()->id(),
            'comment'     => $request->comment,
            'is_internal' => $request->boolean('is_internal'),
        ]);

        if (!$ticket->first_response_at) {
            $ticket->update(['first_response_at' => now()]);
        }

        return back()->with('success', 'Comment added.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status'           => 'required|in:open,assigned,in_progress,pending_user,resolved,closed,cancelled',
            'resolution_notes' => 'nullable|string',
        ]);

        $data = ['status' => $request->status];

        if (in_array($request->status, ['resolved', 'closed']) && !$ticket->resolved_at) {
            $data['resolved_at']      = now();
            $data['resolution_hours'] = round($ticket->created_at->diffInMinutes(now()) / 60, 1);
        }
        if ($request->status === 'closed') {
            $data['closed_at'] = now();
        }
        if ($request->filled('resolution_notes')) {
            $data['resolution_notes'] = $request->resolution_notes;
        }

        $ticket->update($data);

        return back()->with('success', 'Ticket status updated to ' . ucwords(str_replace('_', ' ', $request->status)) . '.');
    }
}
