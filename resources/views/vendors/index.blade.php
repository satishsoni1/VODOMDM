@extends('layouts.main')
@section('title','Vendors')
@section('breadcrumb')
    <li class="breadcrumb-item active">Vendors</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-building me-2"></i>Vendor Master</h5>
    <a href="{{ route('vendors.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Vendor</a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><input class="form-control form-control-sm" name="q" placeholder="Name / Code / Phone" value="{{ request('q') }}"></div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                    <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
                    <option value="blacklisted" {{ request('status')=='blacklisted'?'selected':'' }}>Blacklisted</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('vendors.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-dark">
                <tr><th>Code</th><th>Vendor Name</th><th>Contact</th><th>Phone</th><th>City</th><th>POs</th><th>Score</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($vendors as $v)
                <tr>
                    <td class="fw-bold font-monospace"><a href="{{ route('vendors.show',$v) }}" class="text-decoration-none">{{ $v->code }}</a></td>
                    <td>{{ $v->name }}</td>
                    <td class="small text-muted">{{ $v->contact_person }}</td>
                    <td class="font-monospace small">{{ $v->phone }}</td>
                    <td>{{ $v->city }}</td>
                    <td><span class="badge bg-secondary">{{ $v->purchase_orders_count }}</span></td>
                    <td>
                        <span class="badge bg-{{ $v->performance_score >= 80 ? 'success' : ($v->performance_score >= 50 ? 'warning text-dark' : 'danger') }}">
                            {{ $v->performance_score }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $v->status==='active'?'success':($v->status==='blacklisted'?'danger':'secondary') }}">
                            {{ ucfirst($v->status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('vendors.show',$v) }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-1"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('vendors.edit',$v) }}" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-1"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">No vendors found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($vendors->hasPages())
    <div class="card-footer">{{ $vendors->links() }}</div>
    @endif
</div>
@endsection
