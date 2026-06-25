@extends('layouts.main')
@section('title','WA Templates')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('whatsapp.index') }}">WhatsApp</a></li>
    <li class="breadcrumb-item active">Templates</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:var(--gs-teal-dark)">
        <i class="bi bi-file-text me-2"></i>Message Templates
    </h4>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('whatsapp.templates.sync') }}">
            @csrf
            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-repeat me-1"></i>Sync from Dovesoft</button>
        </form>
        <a href="{{ route('whatsapp.templates.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New Template
        </a>
    </div>
</div>

<div class="row g-3">
    @forelse($templates as $tpl)
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <div class="fw-bold font-monospace">{{ $tpl->name }}</div>
                        <div class="small text-muted">{{ $tpl->category }} · {{ strtoupper($tpl->language) }}</div>
                    </div>
                    <span class="badge bg-{{ $tpl->statusBadgeClass() }}-subtle text-{{ $tpl->statusBadgeClass() }} border">
                        {{ ucfirst($tpl->status) }}
                    </span>
                </div>

                @if($tpl->header_text)
                <div class="small fw-semibold text-muted mb-1">{{ $tpl->header_text }}</div>
                @endif
                <div class="small text-secondary mb-2" style="white-space:pre-wrap;max-height:80px;overflow:hidden">{{ $tpl->body_text }}</div>
                @if($tpl->footer_text)
                <div class="small text-muted fst-italic">{{ $tpl->footer_text }}</div>
                @endif

                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                    <div class="small text-muted">
                        <i class="bi bi-braces me-1"></i>{{ $tpl->variableCount() }} vars
                        &nbsp;|&nbsp;
                        <i class="bi bi-send me-1"></i>{{ $tpl->campaigns_count }} campaigns
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('whatsapp.templates.show', $tpl) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        <form method="POST" action="{{ route('whatsapp.templates.destroy', $tpl) }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 text-muted">
        <i class="bi bi-file-x fs-1 d-block mb-2 opacity-25"></i>
        No templates yet. <a href="{{ route('whatsapp.templates.create') }}">Create one</a> or sync from Dovesoft.
    </div>
    @endforelse
</div>

@if($templates->hasPages())
<div class="mt-3">{{ $templates->links() }}</div>
@endif
@endsection
