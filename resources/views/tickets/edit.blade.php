@extends('layouts.main')
@section('title','Edit Ticket — '.$ticket->ticket_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tickets.show',$ticket) }}">{{ $ticket->ticket_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-6">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-ticket-detailed me-2"></i>Edit Ticket — {{ $ticket->ticket_number }}</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('tickets.update',$ticket) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Priority *</label>
                    <select class="form-select" name="priority" required>
                        @foreach(['low','medium','high','critical'] as $p)
                        <option value="{{ $p }}" {{ $ticket->priority===$p?'selected':'' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Assign To</label>
                    <select class="form-select" name="assigned_to">
                        <option value="">— Unassigned —</option>
                        @foreach($agents as $a)
                        <option value="{{ $a->id }}" {{ $ticket->assigned_to===$a->id?'selected':'' }}>{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12"><label class="form-label">Resolution Notes</label><textarea class="form-control" name="resolution_notes" rows="4">{{ old('resolution_notes',$ticket->resolution_notes) }}</textarea></div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update</button>
                <a href="{{ route('tickets.show',$ticket) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
