@extends('layouts.public')

@section('title', 'Find a Device')

@section('content')

<div class="card mb-3">
    <div class="card-header"><i class="bi bi-search me-1"></i> Find a Device</div>
    <div class="card-body">
        <p class="text-muted small">
            Enter the device's ID (asset tag / serial / IMEI) or an employee code to look up device details.
        </p>

        @if(session('error'))
            <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('scan.find') }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="text" name="query" class="form-control form-control-lg" placeholder="Device ID or Employee Code" value="{{ old('query') }}" required autofocus>
                <button class="btn btn-primary btn-lg" type="submit">Search</button>
            </div>
        </form>
    </div>
</div>

@include('scan._help')

@endsection
