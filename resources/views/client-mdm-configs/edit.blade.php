@extends('layouts.main')
@section('title','Assign MDM Configurations')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mdm.index') }}">MDM</a></li>
    <li class="breadcrumb-item"><a href="{{ route('client-mdm-configs.index') }}">Client Configurations</a></li>
    <li class="breadcrumb-item active">{{ $client->name }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background:var(--gs-teal-light);border-bottom:1px solid #b2d8d4">
                <strong style="color:var(--gs-teal-dark)"><i class="bi bi-gear me-2"></i>Assign Configurations — {{ $client->name }}</strong>
            </div>
            <div class="card-body">
                @if($configs->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-exclamation-circle fs-2 d-block mb-2 opacity-25"></i>
                    No MDM configurations found yet. Run an MDM sync first.
                </div>
                @else
                <form method="POST" action="{{ route('client-mdm-configs.update', $client) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-text mb-2">
                        Devices whose configuration matches any of the checked items below will appear on
                        <strong>{{ $client->name }}</strong>'s portal map and device list.
                    </div>

                    <div class="list-group mb-4" style="max-height:420px;overflow-y:auto">
                        @foreach($configs as $config)
                        <label class="list-group-item d-flex align-items-center gap-2">
                            <input type="checkbox" class="form-check-input mt-0" name="configurations[]"
                                   value="{{ $config }}" @checked(in_array($config, $assigned))>
                            <span class="flex-grow-1">{{ $config }}</span>
                            <span class="badge bg-light text-secondary border">
                                {{ number_format($deviceCounts[$config] ?? 0) }} devices
                            </span>
                        </label>
                        @endforeach
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Assignment</button>
                        <a href="{{ route('client-mdm-configs.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
