@extends('layouts.main')
@section('title','New Client Login')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('client-users.index') }}">Client Users</a></li>
    <li class="breadcrumb-item active">New Login</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-person-plus me-2"></i>Create Client Portal Login</strong>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('client-users.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contact Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="e.g. Rajiv Malhotra">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Login Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="contact@client.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Client Company <span class="text-danger">*</span></label>
                        <select name="client_id" class="form-select @error('client_id') is-invalid @enderror">
                            <option value="">— Select client —</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>
                                {{ $client->name }} ({{ $client->code }})
                            </option>
                            @endforeach
                        </select>
                        @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Min 8 chars, mixed case + number">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Client will use this to log in at the main login page.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Login</button>
                        <a href="{{ route('client-users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
