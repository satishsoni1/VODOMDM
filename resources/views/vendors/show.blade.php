@extends('layouts.main')
@section('title','Vendor — '.$vendor->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vendors.index') }}">Vendors</a></li>
    <li class="breadcrumb-item active">{{ $vendor->name }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-building me-2"></i>{{ $vendor->name }}
        <span class="badge bg-secondary ms-2">{{ $vendor->code }}</span>
        <span class="badge bg-{{ $vendor->status==='active'?'success':($vendor->status==='blacklisted'?'danger':'secondary') }} ms-1">{{ ucfirst($vendor->status) }}</span>
    </h5>
    <div class="d-flex gap-2">
        <a href="{{ route('vendors.edit',$vendor) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('procurement.rfqs.create', ['vendor_id'=>$vendor->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-envelope"></i> Send RFQ</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><strong>Details</strong></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted w-45">GSTIN</td><td>{{ $vendor->gstin ?? '—' }}</td></tr>
                    <tr><td class="text-muted">PAN</td><td>{{ $vendor->pan ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Phone</td><td>{{ $vendor->phone ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Alt Phone</td><td>{{ $vendor->alternate_phone ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Email</td><td>{{ $vendor->email ?? '—' }}</td></tr>
                    <tr><td class="text-muted">City</td><td>{{ $vendor->city }}, {{ $vendor->state }}</td></tr>
                    <tr><td class="text-muted">Payment</td><td>{{ $vendor->payment_terms ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Credit Limit</td><td>₹{{ number_format($vendor->credit_limit) }}</td></tr>
                    <tr><td class="text-muted">Performance</td><td>
                        <span class="badge bg-{{ $vendor->performance_score >= 80 ? 'success' : ($vendor->performance_score >= 50 ? 'warning text-dark' : 'danger') }}">
                            {{ $vendor->performance_score }}/100
                        </span>
                    </td></tr>
                </table>
                @if($vendor->notes)
                    <div class="mt-2 text-muted small">{{ $vendor->notes }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        {{-- Contacts --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Contacts</strong>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                    <i class="bi bi-plus"></i> Add
                </button>
            </div>
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light"><tr><th>Name</th><th>Designation</th><th>Phone</th><th>Email</th><th>Primary</th><th></th></tr></thead>
                <tbody>
                    @forelse($vendor->contacts as $contact)
                    <tr>
                        <td>{{ $contact->name }}</td>
                        <td class="text-muted small">{{ $contact->designation }}</td>
                        <td class="font-monospace small">{{ $contact->phone }}</td>
                        <td class="small">{{ $contact->email }}</td>
                        <td>@if($contact->is_primary)<span class="badge bg-success">Primary</span>@endif</td>
                        <td>
                            <form method="POST" action="{{ route('vendors.contacts.destroy', [$vendor, $contact]) }}" onsubmit="return confirm('Remove contact?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger btn-sm py-0 px-1"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-2">No contacts. Add one above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Recent POs --}}
        @if($vendor->purchaseOrders->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header"><strong>Recent Purchase Orders</strong></div>
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light"><tr><th>PO #</th><th>Date</th><th>Qty</th><th>Value</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($vendor->purchaseOrders as $po)
                    <tr>
                        <td><a href="{{ route('procurement.purchase-orders.show',$po) }}" class="fw-bold">{{ $po->po_number }}</a></td>
                        <td>{{ $po->po_date->format('d M Y') }}</td>
                        <td>{{ $po->quantity }}</td>
                        <td>₹{{ number_format($po->grand_total) }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($po->status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Add Contact Modal --}}
<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Contact</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="{{ route('vendors.contacts.store',$vendor) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name *</label><input class="form-control" name="name" required></div>
                    <div class="mb-3"><label class="form-label">Designation</label><input class="form-control" name="designation"></div>
                    <div class="mb-3"><label class="form-label">Phone</label><input class="form-control" name="phone"></div>
                    <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email"></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="is_primary" value="1"><label class="form-check-label">Set as primary contact</label></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Add Contact</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
