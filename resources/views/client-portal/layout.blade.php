<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Client Portal') — GlobalSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --gs-teal:       #1a8a7c;
            --gs-teal-dark:  #0d4f47;
            --gs-teal-light: #e8f5f3;
            --gs-orange:     #f07030;
            --gs-orange-lt:  #fff3eb;
        }
        body { background: #f0f7f6; font-family: 'Segoe UI', system-ui, sans-serif; }

        /* ── Sidebar ── */
        .cp-sidebar {
            width: 240px; position: fixed; top: 0; left: 0; height: 100vh;
            background: var(--gs-teal-dark); z-index: 1000;
            overflow-y: auto; scrollbar-width: thin; scrollbar-color: #1a6058 #0d4f47;
        }
        .cp-sidebar::-webkit-scrollbar { width: 4px; }
        .cp-sidebar::-webkit-scrollbar-thumb { background: #1a6058; border-radius: 4px; }
        .cp-brand {
            padding: 1.1rem 1rem .9rem;
            background: #0a3830;
            border-bottom: 1px solid #1a6058;
        }
        .cp-brand img { height: 32px; }
        .cp-brand .client-name {
            font-size: .72rem; color: #7dbfb8; margin-top: .3rem;
            font-weight: 600; letter-spacing: .5px; text-transform: uppercase;
        }
        .cp-nav-section {
            color: #7dbfb8; font-size: .68rem; text-transform: uppercase;
            padding: .8rem 1rem .2rem; letter-spacing: 1px;
        }
        .cp-nav-link {
            display: flex; align-items: center; gap: .6rem;
            color: #b2d8d4; padding: .48rem 1rem; font-size: .875rem;
            border-radius: 6px; margin: 1px 8px; text-decoration: none;
            transition: background .15s, color .15s;
        }
        .cp-nav-link i { font-size: 1rem; width: 20px; text-align: center; }
        .cp-nav-link:hover, .cp-nav-link.active {
            background: var(--gs-teal); color: #fff;
        }
        .cp-nav-link.active { font-weight: 600; }

        /* ── Main ── */
        .cp-main { margin-left: 240px; min-height: 100vh; }
        .cp-topbar {
            background: #fff; border-bottom: 1px solid #d4eeeb;
            padding: .7rem 1.5rem; position: sticky; top: 0; z-index: 999;
            display: flex; align-items: center; justify-content: space-between;
        }
        .cp-topbar .page-title { font-weight: 700; color: var(--gs-teal-dark); font-size: 1.05rem; }
        .cp-content { padding: 1.5rem; }

        /* ── Cards / KPI ── */
        .kpi-card {
            border-radius: 12px; border: 1px solid #d4eeeb;
            background: #fff; padding: 1.1rem 1.25rem;
            transition: box-shadow .2s;
        }
        .kpi-card:hover { box-shadow: 0 4px 16px rgba(26,138,124,.12); }
        .kpi-card .kpi-val { font-size: 2rem; font-weight: 800; color: var(--gs-teal-dark); }
        .kpi-card .kpi-label { font-size: .8rem; color: #6c757d; }
        .kpi-card .kpi-icon {
            width: 44px; height: 44px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
        }
        .kpi-teal   { background: var(--gs-teal-light); color: var(--gs-teal); }
        .kpi-orange { background: var(--gs-orange-lt);  color: var(--gs-orange); }
        .kpi-green  { background: #e8f5e9; color: #2e7d32; }
        .kpi-red    { background: #fce8e8; color: #c62828; }

        /* ── Status badges ── */
        .badge-on  { background: #d4edda; color: #155724; }
        .badge-off { background: #f8d7da; color: #721c24; }

        /* ── Table ── */
        .cp-table thead th { background: var(--gs-teal-light); color: var(--gs-teal-dark); font-size: .8rem; font-weight: 700; border-bottom: 2px solid #b2d8d4; }
        .cp-table tbody tr:hover { background: #f5fffe; }

        /* ── Misc ── */
        .section-header { font-size: .95rem; font-weight: 700; color: var(--gs-teal-dark); margin-bottom: .75rem; }
        @media print { .cp-sidebar, .cp-topbar { display: none; } .cp-main { margin-left: 0; } }
    </style>
    @stack('styles')
</head>
<body>

<div class="cp-sidebar">
    <div class="cp-brand">
        <img src="{{ asset('logo.png') }}" alt="GlobalSpace">
        <div class="client-name">{{ auth()->user()->client?->name ?? 'Client Portal' }}</div>
    </div>
    <nav class="py-2">
        <div class="cp-nav-section">Overview</div>
        <a href="{{ route('client.dashboard') }}" class="cp-nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="cp-nav-section">Assets</div>
        <a href="{{ route('client.devices') }}" class="cp-nav-link {{ request()->routeIs('client.devices') || request()->routeIs('client.devices.show') ? 'active' : '' }}">
            <i class="bi bi-phone"></i> My Devices
        </a>
        <a href="{{ route('client.mdm-map') }}" class="cp-nav-link {{ request()->routeIs('client.mdm-map') ? 'active' : '' }}">
            <i class="bi bi-geo-alt-fill"></i> MDM Map
        </a>
        <a href="{{ route('client.mdm-devices') }}" class="cp-nav-link {{ request()->routeIs('client.mdm-devices') || request()->routeIs('client.mdm-devices.show') ? 'active' : '' }}">
            <i class="bi bi-phone-fill"></i> MDM Devices
        </a>
        <a href="{{ route('client.employees') }}" class="cp-nav-link {{ request()->routeIs('client.employees') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Field Staff
        </a>

        <div class="cp-nav-section">Support</div>
        <a href="{{ route('client.tickets') }}" class="cp-nav-link {{ request()->routeIs('client.tickets') ? 'active' : '' }}">
            <i class="bi bi-ticket-perforated"></i> Tickets
        </a>

        <div class="mt-4 pt-2 border-top" style="border-color:#1a6058!important">
            <form method="POST" action="{{ route('logout') }}" class="px-2">
                @csrf
                <button type="submit" class="cp-nav-link btn btn-link w-100 text-start border-0 p-0" style="color:#b2d8d4">
                    <i class="bi bi-box-arrow-left"></i> Sign Out
                </button>
            </form>
        </div>
    </nav>
</div>

<div class="cp-main">
    <div class="cp-topbar">
        <span class="page-title">@yield('page-title', 'Dashboard')</span>
        <div class="d-flex align-items-center gap-3">
            <span class="badge rounded-pill" style="background:var(--gs-teal-light);color:var(--gs-teal);font-size:.8rem;padding:.4rem .8rem">
                <i class="bi bi-building me-1"></i>{{ auth()->user()->client?->name }}
            </span>
            <span class="text-muted small"><i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}</span>
        </div>
    </div>
    <div class="cp-content">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 mb-3">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 mb-3">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
