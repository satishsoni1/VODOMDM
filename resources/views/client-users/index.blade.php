@extends('layouts.main')
@section('title','Client Users')
@section('breadcrumb')
    <li class="breadcrumb-item active">Client Users</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:var(--gs-teal-dark)">Client Portal Users</h4>
    <a href="{{ route('client-users.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Add Client Login
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($users->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-person-x fs-1 d-block mb-2 opacity-25"></i>
            No client logins yet. Create one to give a client access to their portal.
        </div>
        @else
        <table class="table table-hover mb-0 small align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Name</th>
                    <th>Email</th>
                    <th>Client Company</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td class="ps-3 fw-semibold">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge rounded-pill" style="background:var(--gs-teal-light);color:var(--gs-teal-dark)">
                            <i class="bi bi-building me-1"></i>{{ $user->client?->name ?? '—' }}
                        </span>
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge bg-success-subtle text-success border">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border">Inactive</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ $user->created_at?->format('d M Y') }}</td>
                    <td class="pe-3 text-end">
                        <a href="{{ route('client-users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('client-users.destroy', $user) }}" class="d-inline"
                            onsubmit="return confirm('Remove this client login?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white">{{ $users->links() }}</div>
    @endif
</div>
@endsection
