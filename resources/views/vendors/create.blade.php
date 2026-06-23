@extends('layouts.main')
@section('title','Add Vendor')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vendors.index') }}">Vendors</a></li>
    <li class="breadcrumb-item active">Add Vendor</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-xl-9">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-building me-2"></i>New Vendor</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('vendors.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                    <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vendor Code <span class="text-danger">*</span></label>
                    <input class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" placeholder="e.g. VND-002" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">GSTIN</label>
                    <input class="form-control" name="gstin" value="{{ old('gstin') }}" placeholder="22AAAAA0000A1Z5">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact Person</label>
                    <input class="form-control" name="contact_person" value="{{ old('contact_person') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input class="form-control" name="phone" value="{{ old('phone') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Alternate Phone</label>
                    <input class="form-control" name="alternate_phone" value="{{ old('alternate_phone') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" name="email" value="{{ old('email') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">PAN</label>
                    <input class="form-control" name="pan" value="{{ old('pan') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" name="address" rows="2">{{ old('address') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input class="form-control" name="city" value="{{ old('city') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input class="form-control" name="state" value="{{ old('state') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pincode</label>
                    <input class="form-control" name="pincode" value="{{ old('pincode') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Terms</label>
                    <input class="form-control" name="payment_terms" value="{{ old('payment_terms') }}" placeholder="e.g. Net 30">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Credit Limit (₹)</label>
                    <input class="form-control" type="number" name="credit_limit" value="{{ old('credit_limit', 0) }}" min="0">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea>
                </div>

                <div class="col-12 border-top pt-3">
                    <h6 class="fw-bold mb-3">Contacts</h6>
                    <div id="contacts-wrapper">
                        <div class="row g-2 mb-2 contact-row">
                            <div class="col-md-3"><input class="form-control form-control-sm" name="contacts[0][name]" placeholder="Name"></div>
                            <div class="col-md-3"><input class="form-control form-control-sm" name="contacts[0][designation]" placeholder="Designation"></div>
                            <div class="col-md-2"><input class="form-control form-control-sm" name="contacts[0][phone]" placeholder="Phone"></div>
                            <div class="col-md-3"><input class="form-control form-control-sm" name="contacts[0][email]" placeholder="Email" type="email"></div>
                            <div class="col-md-1 d-flex align-items-center"><div class="form-check"><input class="form-check-input" type="checkbox" name="contacts[0][is_primary]" value="1" checked><label class="form-check-label small">Primary</label></div></div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addContactRow()"><i class="bi bi-plus"></i> Add Contact</button>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Vendor</button>
                <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
let contactIdx = 1;
function addContactRow() {
    const wrapper = document.getElementById('contacts-wrapper');
    const i = contactIdx++;
    wrapper.insertAdjacentHTML('beforeend', `
        <div class="row g-2 mb-2 contact-row">
            <div class="col-md-3"><input class="form-control form-control-sm" name="contacts[${i}][name]" placeholder="Name"></div>
            <div class="col-md-3"><input class="form-control form-control-sm" name="contacts[${i}][designation]" placeholder="Designation"></div>
            <div class="col-md-2"><input class="form-control form-control-sm" name="contacts[${i}][phone]" placeholder="Phone"></div>
            <div class="col-md-3"><input class="form-control form-control-sm" name="contacts[${i}][email]" placeholder="Email" type="email"></div>
            <div class="col-md-1 d-flex align-items-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.contact-row').remove()"><i class="bi bi-trash"></i></button></div>
        </div>`);
}
</script>
@endpush
