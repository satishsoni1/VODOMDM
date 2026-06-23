@extends('layouts.main')

@section('title', 'Search — ' . $q)

@section('breadcrumb')
    <li class="breadcrumb-item active">Search Results</li>
@endsection

@section('content')
<h5 class="fw-bold mb-4"><i class="bi bi-search me-2"></i>Results for: <span class="text-primary">{{ $q }}</span></h5>

@if(empty($q) || strlen($q) < 2)
    <div class="alert alert-info">Please enter at least 2 characters to search.</div>
@else

{{-- Devices --}}
@if(!empty($results['devices']) && $results['devices']->isNotEmpty())
<div class="card mb-3">
    <div class="card-header"><strong><i class="bi bi-phone me-1"></i>Devices ({{ $results['devices']->count() }})</strong></div>
    <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>Asset Tag</th><th>Serial</th><th>IMEI 1</th><th>Model</th><th>Employee</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($results['devices'] as $device)
            <tr>
                <td><a href="{{ route('devices.show', $device) }}" class="fw-bold font-monospace">{{ $device->asset_tag }}</a></td>
                <td class="font-monospace small">{{ $device->serial_number }}</td>
                <td class="font-monospace small">{{ $device->imei1 ?? '—' }}</td>
                <td>{{ $device->model?->brand?->name }} {{ $device->model?->model_name }}</td>
                <td>{{ $device->currentEmployee?->name ?? '—' }}</td>
                <td><span class="badge bg-secondary">{{ str_replace('_', ' ', $device->lifecycle_status) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Employees --}}
@if(!empty($results['employees']) && $results['employees']->isNotEmpty())
<div class="card mb-3">
    <div class="card-header"><strong><i class="bi bi-people me-1"></i>Employees ({{ $results['employees']->count() }})</strong></div>
    <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>Emp Code</th><th>Name</th><th>Phone</th><th>Client</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($results['employees'] as $emp)
            <tr>
                <td><a href="{{ route('employees.show', $emp) }}" class="fw-bold">{{ $emp->employee_code }}</a></td>
                <td>{{ $emp->name }}</td>
                <td class="font-monospace small">{{ $emp->phone }}</td>
                <td>{{ $emp->client?->name ?? '—' }}</td>
                <td><span class="badge bg-{{ $emp->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($emp->status) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Tickets --}}
@if(!empty($results['tickets']) && $results['tickets']->isNotEmpty())
<div class="card mb-3">
    <div class="card-header"><strong><i class="bi bi-ticket-perforated me-1"></i>Tickets ({{ $results['tickets']->count() }})</strong></div>
    <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>Ticket #</th><th>Subject</th><th>Device</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($results['tickets'] as $ticket)
            <tr>
                <td><a href="{{ route('tickets.show', $ticket) }}" class="fw-bold">{{ $ticket->ticket_number }}</a></td>
                <td>{{ $ticket->subject }}</td>
                <td class="font-monospace small">{{ $ticket->device?->asset_tag ?? '—' }}</td>
                <td><span class="badge bg-secondary">{{ $ticket->status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Purchase Orders --}}
@if(!empty($results['purchase_orders']) && $results['purchase_orders']->isNotEmpty())
<div class="card mb-3">
    <div class="card-header"><strong><i class="bi bi-bag-check me-1"></i>Purchase Orders</strong></div>
    <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>PO Number</th><th>Vendor</th><th>Value</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($results['purchase_orders'] as $po)
            <tr>
                <td><a href="{{ route('procurement.purchase-orders.show', $po) }}" class="fw-bold">{{ $po->po_number }}</a></td>
                <td>{{ $po->vendor?->name }}</td>
                <td>₹{{ number_format($po->grand_total) }}</td>
                <td><span class="badge bg-secondary">{{ $po->status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Insurance Claims --}}
@if(!empty($results['claims']) && $results['claims']->isNotEmpty())
<div class="card mb-3">
    <div class="card-header"><strong><i class="bi bi-shield me-1"></i>Insurance Claims</strong></div>
    <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>Claim #</th><th>Device</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($results['claims'] as $claim)
            <tr>
                <td class="fw-bold">{{ $claim->claim_number }}</td>
                <td class="font-monospace small">{{ $claim->device?->asset_tag ?? '—' }}</td>
                <td>₹{{ number_format($claim->claimed_amount) }}</td>
                <td><span class="badge bg-secondary">{{ $claim->status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@php
$totalResults = collect($results)->flatten()->count();
@endphp
@if($totalResults === 0)
    <div class="alert alert-warning"><i class="bi bi-info-circle me-1"></i>No results found for "<strong>{{ $q }}</strong>".</div>
@endif

@endif
@endsection
