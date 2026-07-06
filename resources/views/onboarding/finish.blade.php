@extends('layouts.main')
@section('title','Onboard — '.$client->name.' — Done')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
    <li class="breadcrumb-item"><a href="{{ route('clients.show', $client) }}">{{ $client->name }}</a></li>
    <li class="breadcrumb-item active">Onboarding — Done</li>
@endsection

@section('content')
<div class="row justify-content-center"><div class="col-xl-7">
@include('onboarding._steps', ['current' => 4])

<div class="card text-center">
    <div class="card-body py-5">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
        <h4 class="fw-bold mt-3">{{ $client->name }} is onboarded!</h4>
        <p class="text-muted">
            {{ $client->employees_count }} employee(s) added, {{ $client->devices_count }} device(s) tagged to this client.
        </p>

        @if(session('assign_summary'))
        @php $s = session('assign_summary'); @endphp
        <p class="small text-muted">Device assignment: {{ $s['assigned'] }} assigned, {{ $s['skipped'] }} skipped.</p>
        @endif

        <div class="d-flex justify-content-center gap-2 mt-4 flex-wrap">
            <a href="{{ route('clients.show', $client) }}" class="btn btn-primary"><i class="bi bi-briefcase"></i> View Client</a>
            <a href="{{ route('employees.index', ['client_id' => $client->id]) }}" class="btn btn-outline-secondary"><i class="bi bi-people"></i> View Employees</a>
            <a href="{{ route('reports.device-tracking') }}" class="btn btn-outline-secondary"><i class="bi bi-geo-alt"></i> Device Tracking Report</a>
        </div>
    </div>
</div>
</div></div>
@endsection
