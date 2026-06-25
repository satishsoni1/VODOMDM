@extends('layouts.main')
@section('title','WhatsApp Messages')
@section('breadcrumb')
    <li class="breadcrumb-item active">WhatsApp</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:var(--gs-teal-dark)">
        <i class="bi bi-whatsapp me-2" style="color:#25d366"></i>WhatsApp Messages
    </h4>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('whatsapp.process-due') }}">
            @csrf
            <button class="btn btn-sm btn-outline-success"><i class="bi bi-send me-1"></i>Process Due</button>
        </form>
        <a href="{{ route('whatsapp.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Compose
        </a>
        <a href="{{ route('whatsapp.settings') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-gear me-1"></i>Settings
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Pending','pending','warning','bi-clock'],
        ['Sent','sent','success','bi-check2-all'],
        ['Failed','failed','danger','bi-x-circle'],
        ['Inbound','inbound','info','bi-reply-all'],
    ] as [$label,$key,$color,$icon])
    <div class="col-6 col-md-3">
        <div @class(['card border-0 shadow-sm', 'border-start border-3 border-info' => $key==='inbound'])>
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                    style="width:40px;height:40px;background:var(--gs-teal-light)">
                    <i class="bi {{ $icon }}" style="font-size:1.1rem;color:var(--gs-teal)"></i>
                </div>
                <div>
                    <div class="fw-bold fs-5">{{ $stats[$key] }}</div>
                    <div class="text-muted small">
                        @if($key==='inbound')
                        <a href="{{ route('whatsapp.index', ['tab'=>'inbound']) }}" class="text-decoration-none">{{ $label }}</a>
                        @else {{ $label }} @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $tab==='outbound' ? 'active' : '' }}" href="{{ route('whatsapp.index', ['tab'=>'outbound']) }}">
            <i class="bi bi-send me-1"></i>Outbound
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab==='inbound' ? 'active' : '' }}" href="{{ route('whatsapp.index', ['tab'=>'inbound']) }}">
            <i class="bi bi-reply-all me-1"></i>Inbox <span class="badge bg-info-subtle text-info border ms-1">{{ $stats['inbound'] }}</span>
        </a>
    </li>
</ul>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if($tab === 'outbound')
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    @foreach(['pending','sent','failed','scheduled','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-auto">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Phone / name…" value="{{ request('q') }}">
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-primary">Search</button></div>
            @if(request()->hasAny(['status','q']))
            <div class="col-auto"><a href="{{ route('whatsapp.index', ['tab'=>$tab]) }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
            @endif
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($messages->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-chat-dots fs-1 d-block mb-2 opacity-25"></i>
            @if($tab === 'inbound') No incoming messages yet. Configure webhook in Dovesoft portal.
            @else No messages yet. <a href="{{ route('whatsapp.create') }}">Compose one</a>.
            @endif
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover small align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">{{ $tab==='inbound' ? 'From' : 'To' }}</th>
                        <th>Message</th>
                        @if($tab==='outbound') <th>Template / Campaign</th> @endif
                        @if($tab==='outbound') <th>Event</th> @endif
                        <th>Time</th>
                        <th>Status</th>
                        @if($tab==='outbound') <th></th> @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $msg)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold">{{ $msg->to_name ?: '—' }}</div>
                            <div class="text-muted font-monospace" style="font-size:.75rem">{{ $msg->to_phone }}</div>
                        </td>
                        <td style="max-width:280px">
                            <div class="text-truncate" title="{{ $msg->message_text }}">{{ $msg->message_text }}</div>
                        </td>
                        @if($tab==='outbound')
                        <td class="small text-muted">
                            @if($msg->campaign)
                            <a href="{{ route('whatsapp.campaigns.show', $msg->campaign) }}" class="text-decoration-none">
                                <i class="bi bi-megaphone me-1"></i>{{ $msg->campaign->name }}
                            </a>
                            @elseif($msg->template_name)
                            <i class="bi bi-file-text me-1"></i>{{ $msg->template_name }}
                            @else — @endif
                        </td>
                        <td>
                            @if($msg->trigger_event)
                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size:.7rem">
                                {{ \App\Models\WhatsAppMessage::triggerEventLabel($msg->trigger_event) }}
                            </span>
                            @else <span class="text-muted">Manual</span> @endif
                        </td>
                        @endif
                        <td class="text-nowrap text-muted">
                            {{ $msg->sent_at?->format('d M H:i') ?? $msg->created_at->format('d M H:i') }}
                        </td>
                        <td>
                            <span class="badge bg-{{ $msg->statusBadgeClass() }}-subtle text-{{ $msg->statusBadgeClass() }} border">
                                {{ ucfirst($msg->status) }}
                            </span>
                        </td>
                        @if($tab==='outbound')
                        <td class="pe-3">
                            @if(in_array($msg->status, ['pending','scheduled']))
                            <div class="d-flex gap-1">
                                <form method="POST" action="{{ route('whatsapp.send', $msg) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" title="Send now"><i class="bi bi-send"></i></button>
                                </form>
                                <form method="POST" action="{{ route('whatsapp.cancel', $msg) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger" title="Cancel" onclick="return confirm('Cancel?')"><i class="bi bi-x-lg"></i></button>
                                </form>
                            </div>
                            @elseif($msg->error_message)
                            <span class="text-danger" title="{{ $msg->error_message }}" style="cursor:help"><i class="bi bi-info-circle"></i></span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @if($messages->hasPages())
    <div class="card-footer bg-white">{{ $messages->links() }}</div>
    @endif
</div>
@endsection
