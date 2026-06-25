@extends('layouts.main')
@section('title','New Campaign')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('whatsapp.index') }}">WhatsApp</a></li>
    <li class="breadcrumb-item"><a href="{{ route('whatsapp.campaigns.index') }}">Campaigns</a></li>
    <li class="breadcrumb-item active">New</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-xl-8">
<div class="card border-0 shadow-sm">
    <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
        <h5 class="mb-0 fw-bold" style="color:var(--gs-teal-dark)"><i class="bi bi-megaphone me-2"></i>New Campaign</h5>
    </div>
    <div class="card-body">
        @if($errors->any())
        <div class="alert alert-danger small"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('whatsapp.campaigns.store') }}" enctype="multipart/form-data" id="campForm">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Campaign Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. June Device Handover Notifications"
                        value="{{ old('name') }}" required>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Message Source</label>
                    <div class="d-flex gap-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="msg_source" id="srcTemplate" value="template"
                                {{ old('template_id') || request('template') ? 'checked' : '' }}>
                            <label class="form-check-label" for="srcTemplate">Approved Template</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="msg_source" id="srcCustom" value="custom"
                                {{ !old('template_id') && !request('template') ? 'checked' : '' }}>
                            <label class="form-check-label" for="srcCustom">Custom Text Message</label>
                        </div>
                    </div>
                    <div id="templateBlock">
                        <select name="template_id" class="form-select">
                            <option value="">— Select Template —</option>
                            @foreach($templates as $t)
                            <option value="{{ $t->id }}" @selected(old('template_id', request('template'))==$t->id)>
                                {{ $t->name }} ({{ $t->language }})
                            </option>
                            @endforeach
                        </select>
                        @if($templates->isEmpty())
                        <div class="form-text text-warning"><i class="bi bi-exclamation-triangle me-1"></i>No approved templates. <a href="{{ route('whatsapp.templates.create') }}">Create one</a> or sync from Dovesoft.</div>
                        @endif
                    </div>
                    <div id="customBlock" class="d-none">
                        <textarea name="custom_message" rows="4" class="form-control"
                            placeholder="Type your message here…">{{ old('custom_message') }}</textarea>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Contacts CSV <span class="text-danger">*</span></label>
                    <input type="file" name="contacts_csv" class="form-control" accept=".csv,.txt" required>
                    <div class="form-text">
                        Required columns: <code>phone</code>, <code>name</code>, then one column per template variable
                        (e.g. <code>phone,name,var1,var2</code>). First row = headers.
                    </div>
                    <div class="mt-2">
                        <a href="#" onclick="downloadSample(event)" class="small text-primary">
                            <i class="bi bi-download me-1"></i>Download sample CSV
                        </a>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="schedToggle">
                        <label class="form-check-label fw-semibold" for="schedToggle">Schedule for later</label>
                    </div>
                    <div id="schedBlock" class="d-none">
                        <input type="datetime-local" name="scheduled_at" class="form-control" style="max-width:300px"
                            value="{{ old('scheduled_at') }}">
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('whatsapp.campaigns.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success" id="createBtn">
                        <i class="bi bi-upload me-1"></i>Upload & Create Campaign
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
function toggleSource() {
    const isTemplate = document.getElementById('srcTemplate').checked;
    document.getElementById('templateBlock').classList.toggle('d-none', !isTemplate);
    document.getElementById('customBlock').classList.toggle('d-none', isTemplate);
    document.querySelector('[name=template_id]').required = isTemplate;
    document.querySelector('[name=custom_message]').required = !isTemplate;
}
document.getElementById('srcTemplate').addEventListener('change', toggleSource);
document.getElementById('srcCustom').addEventListener('change', toggleSource);
toggleSource();

document.getElementById('schedToggle').addEventListener('change', function() {
    document.getElementById('schedBlock').classList.toggle('d-none', !this.checked);
});

document.getElementById('campForm').addEventListener('submit', function() {
    document.getElementById('createBtn').disabled = true;
    document.getElementById('createBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading…';
});

function downloadSample(e) {
    e.preventDefault();
    const csv = 'phone,name,var1,var2\n919999999999,John Doe,Device123,2026-01-01\n918888888888,Jane Smith,TabletXYZ,2026-01-02\n';
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'campaign_contacts_sample.csv';
    a.click();
}
</script>
@endpush
