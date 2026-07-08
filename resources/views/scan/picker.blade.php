@extends('layouts.public')

@section('title', 'Select a Device')

@section('content')

<div class="card mb-3">
    <div class="card-header"><i class="bi bi-person-check me-1"></i> {{ $employee->name }}</div>
    <div class="card-body">
        <p class="text-muted small mb-2">Multiple devices are assigned to this employee. Select one to view details.</p>
    </div>
    <ul class="list-group list-group-flush">
        @foreach($devices as $device)
            <li class="list-group-item">
                <a href="{{ route('scan.show', ['device' => $device->qr_token]) }}" class="d-flex justify-content-between align-items-center text-decoration-none">
                    <div>
                        <div class="fw-semibold text-dark">{{ $device->model?->brand?->name }} {{ $device->model?->model_name }}</div>
                        <div class="text-muted small font-monospace">{{ $device->asset_tag }}</div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
            </li>
        @endforeach
    </ul>
</div>

<div class="text-center">
    <a href="{{ route('scan.search') }}" class="small">&larr; Search again</a>
</div>

@endsection
