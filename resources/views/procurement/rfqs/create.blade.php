@extends('layouts.main')
@section('title','New RFQ')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procurement.rfqs') }}">RFQs</a></li>
    <li class="breadcrumb-item active">New RFQ</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-9">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-envelope-paper me-2"></i>New Request for Quotation</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('procurement.rfqs.store') }}">
            @csrf
            <div class="row g-3">
                @if($selectedDemand)
                <input type="hidden" name="demand_request_id" value="{{ $selectedDemand->id }}">
                <div class="col-12">
                    <div class="alert alert-info d-flex align-items-center gap-2 py-2 mb-0">
                        <i class="bi bi-link-45deg fs-5"></i>
                        <div>Linked to Demand Request <strong>{{ $selectedDemand->request_number }}</strong> — {{ $selectedDemand->client?->name }}</div>
                    </div>
                </div>
                @else
                <div class="col-md-6">
                    <label class="form-label">Demand Request (optional)</label>
                    <select class="form-select" name="demand_request_id">
                        <option value="">— None —</option>
                        @foreach($demands as $dr)
                        <option value="{{ $dr->id }}" {{ old('demand_request_id')==$dr->id?'selected':'' }}>{{ $dr->request_number }} — {{ $dr->client?->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3"><label class="form-label">Response Deadline</label><input type="date" class="form-control" name="response_deadline" value="{{ old('response_deadline') }}"></div>
                <div class="col-md-3"><label class="form-label">Quantity Required *</label><input type="number" class="form-control @error('quantity') is-invalid @enderror" name="quantity" value="{{ old('quantity', $selectedDemand?->quantity ?? 1) }}" min="1" required>@error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label class="form-label">Device Specification *</label><textarea class="form-control @error('device_specification') is-invalid @enderror" name="device_specification" rows="4" required placeholder="Technical requirements to be sent to vendors — model preferences, OS, storage, accessories...">{{ old('device_specification', $selectedDemand?->device_specification) }}</textarea>@error('device_specification')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label class="form-label">Terms &amp; Conditions</label><textarea class="form-control" name="terms" rows="2" placeholder="Payment terms, delivery terms, warranty requirements, etc.">{{ old('terms') }}</textarea></div>

                <div class="col-12">
                    <label class="form-label fw-medium">Vendors to Invite *</label>
                    @error('vendor_ids')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                    <div class="row g-2">
                        @foreach($vendors as $vendor)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="vendor_ids[]" value="{{ $vendor->id }}" id="v{{ $vendor->id }}"
                                    {{ in_array($vendor->id, old('vendor_ids',[])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="v{{ $vendor->id }}">
                                    <span class="fw-medium">{{ $vendor->name }}</span>
                                    <span class="text-muted small d-block">{{ $vendor->code }}</span>
                                </label>
                            </div>
                        </div>
                        @endforeach
                        @if($vendors->isEmpty())
                        <div class="col-12 text-muted small">No active vendors found. <a href="{{ route('vendors.create') }}">Add a vendor</a> first.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" name="action" value="save" class="btn btn-outline-secondary"><i class="bi bi-save"></i> Save Draft</button>
                <button type="submit" name="action" value="send" class="btn btn-primary"><i class="bi bi-send"></i> Send to Vendors</button>
                <a href="{{ route('procurement.rfqs') }}" class="btn btn-link text-muted">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
