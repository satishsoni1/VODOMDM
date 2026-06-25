@extends('layouts.main')
@section('title','Compose WhatsApp')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('whatsapp.index') }}">WhatsApp</a></li>
    <li class="breadcrumb-item active">Compose</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-7 col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <h5 class="mb-0 fw-bold" style="color:var(--gs-teal-dark)">
                    <i class="bi bi-whatsapp me-2" style="color:#25d366"></i>Compose Message
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('whatsapp.store') }}" id="composeForm">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="text" name="to_phone" class="form-control @error('to_phone') is-invalid @enderror"
                                    placeholder="10-digit number" value="{{ old('to_phone') }}" required maxlength="12">
                            </div>
                            @error('to_phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Recipient Name</label>
                            <input type="text" name="to_name" class="form-control" placeholder="Optional display name"
                                value="{{ old('to_name') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                            <textarea name="message_text" rows="4" class="form-control @error('message_text') is-invalid @enderror"
                                placeholder="Type your message here…" required>{{ old('message_text') }}</textarea>
                            @error('message_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="d-flex justify-content-between mt-1">
                                <div class="text-muted small">Max 4096 characters</div>
                                <div class="text-muted small" id="charCount">0 / 4096</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Template Name</label>
                            <input type="text" name="template_name" class="form-control" placeholder="e.g. handover_notification"
                                value="{{ old('template_name') }}">
                            <div class="form-text">Optional — for tracking/reporting only</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Trigger Event</label>
                            <select name="trigger_event" class="form-select">
                                <option value="">Manual (no event)</option>
                                @foreach(\App\Models\WhatsAppMessage::allTriggerEvents() as $val => $label)
                                <option value="{{ $val }}" @selected(old('trigger_event')===$val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="scheduleToggle" value="1">
                                <label class="form-check-label fw-semibold" for="scheduleToggle">Schedule for later</label>
                            </div>
                        </div>

                        <div class="col-12 d-none" id="scheduleBlock">
                            <label class="form-label fw-semibold">Send At</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control @error('scheduled_at') is-invalid @enderror"
                                value="{{ old('scheduled_at') }}">
                            @error('scheduled_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if($errors->any())
                        <div class="col-12">
                            <div class="alert alert-danger small mb-0">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                                </ul>
                            </div>
                        </div>
                        @endif

                        <div class="col-12 d-flex gap-2 justify-content-end pt-1">
                            <a href="{{ route('whatsapp.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success" id="sendBtn">
                                <i class="bi bi-send me-1"></i><span id="sendBtnText">Send Now</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Quick info --}}
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body py-2 px-3">
                <div class="row g-2 small text-muted">
                    <div class="col-md-4">
                        <i class="bi bi-info-circle me-1" style="color:var(--gs-teal)"></i>
                        Driver: <strong>{{ config('whatsapp.driver', 'log') }}</strong>
                    </div>
                    <div class="col-md-8">
                        <i class="bi bi-telephone me-1" style="color:var(--gs-teal)"></i>
                        10-digit numbers are automatically prefixed with 91
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const textarea   = document.querySelector('textarea[name=message_text]');
const charCount  = document.getElementById('charCount');
const schedCheck = document.getElementById('scheduleToggle');
const schedBlock = document.getElementById('scheduleBlock');
const sendBtn    = document.getElementById('sendBtn');
const sendBtnTxt = document.getElementById('sendBtnText');

textarea.addEventListener('input', () => {
    charCount.textContent = textarea.value.length + ' / 4096';
});

schedCheck.addEventListener('change', () => {
    schedBlock.classList.toggle('d-none', !schedCheck.checked);
    sendBtnTxt.textContent = schedCheck.checked ? 'Schedule' : 'Send Now';
    schedBlock.querySelector('input').name = schedCheck.checked ? 'scheduled_at' : '_scheduled_at';
});

document.getElementById('composeForm').addEventListener('submit', function() {
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + sendBtnTxt.textContent + '…';
});
</script>
@endpush
