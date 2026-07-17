<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Asset Tracking') — Asset Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --gs-teal:       #1a8a7c;
            --gs-teal-dark:  #0d4f47;
            --gs-teal-mid:   #1a6058;
            --gs-teal-light: #e8f5f3;
            --gs-orange:     #f07030;
        }
        body { background: #f0f7f6; font-family: 'Segoe UI', system-ui, sans-serif; }
        .sidebar { height: 100vh; background: var(--gs-teal-dark); width: 250px; position: fixed; top: 0; left: 0; z-index: 1000; overflow-y: auto; scrollbar-width: thin; scrollbar-color: var(--gs-teal-mid) var(--gs-teal-dark); }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: var(--gs-teal-dark); }
        .sidebar::-webkit-scrollbar-thumb { background: var(--gs-teal-mid); border-radius: 4px; }
        .sidebar .brand { padding: 1rem; background: #0a3830; border-bottom: 1px solid var(--gs-teal-mid); }
        .sidebar .brand img { height: 30px; }
        .sidebar .nav-link { color: #b2d8d4; padding: .48rem 1rem; font-size: .875rem; border-radius: 6px; margin: 1px 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: var(--gs-teal); color: #fff; }
        .sidebar .nav-link.active { font-weight: 600; }
        .sidebar .nav-link i { width: 20px; }
        .sidebar .nav-section { color: #7dbfb8; font-size: .68rem; text-transform: uppercase; padding: .75rem 1rem .25rem; letter-spacing: 1px; }
        .main-content { margin-left: 250px; min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid #d4eeeb; padding: .65rem 1.5rem; position: sticky; top: 0; z-index: 999; }
        .content-area { padding: 1.5rem; }
        .stat-card { border-left: 4px solid; border-radius: 8px; }
        .stat-card.blue   { border-color: var(--gs-teal); }
        .stat-card.green  { border-color: #198754; }
        .stat-card.orange { border-color: var(--gs-orange); }
        .stat-card.red    { border-color: #dc3545; }
        .stat-card.purple { border-color: #6f42c1; }
        .badge-status { font-size: .72rem; }
        .btn-primary { background: var(--gs-teal); border-color: var(--gs-teal); }
        .btn-primary:hover { background: var(--gs-teal-dark); border-color: var(--gs-teal-dark); }
        .text-primary { color: var(--gs-teal) !important; }
        .bg-primary   { background: var(--gs-teal) !important; }
        .border-primary { border-color: var(--gs-teal) !important; }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <img src="{{ asset('logo.png') }}" alt="GlobalSpace" class="mb-1">
        <div style="font-size:.68rem;color:#7dbfb8;margin-top:.3rem;letter-spacing:.5px">CRM & Lifecycle Management</div>
    </div>

    <nav class="mt-2">
        <div class="nav-section">Main</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('search') }}" class="nav-link {{ request()->routeIs('search') ? 'active' : '' }}">
            <i class="bi bi-search"></i> Global Search
        </a>

        <div class="nav-section">Procurement</div>
        <a href="{{ route('procurement.index') }}" class="nav-link {{ request()->routeIs('procurement.*') ? 'active' : '' }}">
            <i class="bi bi-bag-check"></i> Procurement
        </a>
        <a href="{{ route('vendors.index') }}" class="nav-link {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Vendors
        </a>

        <div class="nav-section">Inventory</div>
        <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
            <i class="bi bi-boxes"></i> Warehouse
        </a>
        <a href="{{ route('devices.index') }}" class="nav-link {{ request()->routeIs('devices.*') ? 'active' : '' }}">
            <i class="bi bi-phone"></i> Devices
        </a>

        <div class="nav-section">Operations</div>
        <a href="{{ route('dispatches.index') }}" class="nav-link {{ request()->routeIs('dispatches.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> Dispatch
        </a>
        <a href="{{ route('handovers.index') }}" class="nav-link {{ request()->routeIs('handovers.*') ? 'active' : '' }}">
            <i class="bi bi-person-check"></i> Handovers
        </a>
        <a href="{{ route('link-requests.index') }}" class="nav-link {{ request()->routeIs('link-requests.*') ? 'active' : '' }}">
            <i class="bi bi-qr-code-scan"></i> QR Link Requests
        </a>
        <a href="{{ route('scan-help.index') }}" class="nav-link {{ request()->routeIs('scan-help.*') ? 'active' : '' }}">
            <i class="bi bi-question-circle"></i> Scan Help Content
        </a>
        <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
            <i class="bi bi-briefcase"></i> Clients
        </a>
        <a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Employees
        </a>

        <div class="nav-section">Service</div>
        <a href="{{ route('tickets.index') }}" class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
            <i class="bi bi-ticket-perforated"></i> Service Desk
        </a>
        <a href="{{ route('repairs.index') }}" class="nav-link {{ request()->routeIs('repairs.*') ? 'active' : '' }}">
            <i class="bi bi-tools"></i> Repairs
        </a>

        <div class="nav-section">CRM</div>
        <a href="{{ route('recovery.index') }}" class="nav-link {{ request()->routeIs('recovery.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-return-left"></i> Recovery
        </a>
        <a href="{{ route('insurance.index') }}" class="nav-link {{ request()->routeIs('insurance.*') ? 'active' : '' }}">
            <i class="bi bi-shield-check"></i> Insurance
        </a>

        <div class="nav-section">MDM Portal</div>
        <a href="{{ route('mdm.index') }}" class="nav-link {{ request()->routeIs('mdm.index') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('mdm.sync') }}" class="nav-link {{ request()->routeIs('mdm.sync') ? 'active' : '' }}">
            <i class="bi bi-database-fill-up"></i> Sync
        </a>
        <a href="{{ route('mdm.devices') }}" class="nav-link {{ request()->routeIs('mdm.devices','mdm.show') ? 'active' : '' }}">
            <i class="bi bi-phone-fill"></i> Devices
        </a>
        <a href="{{ route('mdm.link') }}" class="nav-link {{ request()->routeIs('mdm.link') ? 'active' : '' }}">
            <i class="bi bi-link-45deg"></i> Link Employee
        </a>
        <a href="{{ route('mdm.map') }}" class="nav-link {{ request()->routeIs('mdm.map') ? 'active' : '' }}">
            <i class="bi bi-geo-alt-fill"></i> Device Map
        </a>
        <a href="{{ route('client-mdm-configs.index') }}" class="nav-link {{ request()->routeIs('client-mdm-configs.*') ? 'active' : '' }}">
            <i class="bi bi-diagram-2"></i> Client Configurations
        </a>

        <div class="nav-section">Analytics</div>
        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> Reports
        </a>
        <a href="{{ route('flow') }}" class="nav-link {{ request()->routeIs('flow') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i> Flow Document
        </a>

        <div class="nav-section">Messaging</div>
        <a href="{{ route('whatsapp.index') }}" class="nav-link {{ request()->routeIs('whatsapp.index','whatsapp.create','whatsapp.store') ? 'active' : '' }}">
            <i class="bi bi-whatsapp" style="color:#25d366"></i> Messages
        </a>
        <a href="{{ route('whatsapp.templates.index') }}" class="nav-link {{ request()->routeIs('whatsapp.templates.*') ? 'active' : '' }}">
            <i class="bi bi-file-text"></i> WA Templates
        </a>
        <a href="{{ route('whatsapp.campaigns.index') }}" class="nav-link {{ request()->routeIs('whatsapp.campaigns.*') ? 'active' : '' }}">
            <i class="bi bi-megaphone"></i> WA Campaigns
        </a>

        <div class="nav-section">Integrations</div>
        <a href="{{ route('api-logs.index') }}" class="nav-link {{ request()->routeIs('api-logs.*') ? 'active' : '' }}">
            <i class="bi bi-journal-code"></i> API Logs
        </a>

        <div class="nav-section">Admin</div>
        <a href="{{ route('client-users.index') }}" class="nav-link {{ request()->routeIs('client-users.*') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i> Client Logins
        </a>

        <div class="nav-section mt-4"></div>
        <form method="POST" action="{{ route('logout') }}" class="px-2 mb-3">
            @csrf
            <button type="submit" class="nav-link btn btn-link w-100 text-start text-danger">
                <i class="bi bi-box-arrow-left"></i> Sign Out
            </button>
        </form>
    </nav>
</div>

<div class="main-content">
    <div class="topbar d-flex align-items-center justify-content-between">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form action="{{ route('search') }}" method="GET" class="d-flex">
                <input class="form-control form-control-sm" type="search" name="q" placeholder="Search serial, IMEI, employee…" style="width:260px" value="{{ request('q') }}">
                <button class="btn btn-sm btn-primary ms-1"><i class="bi bi-search"></i></button>
            </form>
            <span class="text-muted small">{{ auth()->user()?->name }}</span>
        </div>
    </div>

    <div class="content-area">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
