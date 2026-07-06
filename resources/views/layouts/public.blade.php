<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Device Info') — Asset Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --gs-teal: #1a8a7c;
            --gs-teal-dark: #0d4f47;
            --gs-orange: #f07030;
        }
        body {
            background: #f0f7f6;
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
        }
        .public-wrap { max-width: 480px; margin: 0 auto; padding: 1.25rem 1rem 3rem; }
        .public-header { text-align: center; margin-bottom: 1.25rem; }
        .public-header img { height: 34px; }
        .public-header .subtitle { color: var(--gs-teal-dark); font-size: .78rem; letter-spacing: .5px; margin-top: .25rem; }
        .card { border: none; border-radius: 14px; box-shadow: 0 2px 10px rgba(13,79,71,.08); }
        .card-header { background: var(--gs-teal-dark); color: #fff; border-radius: 14px 14px 0 0 !important; font-weight: 600; font-size: .9rem; }
        .btn-primary { background: var(--gs-teal); border-color: var(--gs-teal); }
        .btn-primary:hover, .btn-primary:focus { background: var(--gs-teal-dark); border-color: var(--gs-teal-dark); }
        .btn-lg { border-radius: 10px; padding: .75rem 1rem; font-size: 1rem; }
        .form-control-lg { border-radius: 10px; }
        .badge-status { font-size: .8rem; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="public-wrap">
        <div class="public-header">
            <img src="{{ asset('logo.png') }}" alt="GlobalSpace">
            <div class="subtitle">Asset Tracking</div>
        </div>

        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
