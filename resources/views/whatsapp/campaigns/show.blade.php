@extends('layouts.main')
@section('title','Campaign: '.$campaign->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('whatsapp.index') }}">WhatsApp</a></li>
    <li class="breadcrumb-item"><a href="{{ route('whatsapp.campaigns.index') }}">Campaigns</a></li>
    <li class="breadcrumb-item active">{{ $campaign->name }}</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:var(--gs-teal-dark)">{{ $campaign->name }}</h4>
    <div class="d-flex gap-2">
        @if(in_array($campaign->status, ['draft','scheduled']))
        <form method="POST" action="{{ route('whatsapp.campaigns.launch', $campaign) }}">
            @csrf
            <button class="btn btn-success" id="launchBtn" onclick="document.getElementById('launchBtn').disabled=true;document.getElementById('launchBtn').innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span>Launching…';this.closest('form').submit();return false;">
                <i class="bi bi-send me-1"></i>Launch Campaign
            </button>
        </form>
        <form method="POST" action="{{ route('whatsapp.campaigns.cancel', $campaign) }}">
            @csrf
            <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel?')">Cancel</button>
        </form>
        @endif
    </div>
</div>

<div class="row g-4">
    {{-- Stats --}}
    <div class="col-12">
        <div class="row g-3">
            @foreach([
                [$campaign->total_contacts,'Total','bi-people','teal'],
                [$campaign->sent,'Sent','bi-check2-all','success'],
                [$campaign->failed,'Failed','bi-x-circle','danger'],
                [$campaign->delivered,'Delivered','bi-check-circle','info'],
            ] as [$val,$lbl,$ic,$c])
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-3" style="color:var(--gs-teal-dark)">{{ $val }}</div>
                    <div class="text-muted small">{{ $lbl }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Progress bar --}}
    @if($campaign->total_contacts)
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Progress</span>
                    <span>{{ $campaign->progressPercent() }}% · {{ $campaign->sent + $campaign->failed }} / {{ $campaign->total_contacts }} processed</span>
                </div>
                <div class="progress" style="height:10px">
                    <div class="progress-bar" style="width:{{ min($campaign->sent / max($campaign->total_contacts,1) * 100, 100) }}%;background:var(--gs-teal)"></div>
                    <div class="progress-bar bg-danger" style="width:{{ min($campaign->failed / max($campaign->total_contacts,1) * 100, 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Details + contacts --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)">Campaign Info</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm small mb-0">
                    <tr><td class="text-muted ps-3">Status</td>
                        <td><span class="badge bg-{{ $campaign->statusBadgeClass() }}-subtle text-{{ $campaign->statusBadgeClass() }} border">{{ ucfirst($campaign->status) }}</span></td>
                    </tr>
                    <tr><td class="text-muted ps-3">Template</td>
                        <td>
                            @if($campaign->template)
                            <a href="{{ route('whatsapp.templates.show', $campaign->template) }}" class="font-monospace">{{ $campaign->template->name }}</a>
                            @else <span class="text-muted">Custom text</span> @endif
                        </td>
                    </tr>
                    @if($campaign->custom_message)
                    <tr><td class="text-muted ps-3" colspan="2">
                        <div class="p-2 small text-secondary" style="white-space:pre-wrap">{{ $campaign->custom_message }}</div>
                    </td></tr>
                    @endif
                    <tr><td class="text-muted ps-3">Scheduled</td><td>{{ $campaign->scheduled_at?->format('d M Y H:i') ?? 'Immediate' }}</td></tr>
                    <tr><td class="text-muted ps-3">Started</td><td>{{ $campaign->started_at?->format('d M Y H:i') ?? '—' }}</td></tr>
                    <tr><td class="text-muted ps-3">Completed</td><td>{{ $campaign->completed_at?->format('d M Y H:i') ?? '—' }}</td></tr>
                    <tr><td class="text-muted ps-3">Created By</td><td>{{ $campaign->createdBy?->name ?? '—' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Contact list --}}
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)">Contacts ({{ $campaign->total_contacts }})</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm small mb-0 align-middle">
                        <thead class="table-light">
                            <tr><th class="ps-3">Phone</th><th>Name</th><th>Variables</th><th>Status</th><th>Sent At</th><th>Error</th></tr>
                        </thead>
                        <tbody>
                        @foreach($contacts as $contact)
                        <tr>
                            <td class="ps-3 font-monospace">{{ $contact->phone }}</td>
                            <td>{{ $contact->name ?: '—' }}</td>
                            <td class="text-muted">
                                @if($contact->variables)
                                {{ collect($contact->variables)->map(fn($v,$k)=>"$k:$v")->implode(', ') }}
                                @else — @endif
                            </td>
                            <td>
                                @php $sc = match($contact->status) { 'sent'=>'success','failed'=>'danger','skipped'=>'secondary',default=>'warning' }; @endphp
                                <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} border">{{ ucfirst($contact->status) }}</span>
                            </td>
                            <td class="text-muted text-nowrap">{{ $contact->sent_at?->format('H:i:s') ?? '—' }}</td>
                            <td class="text-danger small" style="max-width:150px">
                                <div class="text-truncate" title="{{ $contact->error_message }}">{{ $contact->error_message ?? '' }}</div>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if($contacts->hasPages())
            <div class="card-footer bg-white">{{ $contacts->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
