@extends('layouts.main')
@section('title','Demand Requests')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item active">Demand Requests</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2"></i>Demand Requests</h5>
    <a href="{{ route('procurement.demand-requests.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New DR</a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><input class="form-control form-control-sm" name="q" placeholder="DR Number / Specification" value="{{ request('q') }}"></div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    @foreach(['draft','submitted','approved','rejected','converted_to_po'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="client_id">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('procurement.demand-requests') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-dark">
                <tr><th>DR Number</th><th>Specification</th><th>Qty</th><th>Client</th><th>Required By</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($demands as $dr)
                <tr>
                    <td><a href="{{ route('procurement.demand-requests.show',$dr) }}" class="fw-bold font-monospace text-decoration-none">{{ $dr->request_number }}</a></td>
                    <td class="small">{{ Str::limit($dr->device_specification,50) }}</td>
                    <td>{{ $dr->quantity }}</td>
                    <td class="small">{{ $dr->client?->name ?? '—' }}</td>
                    <td class="small {{ $dr->required_date && \Carbon\Carbon::parse($dr->required_date)->isPast() && !in_array($dr->status,['approved','converted_to_po']) ? 'text-danger fw-bold' : '' }}">
                        {{ $dr->required_date ? \Carbon\Carbon::parse($dr->required_date)->format('d M Y') : '—' }}
                    </td>
                    <td>
                        <span class="badge bg-{{ match($dr->status){ 'approved'=>'success','rejected'=>'danger','submitted'=>'warning text-dark','converted_to_po'=>'info',default=>'secondary' } }}">
                            {{ ucwords(str_replace('_',' ',$dr->status)) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('procurement.demand-requests.show',$dr) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">No demand requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($demands->hasPages())<div class="card-footer">{{ $demands->links() }}</div>@endif
</div>
@endsection
