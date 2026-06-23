@extends('client-portal.layout')
@section('title','Support Tickets')
@section('page-title','Support Tickets')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
        <strong style="color:var(--gs-teal-dark)"><i class="bi bi-ticket-perforated me-2"></i>Your Tickets ({{ $tickets->total() }})</strong>
    </div>
    <div class="card-body p-0">
        @if($tickets->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-ticket-perforated fs-1 d-block mb-2 opacity-25"></i>No tickets raised yet.
        </div>
        @else
        <div class="table-responsive">
            <table class="table cp-table table-hover mb-0 small align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">Ticket #</th>
                        <th>Subject</th>
                        <th>Device</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Raised By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    @php
                        $sc = match($ticket->status) {
                            'open'        => 'danger',
                            'in_progress' => 'warning',
                            'resolved'    => 'success',
                            'closed'      => 'secondary',
                            default       => 'secondary'
                        };
                        $pc = match($ticket->priority ?? '') {
                            'critical' => 'danger',
                            'high'     => 'warning',
                            'medium'   => 'info',
                            default    => 'secondary'
                        };
                    @endphp
                    <tr>
                        <td class="ps-3 fw-semibold font-monospace">{{ $ticket->ticket_number }}</td>
                        <td>
                            <div class="fw-semibold" style="color:var(--gs-teal-dark)">{{ $ticket->subject }}</div>
                            @if($ticket->resolution_notes)
                            <div class="text-muted" style="font-size:.72rem">{{ Str::limit($ticket->resolution_notes, 50) }}</div>
                            @endif
                        </td>
                        <td class="font-monospace small text-muted">
                            {{ $ticket->device?->serial_number ?? '—' }}
                        </td>
                        <td>
                            <span class="badge bg-{{ $pc }}-subtle text-{{ $pc }} border" style="font-size:.72rem">
                                {{ ucfirst($ticket->priority ?? 'normal') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} border" style="font-size:.72rem">
                                {{ ucfirst(str_replace('_',' ',$ticket->status)) }}
                            </span>
                        </td>
                        <td>{{ $ticket->raisedBy?->name ?? '—' }}</td>
                        <td class="text-muted text-nowrap">{{ $ticket->created_at?->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @if($tickets->hasPages())
    <div class="card-footer bg-white">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
