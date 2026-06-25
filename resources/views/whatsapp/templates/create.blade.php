@extends('layouts.main')
@section('title','New Template')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('whatsapp.index') }}">WhatsApp</a></li>
    <li class="breadcrumb-item"><a href="{{ route('whatsapp.templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item active">New</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-xl-8">
<div class="card border-0 shadow-sm">
    <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
        <h5 class="mb-0 fw-bold" style="color:var(--gs-teal-dark)"><i class="bi bi-file-plus me-2"></i>New Message Template</h5>
    </div>
    <div class="card-body">
        @if($errors->any())
        <div class="alert alert-danger small"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('whatsapp.templates.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control font-monospace" placeholder="e.g. handover_notification"
                        value="{{ old('name') }}" required pattern="[a-z0-9_]+" title="lowercase letters, numbers and underscores only">
                    <div class="form-text">Lowercase, underscores only. Must match Dovesoft exactly.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Category</label>
                    <select name="category" class="form-select">
                        @foreach(['MARKETING','UTILITY','AUTHENTICATION'] as $cat)
                        <option value="{{ $cat }}" @selected(old('category')===$cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Language</label>
                    <select name="language" class="form-select">
                        <option value="en" @selected(old('language','en')==='en')>English (en)</option>
                        <option value="en_US" @selected(old('language')==='en_US')>English US</option>
                        <option value="hi" @selected(old('language')==='hi')>Hindi (hi)</option>
                        <option value="gu" @selected(old('language')==='gu')>Gujarati (gu)</option>
                        <option value="mr" @selected(old('language')==='mr')>Marathi (mr)</option>
                    </select>
                </div>

                {{-- Header --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Header Type</label>
                    <select name="header_type" class="form-select" id="headerType">
                        <option value="">None</option>
                        <option value="TEXT" @selected(old('header_type')==='TEXT')>Text</option>
                        <option value="IMAGE" @selected(old('header_type')==='IMAGE')>Image</option>
                        <option value="VIDEO" @selected(old('header_type')==='VIDEO')>Video</option>
                        <option value="DOCUMENT" @selected(old('header_type')==='DOCUMENT')>Document</option>
                    </select>
                </div>
                <div class="col-md-8" id="headerTextBlock" style="{{ old('header_type')==='TEXT' ? '' : 'display:none' }}">
                    <label class="form-label fw-semibold">Header Text <span class="text-muted">(max 60 chars)</span></label>
                    <input type="text" name="header_text" class="form-control" maxlength="60" value="{{ old('header_text') }}">
                </div>

                {{-- Body --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">Body Text <span class="text-danger">*</span></label>
                    <textarea name="body_text" rows="5" class="form-control" required
                        placeholder="Hello {{1}}, your device {{2}} has been handed over on {{3}}. Contact us for support.">{{ old('body_text') }}</textarea>
                    <div class="form-text">Use <code>{{"{{"}}1{{"}}"}}</code>, <code>{{"{{"}}2{{"}}"}}</code> … for variables</div>
                </div>

                {{-- Footer --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">Footer Text <span class="text-muted">(optional, max 60 chars)</span></label>
                    <input type="text" name="footer_text" class="form-control" maxlength="60" value="{{ old('footer_text') }}"
                        placeholder="GlobalSpace Technologies">
                </div>

                {{-- Variables --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">Variable Names <span class="text-muted">(for campaign CSV mapping)</span></label>
                    <div id="varList" class="d-flex flex-wrap gap-2 mb-2">
                        @foreach(old('variables',[]) as $i => $v)
                        <div class="input-group" style="width:auto">
                            <span class="input-group-text small">{{"{{"}}{{ $i+1 }}{{"}}"}}</span>
                            <input type="text" name="variables[]" class="form-control form-control-sm" value="{{ $v }}" style="width:120px" placeholder="var name">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.input-group').remove()"><i class="bi bi-x"></i></button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addVar">
                        <i class="bi bi-plus me-1"></i>Add Variable
                    </button>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('whatsapp.templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Save & Submit to Dovesoft</button>
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
document.getElementById('headerType').addEventListener('change', function() {
    document.getElementById('headerTextBlock').style.display = this.value === 'TEXT' ? '' : 'none';
});

let varCount = {{ count(old('variables',[])) }};
document.getElementById('addVar').addEventListener('click', function() {
    varCount++;
    const div = document.createElement('div');
    div.className = 'input-group';
    div.style.width = 'auto';
    div.innerHTML = `<span class="input-group-text small">{{${varCount}}}</span>
        <input type="text" name="variables[]" class="form-control form-control-sm" style="width:120px" placeholder="var name">
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.input-group').remove()"><i class="bi bi-x"></i></button>`;
    document.getElementById('varList').appendChild(div);
});
</script>
@endpush
