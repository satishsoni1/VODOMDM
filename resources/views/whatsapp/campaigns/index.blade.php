@extends('layouts.main')
@section('title','WA Campaigns')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('whatsapp.index') }}">WhatsApp</a></li>
    <li class="breadcrumb-item active">Campaigns</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:var(--gs-teal-dark)"><i class="bi bi-megaphone me-2"></i>Campaigns</h4>
    <a href="{{ route('whatsapp.campaigns.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Campaign
    </a>
</div>

<div class="row g-3 mb-4">
    @foreach([['Total','total','bi-list-ul','teal'],['Running','running','bi-arrow-repeat','primary'],['Completed','completed','bi-check-circle','success'],['Scheduled','scheduled','bi-calendar','info']] as [$l,$k,$ic,$c])
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:var(--gs-teal-light)">
                    <i class="bi {{ $ic }}" style="color:var(--gs-teal);font-size:1.1rem"></i>
                </div>
                <div><div class="fw-bold fs-5">{{ $stats[$k] }}</div><div class="text-muted small">{{ $l }}</div></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($campaigns->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-megaphone fs-1 d-block mb-2 opacity-25"></i>No campaigns yet. <a href="{{ route('whatsapp.campaigns.create') }}">Create one</a>.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover small align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Campaign</th>
                        <th>Template</th>
                        <th class="text-center">Contacts</th>
                        <th class="text-center">Sent</th>
                        <th class="text-center">Failed</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Scheduled</th>
                        <th>By</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($campaigns as $c)
                <tr>
                    <td class="ps-3 fw-semibold">{{ $c->name }}</td>
                    <td class="font-monospace text-muted small">{{ $c->template?->name ?? '—' }}</td>
                    <td class="text-center">{{ $c->total_contacts }}</td>
                    <td class="text-center text-success fw-semibold">{{ $c->sent }}</td>
                    <td class="text-center text-danger">{{ $c->failed }}</td>
                    <td style="min-width:100px">
                        <div class="progress" style="height:6px">
                            <div class="progress-bar" style="width:{{ $c->progressPercent() }}%;background:var(--gs-teal)"></div>
                        </div>
                        <div class="text-muted" style="font-size:.7rem">{{ $c->progressPercent() }}%</div>
                    </td>
                    <td><span class="badge bg-{{ $c->statusBadgeClass() }}-subtle text-{{ $c->statusBadgeClass() }} border">{{ ucfirst($c->status) }}</span></td>
                    <td class="text-muted text-nowrap">{{ $c->scheduled_at?->format('d M H:i') ?? '—' }}</td>
                    <td class="text-nowrap">{{ $c->createdBy?->name ?? '—' }}</td>
                    <td class="pe-3">
                        <a href="{{ route('whatsapp.campaigns.show', $c) }}" class="btn btn-sm btn-outline-secondary">View</a>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @if($campaigns->hasPages())
    <div class="card-footer bg-white">{{ $campaigns->links() }}</div>
    @endif
</div>
@endsection
