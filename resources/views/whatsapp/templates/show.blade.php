@extends('layouts.main')
@section('title','Template: '.$template->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('whatsapp.index') }}">WhatsApp</a></li>
    <li class="breadcrumb-item"><a href="{{ route('whatsapp.templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item active font-monospace">{{ $template->name }}</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center justify-content-between" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)">Template Preview</strong>
                <span class="badge bg-{{ $template->statusBadgeClass() }}-subtle text-{{ $template->statusBadgeClass() }} border">{{ ucfirst($template->status) }}</span>
            </div>
            <div class="card-body">
                {{-- WhatsApp bubble mock --}}
                <div class="rounded p-3 mx-auto" style="max-width:360px;background:#e2ffc7;box-shadow:0 1px 3px rgba(0,0,0,.15)">
                    @if($template->header_text)
                    <div class="fw-bold mb-1 small">{{ $template->header_text }}</div>
                    @endif
                    <div class="small" style="white-space:pre-wrap">{{ $template->body_text }}</div>
                    @if($template->footer_text)
                    <div class="text-muted mt-1" style="font-size:.72rem">{{ $template->footer_text }}</div>
                    @endif
                    <div class="text-end text-muted mt-1" style="font-size:.65rem">{{ now()->format('H:i') }} ✓✓</div>
                </div>
            </div>
        </div>

        {{-- Campaigns using this template --}}
        @if($template->campaigns->count())
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)">Campaigns Using This Template</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm small mb-0">
                    <thead class="table-light"><tr><th class="ps-3">Campaign</th><th>Status</th><th>Sent</th><th>Created</th></tr></thead>
                    <tbody>
                    @foreach($template->campaigns as $c)
                    <tr>
                        <td class="ps-3 fw-semibold"><a href="{{ route('whatsapp.campaigns.show', $c) }}">{{ $c->name }}</a></td>
                        <td><span class="badge bg-{{ $c->statusBadgeClass() }}-subtle text-{{ $c->statusBadgeClass() }} border">{{ ucfirst($c->status) }}</span></td>
                        <td>{{ $c->sent }} / {{ $c->total_contacts }}</td>
                        <td class="text-muted">{{ $c->created_at->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="col-xl-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)">Details</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm small mb-0">
                    <tr><td class="text-muted ps-3">Name</td><td class="font-monospace fw-semibold">{{ $template->name }}</td></tr>
                    <tr><td class="text-muted ps-3">Dovesoft ID</td><td class="font-monospace">{{ $template->dovesoft_id ?? '—' }}</td></tr>
                    <tr><td class="text-muted ps-3">Category</td><td>{{ $template->category }}</td></tr>
                    <tr><td class="text-muted ps-3">Language</td><td>{{ $template->language }}</td></tr>
                    <tr><td class="text-muted ps-3">Header</td><td>{{ $template->header_type ?? 'None' }}</td></tr>
                    <tr><td class="text-muted ps-3">Variables</td>
                        <td>
                            @foreach($template->variables ?? [] as $i => $v)
                            <code>{{"{{"}}{{ $i+1 }}{{"}}"}}</code>=<span class="text-muted">{{ $v }}</span>
                            @endforeach
                            @if(!$template->variables) <span class="text-muted">—</span> @endif
                        </td>
                    </tr>
                    <tr><td class="text-muted ps-3">Created</td><td>{{ $template->created_at->format('d M Y') }}</td></tr>
                    @if($template->reject_reason)
                    <tr><td class="text-muted ps-3" colspan="2">
                        <div class="text-danger small p-2"><i class="bi bi-exclamation-triangle me-1"></i>{{ $template->reject_reason }}</div>
                    </td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <a href="{{ route('whatsapp.campaigns.create') }}?template={{ $template->id }}" class="btn btn-success w-100 mb-2">
                    <i class="bi bi-megaphone me-1"></i>Create Campaign with this Template
                </a>
                <form method="POST" action="{{ route('whatsapp.templates.destroy', $template) }}" onsubmit="return confirm('Delete this template?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100 btn-sm">Delete Template</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
