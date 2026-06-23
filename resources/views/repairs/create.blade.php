@extends('layouts.main')
@section('title','New Repair Order')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('repairs.index') }}">Repairs</a></li>
    <li class="breadcrumb-item active">New Repair Order</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
<div class="card">
    <div class="card-header"><strong><i class="bi bi-tools me-2"></i>New Repair / RMA Order</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('repairs.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Device *</label>
                    <select class="form-select @error('device_id') is-invalid @enderror" name="device_id" required>
                        <option value="">— Select Device —</option>
                        @foreach($devices as $dev)
                        <option value="{{ $dev->id }}" {{ old('device_id', $selectedDevice?->id)==$dev->id?'selected':'' }}>
                            {{ $dev->asset_tag }} — {{ $dev->model?->brand?->name }} {{ $dev->model?->model_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('device_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Service Centre *</label>
                    <select class="form-select @error('service_center_id') is-invalid @enderror" name="service_center_id" required>
                        <option value="">— Select Service Centre —</option>
                        @foreach($serviceCenters as $sc)
                        <option value="{{ $sc->id }}" {{ old('service_center_id')==$sc->id?'selected':'' }}>{{ $sc->name }} ({{ $sc->city }})</option>
                        @endforeach
                    </select>
                    @error('service_center_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Linked Ticket</label>
                    <select class="form-select" name="ticket_id">
                        <option value="">— None —</option>
                        @foreach($openTickets as $t)
                        <option value="{{ $t->id }}" {{ old('ticket_id', $selectedTicket?->id)==$t->id?'selected':'' }}>{{ $t->ticket_number }} — {{ Str::limit($t->subject,50) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Linked Insurance Claim</label>
                    <select class="form-select" name="insurance_claim_id">
                        <option value="">— None —</option>
                        @foreach($openClaims as $cl)
                        <option value="{{ $cl->id }}" {{ old('insurance_claim_id')==$cl->id?'selected':'' }}>{{ $cl->claim_number }} — {{ $cl->incident_type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Repair Type *</label>
                    <select class="form-select @error('repair_type') is-invalid @enderror" name="repair_type" required>
                        @foreach(['warranty','paid','insurance'] as $t)
                        <option value="{{ $t }}" {{ old('repair_type')===$t?'selected':'' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                    @error('repair_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4"><label class="form-label">Sent Date *</label><input type="date" class="form-control @error('sent_date') is-invalid @enderror" name="sent_date" value="{{ old('sent_date', now()->format('Y-m-d')) }}" required>@error('sent_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-4"><label class="form-label">Est. Return Date</label><input type="date" class="form-control" name="estimated_return_date" value="{{ old('estimated_return_date') }}"></div>
                <div class="col-md-4"><label class="form-label">Estimated Cost (₹)</label><input type="number" class="form-control" name="estimated_cost" value="{{ old('estimated_cost') }}" step="0.01" min="0"></div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="under_warranty" value="1" id="warrantyCheck" class="form-check-input" {{ old('under_warranty') ? 'checked' : '' }}>
                        <label class="form-check-label" for="warrantyCheck">Under Manufacturer Warranty</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Fault Description *</label>
                    <input class="form-control @error('fault_description') is-invalid @enderror" name="fault_description" value="{{ old('fault_description') }}" required placeholder="Brief fault summary">
                    @error('fault_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12"><label class="form-label">Detailed Notes</label><textarea class="form-control" name="detailed_notes" rows="4" placeholder="Detailed description, steps to reproduce, previous repair history…">{{ old('detailed_notes') }}</textarea></div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Repair Order</button>
                <a href="{{ route('repairs.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
