@extends('layouts.main')
@section('title','Client MDM Configurations')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mdm.index') }}">MDM</a></li>
    <li class="breadcrumb-item active">Client Configurations</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold" style="color:var(--gs-teal-dark)">Client MDM Configurations</h4>
        <div class="text-muted small">Assign MDM device configurations to a client so their portal login only shows those devices.</div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($clients->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-building fs-1 d-block mb-2 opacity-25"></i>
            No clients found.
        </div>
        @else
        <table class="table table-hover mb-0 small align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Client</th>
                    <th>Assigned Configurations</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($clients as $client)
                <tr>
                    <td class="ps-3 fw-semibold">{{ $client->name }}</td>
                    <td>
                        @forelse($client->mdmConfigurations as $cfg)
                            <span class="badge rounded-pill me-1 mb-1" style="background:var(--gs-teal-light);color:var(--gs-teal-dark)">
                                {{ $cfg->configuration }}
                            </span>
                        @empty
                            <span class="text-muted fst-italic">None assigned</span>
                        @endforelse
                    </td>
                    <td class="pe-3 text-end">
                        <a href="{{ route('client-mdm-configs.edit', $client) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil me-1"></i>Assign
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
