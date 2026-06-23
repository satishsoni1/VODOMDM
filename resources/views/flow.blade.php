@extends('layouts.main')
@section('title', 'System Flow Document')
@section('breadcrumb')
    <li class="breadcrumb-item active">Flow Document</li>
@endsection

@push('styles')
<style>
    .phase-card { border-left: 4px solid; transition: box-shadow .2s; }
    .phase-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.1); }
    .phase-number { width: 36px; height: 36px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .9rem; flex-shrink: 0; }
    .flow-connector { position: relative; }
    .flow-connector::before { content:''; position:absolute; left:18px; top:0; bottom:0; width:2px; background:#dee2e6; }
    .flow-item { position: relative; padding-left: 48px; padding-bottom: 20px; }
    .flow-item::before { content:''; position:absolute; left:12px; top:8px; width:14px; height:14px; border-radius:50%; background: #0d6efd; border:3px solid #fff; box-shadow: 0 0 0 2px #0d6efd; }
    .rule-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 12px 16px; font-size: .85rem; }
    .status-pill { display: inline-block; padding: .2em .7em; border-radius: 30px; font-size: .75rem; font-weight: 600; margin: 2px; }
    .toc-nav .nav-link { font-size: .85rem; padding: .3rem .75rem; color: #495057; border-radius: 6px; }
    .toc-nav .nav-link:hover, .toc-nav .nav-link.active { background: #e7f1ff; color: #0d6efd; }

    @media print {
        /* Hide all application chrome */
        .sidebar, .topbar, .alert, #toc-col, #print-btn, #module-map-section { display: none !important; }

        /* Remove app layout margin — flow fills full page */
        .main-content { margin-left: 0 !important; }
        .content-area { padding: 0 !important; }
        body { background: #fff !important; font-size: 11pt; }

        /* Full-width single column */
        .row { display: block !important; }
        .col-xl-10, .col-lg-9 { width: 100% !important; max-width: 100% !important; }

        /* Hide system stack column (DB/tech info) inside overview */
        #system-stack-col { display: none !important; }
        /* Make the business pillars take full width without the stack col */
        #overview-pillars-col { width: 100% !important; max-width: 100% !important; }

        /* Hide "Open Module" links in phase headers */
        .phase-card a[href] { display: none !important; }

        /* Cards: remove shadows, keep borders */
        .card { box-shadow: none !important; border: 1px solid #ccc !important; break-inside: avoid; margin-bottom: 16pt !important; }
        .card-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* Phase cards: keep the left colour border */
        .phase-card { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* Status pills: keep colours */
        .status-pill, .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* Page breaks before each phase card for readability */
        .phase-card { break-before: auto; break-inside: avoid; }

        /* Flow-item dots keep colour */
        .flow-item::before { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* Footer */
        #flow-footer { display: block !important; }

        /* Headings tighter */
        h3 { font-size: 16pt; }
        h5 { font-size: 12pt; }
        h6 { font-size: 10pt; }
    }
</style>
@endpush

@section('content')
<div class="row g-4">

    {{-- ── TOC SIDEBAR ──────────────────────────────────────────────────── --}}
    <div id="toc-col" class="col-xl-2 col-lg-3 d-none d-lg-block">
        <div class="card border-0 shadow-sm sticky-top" style="top:70px">
            <div class="card-body p-2">
                <p class="text-muted text-uppercase fw-bold small px-2 mb-2" style="font-size:.7rem;letter-spacing:.05em">CONTENTS</p>
                <nav class="nav flex-column toc-nav">
                    <a href="#overview"     class="nav-link">Overview</a>
                    <a href="#lifecycle"    class="nav-link">Device Lifecycle</a>
                    <a href="#ph1"          class="nav-link">Ph 1 · Demand</a>
                    <a href="#ph2"          class="nav-link">Ph 2 · RFQ</a>
                    <a href="#ph3"          class="nav-link">Ph 3 · Purchase Order</a>
                    <a href="#ph4"          class="nav-link">Ph 4 · GRN</a>
                    <a href="#ph5"          class="nav-link">Ph 5 · QC</a>
                    <a href="#ph6"          class="nav-link">Ph 6 · Storage</a>
                    <a href="#ph7"          class="nav-link">Ph 7 · Config</a>
                    <a href="#ph8"          class="nav-link">Ph 8 · Dispatch</a>
                    <a href="#ph9"          class="nav-link">Ph 9 · Delivery</a>
                    <a href="#ph10"         class="nav-link">Ph 10 · Handover</a>
                    <a href="#ph11"         class="nav-link">Ph 11 · Active Use</a>
                    <a href="#ph12"         class="nav-link">Ph 12 · Support</a>
                    <a href="#ph13"         class="nav-link">Ph 13 · Repair</a>
                    <a href="#ph14"         class="nav-link">Ph 14 · Insurance</a>
                    <a href="#ph15"         class="nav-link">Ph 15 · Recovery</a>
                    <a href="#ph16"         class="nav-link">Ph 16 · Retirement</a>
                    <a href="#ph17"         class="nav-link">Ph 17 · Disposal</a>
                    <a href="#roles"        class="nav-link">Roles & Access</a>
                    <a href="#rules"        class="nav-link">Business Rules</a>
                    <a href="#modules"      class="nav-link">Module Map</a>
                </nav>
            </div>
        </div>
    </div>

    {{-- ── MAIN CONTENT ─────────────────────────────────────────────────── --}}
    <div class="col-xl-10 col-lg-9">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-diagram-3 me-2 text-primary"></i>System Flow Document</h3>
                <p class="text-muted mb-0">End-to-End CRM &amp; Asset Lifecycle Management — Complete Reference</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <button id="print-btn" onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-printer me-1"></i>Print
                </button>
                <div class="text-end">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">v2.0 · 17 Phases</span>
                    <div class="small text-muted mt-1">Last Updated: {{ now()->format('d M Y') }}</div>
                </div>
            </div>
        </div>

        {{-- ── OVERVIEW ─────────────────────────────────────────────────── --}}
        <div id="overview" class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>System Overview</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div id="overview-pillars-col" class="col-md-8">
                        <p class="mb-3">This system manages the <strong>complete lifecycle of mobile devices</strong> deployed to client field force employees. It bridges procurement, logistics, operations, IT support, finance, and recovery into a single unified workflow.</p>
                        <div class="row g-2">
                            @php
                            $pillars = [
                                ['bi-buildings','Primary Clients','FMCG, Pharma, BFSI — field force operators'],
                                ['bi-phone','Device Types','Smartphones, tablets, feature phones, laptops'],
                                ['bi-geo-alt','Geography','Pan-India multi-warehouse deployment'],
                                ['bi-people','End Users','Field sales executives, medical reps, distributors'],
                                ['bi-shield-check','Compliance','Full audit trail, ownership history, GST invoice tracking'],
                            ];
                            @endphp
                            @foreach($pillars as $p)
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-2 bg-light rounded">
                                    <i class="bi {{ $p[0] }} text-primary mt-1"></i>
                                    <div><div class="fw-semibold small">{{ $p[1] }}</div><div class="text-muted" style="font-size:.8rem">{{ $p[2] }}</div></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div id="system-stack-col" class="col-md-4">
                        <div class="bg-primary-subtle rounded p-3 h-100">
                            <h6 class="text-primary fw-bold mb-3">System Stack</h6>
                            @php
                            $stack = [
                                'Framework'   => 'Laravel 12 (PHP 8.3)',
                                'Database'    => 'MySQL / MariaDB (57 tables)',
                                'UI'          => 'Bootstrap 5 + Bootstrap Icons',
                                'Auth'        => 'Laravel Breeze',
                                'Server'      => 'XAMPP / Apache',
                                'API'         => 'REST (JSON)',
                            ];
                            @endphp
                            @foreach($stack as $k => $v)
                            <div class="d-flex justify-content-between border-bottom border-primary-subtle py-1 small">
                                <span class="text-muted">{{ $k }}</span><span class="fw-semibold">{{ $v }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── LIFECYCLE STATUS FLOW ────────────────────────────────────── --}}
        <div id="lifecycle" class="card border-0 shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-repeat me-2 text-success"></i>Device Lifecycle Status Flow</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">The <code>lifecycle_status</code> field on every device tracks its current state. Transitions are one-directional unless explicitly noted.</p>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @php
                    $statuses = [
                        ['in_stock',       'success',   'In Stock'],
                        ['qc_done',        'info',      'QC Done'],
                        ['configured',     'primary',   'Configured'],
                        ['dispatched',     'warning',   'Dispatched'],
                        ['delivered',      'primary',   'Delivered'],
                        ['assigned',       'secondary', 'Assigned'],
                        ['active',         'success',   'Active'],
                        ['under_repair',   'danger',    'Under Repair'],
                        ['recovery_pending','warning',  'Recovery Pending'],
                        ['returned',       'secondary', 'Returned'],
                        ['disposed',       'dark',      'Disposed'],
                        ['lost',           'danger',    'Lost'],
                    ];
                    @endphp
                    @foreach($statuses as $i => $s)
                        <span class="status-pill bg-{{ $s[1] }}-subtle text-{{ $s[1] }} border border-{{ $s[1] }}-subtle">{{ $s[2] }}</span>
                        @if($i < count($statuses)-1)<i class="bi bi-arrow-right text-muted"></i>@endif
                    @endforeach
                </div>
                <div class="row g-2 mt-3">
                    <div class="col-md-6">
                        <div class="rule-box">
                            <strong>Forward transitions (normal path):</strong><br>
                            in_stock → <em>qc_done</em> → <em>configured</em> → <em>dispatched</em> → <em>delivered</em> → <em>assigned</em> → <em>active</em>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rule-box">
                            <strong>Exception paths:</strong><br>
                            active → <em>under_repair</em> → in_stock (repaired) or disposed (unrepairable)<br>
                            active → <em>recovery_pending</em> → returned → in_stock<br>
                            active → <em>lost</em> (theft, FIR filed)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── PHASE GRID ───────────────────────────────────────────────── --}}
        @php
        $phases = [
            [
                'id' => 'ph1', 'n' => 1, 'title' => 'Demand Request', 'icon' => 'bi-file-earmark-text',
                'color' => '#0d6efd', 'module' => 'Procurement', 'role' => 'Procurement Manager',
                'route' => 'procurement.demand-requests',
                'desc'  => 'Client raises a requirement for a specific number of devices. Internal team validates, estimates budget, and approves the demand before proceeding to vendor sourcing.',
                'steps' => [
                    'Client project manager submits device requirement with quantity, model spec, region, and budget.',
                    'Procurement team creates a Demand Request (DR) with unique DR number.',
                    'Finance/Admin approves DR and sets approved budget.',
                    'DR status transitions: <code>submitted → under_review → approved → converted_to_po</code>',
                    'Approved DR triggers RFQ creation.',
                ],
                'inputs'  => 'Client, ClientProject, DeviceModel, Region, Quantity, Budget',
                'outputs' => 'DemandRequest (approved)',
                'rules'   => ['Budget must be set before approval.', 'A single DR can result in multiple POs if quantity is split across vendors.', 'Rejected DRs require justification note.'],
            ],
            [
                'id' => 'ph2', 'n' => 2, 'title' => 'RFQ — Request for Quotation', 'icon' => 'bi-envelope-paper',
                'color' => '#6610f2', 'module' => 'Procurement', 'role' => 'Procurement Manager',
                'route' => 'procurement.rfqs',
                'desc'  => 'Procurement sends a formal Request for Quotation to multiple vendors. Vendors respond with unit prices, delivery timelines, and warranty terms. Best quotation is selected.',
                'steps' => [
                    'Create RFQ linked to approved DR. Set response deadline.',
                    'Select 2–4 vendors and send RFQ (email notification).',
                    'Vendors submit quotations with unit price, delivery days, warranty months.',
                    'Procurement compares quotations and marks the winning bid.',
                    'RFQ status transitions: <code>draft → sent → responses_received → closed</code>',
                ],
                'inputs'  => 'DemandRequest, Vendor list, Spec sheet',
                'outputs' => 'Rfq, VendorQuotation (selected)',
                'rules'   => ['At least 2 vendors must be invited before sending.', 'Selected quotation locks and auto-fills PO creation.', 'Quotations valid until response_deadline.'],
            ],
            [
                'id' => 'ph3', 'n' => 3, 'title' => 'Purchase Order', 'icon' => 'bi-receipt',
                'color' => '#0dcaf0', 'module' => 'Procurement', 'role' => 'Procurement Manager / Finance',
                'route' => 'procurement.purchase-orders',
                'desc'  => 'A legally binding Purchase Order is created for the selected vendor based on the winning quotation. Finance approves and the vendor is notified. Tax (GST) is calculated automatically.',
                'steps' => [
                    'Auto-fill PO from selected quotation (vendor, unit price, quantity).',
                    'Finance reviews and approves the PO.',
                    'Vendor receives PO and confirms acceptance.',
                    'Finance records vendor invoice (PO Invoice) on receipt.',
                    'PO status: <code>draft → approved → sent_to_vendor → partially_received → completed</code>',
                ],
                'inputs'  => 'VendorQuotation (selected), DemandRequest',
                'outputs' => 'PurchaseOrder, PoInvoice',
                'rules'   => ['Grand total = unit_price × quantity + tax_amount.', 'PO cannot be modified after approval without admin override.', 'Payment terms tracked for cash-flow planning.'],
            ],
            [
                'id' => 'ph4', 'n' => 4, 'title' => 'Goods Receipt Note (GRN)', 'icon' => 'bi-box-arrow-in-down',
                'color' => '#198754', 'module' => 'Inventory / Warehouse', 'role' => 'Warehouse Staff',
                'route' => 'inventory.grn',
                'desc'  => 'When devices arrive from the vendor, warehouse staff records a Goods Receipt Note. Physical quantities are matched against the PO. Damaged or missing units are documented.',
                'steps' => [
                    'Warehouse receives shipment with vendor delivery challan.',
                    'Create GRN linked to PO. Enter quantity received, accepted, rejected.',
                    'Record delivery challan number and driver details.',
                    'If partial: GRN status = partially_accepted, PO stays open.',
                    'On full receipt: PO status transitions to completed.',
                ],
                'inputs'  => 'PurchaseOrder, Delivery Challan, Physical devices',
                'outputs' => 'Grn record, Devices created (lifecycle_status = in_stock)',
                'rules'   => ['Quantity accepted + rejected must equal quantity received.', 'Each accepted device is registered individually with IMEI, serial number, asset tag.', 'Rejected devices trigger vendor return process.'],
            ],
            [
                'id' => 'ph5', 'n' => 5, 'title' => 'Quality Check (QC)', 'icon' => 'bi-patch-check',
                'color' => '#20c997', 'module' => 'Inventory / Warehouse', 'role' => 'Warehouse QC Staff',
                'route' => 'inventory.index',
                'desc'  => 'Each registered device undergoes a quality check to verify hardware integrity, IMEI validity, box accessories, and screen/camera/button functionality. Passed devices proceed to configuration.',
                'steps' => [
                    'Open device detail page and update lifecycle_status to qc_done.',
                    'Record condition (new / good / fair) and any defects observed.',
                    'Verify IMEI matches box label and device settings.',
                    'Check all accessories are present (charger, cable, SIM ejector).',
                    'Failed QC → condition = defective, hold for vendor replacement.',
                ],
                'inputs'  => 'Device (in_stock)',
                'outputs' => 'Device (qc_done or defective)',
                'rules'   => ['QC must be done before configuration.', 'IMEI must be unique in the database.', 'Defective devices are excluded from dispatch queue.'],
            ],
            [
                'id' => 'ph6', 'n' => 6, 'title' => 'Storage & Inventory Management', 'icon' => 'bi-archive',
                'color' => '#fd7e14', 'module' => 'Inventory', 'role' => 'Warehouse Manager',
                'route' => 'inventory.index',
                'desc'  => 'Devices are stored at designated warehouse locations. The system tracks stock levels by location, model, and condition. Inter-warehouse transfers are recorded with reason and authorization.',
                'steps' => [
                    'Devices assigned to warehouse location (current_location_id).',
                    'Inventory dashboard shows stock by location, model, status.',
                    'Inter-location transfer: update current_location_id with transfer log.',
                    'Min-stock alerts trigger new demand requests automatically.',
                    'Box numbers tracked for physical storage bin management.',
                ],
                'inputs'  => 'Device (qc_done / configured), Location',
                'outputs' => 'Inventory count by location/status',
                'rules'   => ['Each device must have a valid current_location_id.', 'Transfers require authorization from warehouse manager.', 'Stock counts reconciled weekly against physical count.'],
            ],
            [
                'id' => 'ph7', 'n' => 7, 'title' => 'Device Configuration', 'icon' => 'bi-gear',
                'color' => '#6f42c1', 'module' => 'Inventory', 'role' => 'Warehouse / IT',
                'route' => 'inventory.index',
                'desc'  => 'Devices are configured with client-specific MDM enrollment, apps, SIM cards, and network settings before dispatch. Device status is updated to configured after successful setup.',
                'steps' => [
                    'MDM enrollment: device enrolled in client-specific MDM profile.',
                    'Client apps installed (SFA, DMS, CRM, expense, etc.).',
                    'SIM card activated and assigned to device (MSISDN recorded).',
                    'Network APN and VPN configured as per client policy.',
                    'lifecycle_status updated to <code>configured</code>.',
                ],
                'inputs'  => 'Device (qc_done), MDM profile, SIM card, Client config',
                'outputs' => 'Device (configured)',
                'rules'   => ['Configuration must be completed before dispatch batch creation.', 'SIM assignment creates a SIM-device link record.', 'MDM profile ID is recorded on the device record.'],
            ],
            [
                'id' => 'ph8', 'n' => 8, 'title' => 'Dispatch', 'icon' => 'bi-truck',
                'color' => '#ffc107', 'module' => 'Dispatch', 'role' => 'Operations Manager',
                'route' => 'dispatches.index',
                'desc'  => 'Configured devices are grouped into a Dispatch Batch and handed to a courier partner for delivery to the client location. AWB numbers and tracking are recorded.',
                'steps' => [
                    'Operations creates a Dispatch Batch selecting devices to dispatch.',
                    'Select client, project, courier partner, and destination address.',
                    'Enter AWB number and tracking number from courier.',
                    'Each device in batch: lifecycle_status = <code>dispatched</code>, client_id assigned.',
                    'Batch status: <code>pending → in_transit → delivered → partially_delivered</code>',
                ],
                'inputs'  => 'Devices (configured / in_stock), CourierPartner, Client, Location',
                'outputs' => 'DispatchBatch, DispatchItems (per device)',
                'rules'   => ['Only in_stock, qc_done, or configured devices can be dispatched.', 'AWB number is mandatory before marking in_transit.', 'Freight cost tracked per dispatch for financial reporting.'],
            ],
            [
                'id' => 'ph9', 'n' => 9, 'title' => 'Delivery Confirmation', 'icon' => 'bi-box-seam-fill',
                'color' => '#28a745', 'module' => 'Dispatch', 'role' => 'Operations Manager',
                'route' => 'dispatches.index',
                'desc'  => 'Operations updates the dispatch batch with actual delivery date upon courier confirmation or client acknowledgement. All devices in the batch move to delivered status.',
                'steps' => [
                    'Courier confirms delivery or client sends POD (Proof of Delivery).',
                    'Operations updates batch: status = <code>delivered</code>, actual_delivery_date set.',
                    'All DispatchItems updated: status = <code>delivered</code>.',
                    'All devices in batch: lifecycle_status = <code>delivered</code>.',
                    'Client notified to proceed with employee handover.',
                ],
                'inputs'  => 'DispatchBatch (in_transit), Delivery confirmation',
                'outputs' => 'DispatchBatch (delivered), Devices (delivered)',
                'rules'   => ['Actual delivery date cannot be before dispatch date.', 'Partial delivery: some items mark delivered, batch stays partially_delivered.', 'Delivery triggers handover workflow initiation.'],
            ],
            [
                'id' => 'ph10', 'n' => 10, 'title' => 'Device Handover to Employee', 'icon' => 'bi-person-check',
                'color' => '#17a2b8', 'module' => 'Handovers', 'role' => 'Operations Manager',
                'route' => 'handovers.index',
                'desc'  => 'A formal handover record is created when a device is physically given to a field employee. Employee signs an acknowledgement receipt. Ownership history is updated.',
                'steps' => [
                    'Operations creates Handover record linking device → employee → client project.',
                    'Record accessories handed, condition at handover, handover location.',
                    'Employee signs digital/physical acknowledgement.',
                    'Update handover: acknowledgement_received = true, acknowledged_at timestamp.',
                    'Activate handover: lifecycle_status = <code>active</code>, current_employee_id set.',
                    'OwnershipHistory record created (device_id, employee_id, from_date).',
                ],
                'inputs'  => 'Device (delivered/assigned), Employee, ClientProject',
                'outputs' => 'DeviceHandover, OwnershipHistory, Device (active)',
                'rules'   => ['One active handover per device at a time.', 'Employee must belong to the same client as the device.', 'Handover date cannot be before dispatch date.', 'Acknowledgement required before device can be marked active.'],
            ],
            [
                'id' => 'ph11', 'n' => 11, 'title' => 'Active Use', 'icon' => 'bi-phone-vibrate',
                'color' => '#28a745', 'module' => 'Devices', 'role' => 'All',
                'route' => 'devices.index',
                'desc'  => 'The device is in active use by the employee. System tracks device age, warranty expiry, and SIM status. Tickets can be raised for issues. This is the steady-state phase.',
                'steps' => [
                    'Device dashboard shows active devices by client, employee, region.',
                    'Warranty expiry alerts generated 90 days before expiry.',
                    'Employees raise support tickets via helpdesk.',
                    'MDM reports device health, app compliance, and location.',
                    'Annual renewal reminder for insurance policies.',
                ],
                'inputs'  => 'Device (active)',
                'outputs' => 'Usage reports, Ticket triggers, Insurance renewals',
                'rules'   => ['Active device must have an assigned employee (current_employee_id).', 'Warranty expiry tracked against purchase_date + warranty_months.', 'Device age category: <1yr = new, 1–2yr = mid-life, >2yr = aging.'],
            ],
            [
                'id' => 'ph12', 'n' => 12, 'title' => 'Support Tickets / Service Desk', 'icon' => 'bi-headset',
                'color' => '#dc3545', 'module' => 'Tickets', 'role' => 'Service Desk Agent',
                'route' => 'tickets.index',
                'desc'  => 'Employees report device issues via the service desk. Tickets are categorized, prioritized, and assigned to agents. SLA timers track response and resolution obligations.',
                'steps' => [
                    'Ticket raised by service desk agent on behalf of employee.',
                    'Auto-assign priority (critical/high/medium/low) and SLA deadline from category.',
                    'Agent assigns ticket to self or another agent for investigation.',
                    'Agent adds comments (internal or customer-facing) during investigation.',
                    'Resolution: ticket closed, resolution_hours calculated.',
                    'SLA breach: sla_due_at exceeded → ticket highlighted in red.',
                ],
                'inputs'  => 'Device (active), Employee, TicketCategory',
                'outputs' => 'Ticket, TicketComments, first_response_at, resolved_at',
                'rules'   => ['SLA hours per category: Critical 2h, High 4-8h, Medium 24h, Low 48-72h.', 'First response must be logged before SLA breach to stop SLA clock.', 'Screen damage / hardware faults → auto-link to Repair Order.', 'Theft / loss → trigger Recovery Case and Insurance Claim.'],
            ],
            [
                'id' => 'ph13', 'n' => 13, 'title' => 'Repair Management', 'icon' => 'bi-tools',
                'color' => '#e83e8c', 'module' => 'Repairs', 'role' => 'Service Desk / Operations',
                'route' => 'repairs.index',
                'desc'  => 'Hardware-fault devices are sent to an authorized service center. A Repair Order (RMA) tracks the repair progress, costs, and outcome. Device lifecycle is updated accordingly.',
                'steps' => [
                    'Create Repair Order (RMA) linking device, service center, and optionally a ticket.',
                    'Record fault description, repair type (warranty / chargeable / AMC), estimated cost.',
                    'Device sent to service center: lifecycle_status = <code>under_repair</code>.',
                    'Service center updates status: received → under_repair → repaired/unrepairable.',
                    'On return: if repaired → lifecycle_status = <code>in_stock</code> (ready for re-use).',
                    'If unrepairable → lifecycle_status = <code>disposed</code>.',
                ],
                'inputs'  => 'Device (active/assigned), ServiceCenter, Ticket (optional)',
                'outputs' => 'RepairOrder (RMA), Device (in_stock or disposed)',
                'rules'   => ['Warranty repairs: no cost if within warranty_months from purchase_date.', 'Chargeable repairs: actual_cost billed to client/insurance.', 'Repair order must be linked to a service center.', 'Overdue repairs: estimated_return_date exceeded → flagged on dashboard.'],
            ],
            [
                'id' => 'ph14', 'n' => 14, 'title' => 'Insurance Management', 'icon' => 'bi-shield-check',
                'color' => '#fd7e14', 'module' => 'Insurance', 'role' => 'Finance / Operations',
                'route' => 'insurance.index',
                'desc'  => 'Insurance policies cover devices against theft, accidental damage, and liquid damage. Claims are filed when incidents occur. Policy expiry and claim status are tracked.',
                'steps' => [
                    'Finance creates an Insurance Policy with provider, coverage, premium, sum insured, dates.',
                    'Devices enrolled under the policy (DeviceInsurance link records).',
                    'Incident occurs → File claim from policy detail page.',
                    'Claim lifecycle: <code>submitted → under_review → approved → settled / rejected</code>',
                    'Approved amount and settlement date recorded when insurer pays.',
                    'Policy expiry alerts generated 30 days before expiry_date.',
                ],
                'inputs'  => 'InsurancePolicy, Device, InsuranceProvider, Incident details',
                'outputs' => 'InsuranceClaim, Settlement record',
                'rules'   => ['Theft claims require FIR number.', 'Claim must be filed within 30 days of incident.', '10% deductible applied to physical damage claims.', 'Policy automatically flagged "expiring" when within 30 days of expiry.'],
            ],
            [
                'id' => 'ph15', 'n' => 15, 'title' => 'Device Recovery', 'icon' => 'bi-arrow-counterclockwise',
                'color' => '#6f42c1', 'module' => 'Recovery', 'role' => 'Recovery Agent',
                'route' => 'recovery.index',
                'desc'  => 'When an employee leaves (resignation, termination, project-end) or device is overdue, a Recovery Case is opened. Recovery agents call the employee and arrange device pickup.',
                'steps' => [
                    'Recovery case created linking device, employee, client, and trigger reason.',
                    'lifecycle_status updated to <code>recovery_pending</code>.',
                    'Agent logs call attempts (CallLog) with outcome: connected/no_answer/promised/refused.',
                    'Employee confirms pickup date → Follow-up record created.',
                    'Device collected: status = <code>recovered</code>, recovered_date set.',
                    'Device returned to warehouse: lifecycle_status = <code>returned</code>.',
                    'Device re-enters inventory for next deployment cycle.',
                ],
                'inputs'  => 'Device (active), Employee (departing), Trigger reason',
                'outputs' => 'RecoveryCase, CallLogs, Device (returned)',
                'rules'   => ['Recovery due date = exit_date + 7 days.', 'Overdue = recovery_due_date exceeded and status not "recovered".', 'Escalation after 3 failed call attempts.', 'Recovered device conditioned and stored at warehouse before re-issue.'],
            ],
            [
                'id' => 'ph16', 'n' => 16, 'title' => 'Device Retirement / Write-off', 'icon' => 'bi-calendar-x',
                'color' => '#adb5bd', 'module' => 'Devices', 'role' => 'Finance / Admin',
                'route' => 'devices.index',
                'desc'  => 'Devices reaching end-of-life (3+ years, repeated failures, unrepairable) are reviewed for retirement. Finance approves write-off and device is removed from active inventory.',
                'steps' => [
                    'Devices >3 years or with repeated repair history flagged for review.',
                    'Operations creates retirement request citing reason.',
                    'Finance approves write-off (net book value calculation).',
                    'Device marked as disposed in the system.',
                    'Physical device decommissioned: data wipe, IMEI block, MDM un-enroll.',
                ],
                'inputs'  => 'Device (returned / under_repair), Age/condition criteria',
                'outputs' => 'Device (disposed), Write-off record',
                'rules'   => ['Write-off requires Finance approval.', 'IMEI must be blocked with carrier before physical disposal.', 'Data wipe certificate required for compliance.', 'Written-off value recorded in financial report.'],
            ],
            [
                'id' => 'ph17', 'n' => 17, 'title' => 'Physical Disposal', 'icon' => 'bi-trash3',
                'color' => '#343a40', 'module' => 'Devices', 'role' => 'Warehouse / Finance',
                'route' => 'devices.index',
                'desc'  => 'Physically disposed devices are handed to authorized e-waste recyclers. A disposal certificate is obtained for regulatory compliance. Device record is archived.',
                'steps' => [
                    'Devices in disposed status transferred to recycler/refurbisher.',
                    'Disposal certificate obtained from recycler (e-waste compliance).',
                    'Device record updated with disposal_date, disposal_method, recycler details.',
                    'Fixed asset register updated (device fully depreciated).',
                    'GST credit note raised if applicable.',
                ],
                'inputs'  => 'Device (disposed)',
                'outputs' => 'Disposal certificate, Updated asset register',
                'rules'   => ['Only CPCB-authorized e-waste recyclers permitted.', 'Bulk disposal batched monthly.', 'Disposal certificate archived with device record.'],
            ],
        ];
        @endphp

        @php $phaseColors = ['0d6efd','6610f2','0dcaf0','198754','20c997','fd7e14','6f42c1','ffc107','28a745','17a2b8','28a745','dc3545','e83e8c','fd7e14','6f42c1','adb5bd','343a40']; @endphp

        @foreach($phases as $phase)
        <div id="{{ $phase['id'] }}" class="card border-0 shadow-sm mb-4 phase-card" style="border-left-color: {{ $phase['color'] }} !important">
            <div class="card-header d-flex align-items-center gap-3 py-3" style="background: {{ $phase['color'] }}15; border-bottom: 1px solid {{ $phase['color'] }}30">
                <span class="phase-number text-white" style="background: {{ $phase['color'] }}">{{ $phase['n'] }}</span>
                <div class="flex-grow-1">
                    <h5 class="mb-0 fw-bold">{{ $phase['title'] }}</h5>
                    <div class="d-flex gap-3 mt-1 flex-wrap">
                        <span class="small text-muted"><i class="bi bi-grid me-1"></i>Module: <strong>{{ $phase['module'] }}</strong></span>
                        <span class="small text-muted"><i class="bi bi-person me-1"></i>Role: <strong>{{ $phase['role'] }}</strong></span>
                        @if(isset($phase['route']))
                        <a href="{{ route($phase['route']) }}" class="small text-decoration-none" style="color:{{ $phase['color'] }}"><i class="bi bi-arrow-right-circle me-1"></i>Open Module</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <p class="text-secondary mb-4">{{ $phase['desc'] }}</p>
                <div class="row g-4">
                    <div class="col-lg-5">
                        <h6 class="fw-semibold mb-3"><i class="bi bi-list-ol me-2" style="color:{{ $phase['color'] }}"></i>Workflow Steps</h6>
                        <div class="flow-connector">
                            @foreach($phase['steps'] as $step)
                            <div class="flow-item small">{!! $step !!}</div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <h6 class="fw-semibold mb-2"><i class="bi bi-arrow-down-circle me-2 text-success"></i>Inputs</h6>
                        <div class="rule-box mb-3 small">{{ $phase['inputs'] }}</div>
                        <h6 class="fw-semibold mb-2"><i class="bi bi-arrow-up-circle me-2 text-primary"></i>Outputs</h6>
                        <div class="rule-box small">{{ $phase['outputs'] }}</div>
                    </div>
                    <div class="col-lg-3">
                        <h6 class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Business Rules</h6>
                        <ul class="list-unstyled mb-0">
                            @foreach($phase['rules'] as $rule)
                            <li class="d-flex gap-2 mb-2">
                                <i class="bi bi-dot text-warning mt-1 flex-shrink-0" style="font-size:1.2rem"></i>
                                <span class="small">{{ $rule }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        {{-- ── ROLES & ACCESS ───────────────────────────────────────────── --}}
        <div id="roles" class="card border-0 shadow-sm mb-4">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-person-badge me-2 text-primary"></i>Roles &amp; Access Matrix</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Role</th>
                                <th>Procurement</th>
                                <th>Inventory</th>
                                <th>Dispatch</th>
                                <th>Tickets</th>
                                <th>Recovery</th>
                                <th>Insurance</th>
                                <th>Repairs</th>
                                <th>Reports</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $access = [
                                ['Super Admin',     '✓ Full','✓ Full','✓ Full','✓ Full','✓ Full','✓ Full','✓ Full','✓ Full'],
                                ['Admin',           '✓ Full','✓ Full','✓ Full','✓ Full','✓ Full','✓ Full','✓ Full','✓ Full'],
                                ['Procurement',     '✓ Full','View','View','—','—','—','—','✓'],
                                ['Warehouse',       'View','✓ Full','View','—','—','—','—','Inventory'],
                                ['Operations',      'View','View','✓ Full','View','✓','View','View','✓'],
                                ['Service Desk',    '—','View','—','✓ Full','—','View','✓','Support'],
                                ['Recovery Agent',  '—','View','—','View','✓ Full','—','—','Recovery'],
                                ['Finance',         'View','—','View','—','—','✓ Full','—','✓ Full'],
                                ['Viewer',          'View','View','View','View','View','View','View','View'],
                            ];
                            @endphp
                            @foreach($access as $row)
                            <tr>
                                @foreach($row as $i => $cell)
                                @if($i === 0)
                                    <td class="fw-semibold">{{ $cell }}</td>
                                @elseif($cell === '✓ Full')
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle">Full</span></td>
                                @elseif($cell === '—')
                                    <td class="text-muted">—</td>
                                @elseif($cell === 'View')
                                    <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">View</span></td>
                                @else
                                    <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ $cell }}</span></td>
                                @endif
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── BUSINESS RULES SUMMARY ───────────────────────────────────── --}}
        <div id="rules" class="card border-0 shadow-sm mb-4">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-clipboard-check me-2 text-warning"></i>Global Business Rules</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    @php
                    $globalRules = [
                        ['Number Generation','All entities use prefixed random codes: DR-XXXXX, RFQ-XXXXX, PO-XXXXX, GRN-XXXXX, DISP-XXXXX, HO-XXXXX, TKT-XXXXX, RCV-XXXXX, POL-XXXXX, CLM-XXXXX, RMA-XXXXX.','bi-hash','primary'],
                        ['Soft Deletes','All models use SoftDeletes. No record is permanently deleted from the database. Deleted records are excluded from all queries but retained for audit.','bi-trash','danger'],
                        ['Ownership History','Every device ownership change (handover, transfer, recovery) creates an OwnershipHistory record with from_date and to_date. Full chain of custody maintained.','bi-journal-text','info'],
                        ['Lifecycle Integrity','lifecycle_status transitions are enforced in controllers. A device cannot skip states (e.g., cannot go from in_stock directly to active without dispatch and handover).','bi-diagram-2','success'],
                        ['Client Isolation','Devices, employees, tickets, and cases are always scoped to a client. Cross-client visibility is restricted to Admin and Super Admin roles only.','bi-building-lock','secondary'],
                        ['SLA Enforcement','Every ticket has an sla_due_at timestamp. Breached tickets appear in red on the dashboard. first_response_at is set on the first agent comment.','bi-stopwatch','danger'],
                        ['Audit Trail','All creates and updates are timestamped with created_at, updated_at. Key actions record the acting user (created_by, approved_by, dispatched_by, etc.).','bi-clock-history','warning'],
                        ['Financial Accuracy','PO totals = unit_price × quantity. grand_total = total_amount + tax_amount. Repair costs and insurance settlements feed directly into the Financial Report.','bi-calculator','success'],
                    ];
                    @endphp
                    @foreach($globalRules as $r)
                    <div class="col-md-6">
                        <div class="d-flex gap-3 p-3 border rounded">
                            <i class="bi {{ $r[3] ? 'text-'.$r[3] : '' }} {{ $r[0] }} bi-{{ substr($r[2],3) }}" style="font-size:1.4rem; flex-shrink:0">
                                <i class="bi {{ $r[2] }}" style="font-size:1.4rem; color: var(--bs-{{ $r[3] }})"></i>
                            </i>
                            <div>
                                <div class="fw-semibold small mb-1">{{ $r[0] }}</div>
                                <div class="text-muted" style="font-size:.8rem">{{ $r[1] }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── MODULE MAP ───────────────────────────────────────────────── --}}
        <div id="module-map-section">
        <div id="modules" class="card border-0 shadow-sm mb-4">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-map me-2 text-success"></i>Module Reference Map</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small">
                        <thead class="table-light">
                            <tr><th>Module</th><th>Key Models</th><th>Controller</th><th>Key Routes</th></tr>
                        </thead>
                        <tbody>
                            @php
                            $moduleMap = [
                                ['Procurement',  'DemandRequest, Rfq, VendorQuotation, PurchaseOrder, PoInvoice', 'ProcurementController', '/procurement/...'],
                                ['Inventory',    'Grn, Device, Location',                                          'InventoryController',   '/inventory/...'],
                                ['Devices',      'Device, DeviceModel, DeviceBrand, DeviceCategory',              'DeviceController',      '/devices/...'],
                                ['Dispatch',     'DispatchBatch, DispatchItem, CourierPartner',                   'DispatchController',    '/dispatches/...'],
                                ['Handovers',    'DeviceHandover, OwnershipHistory',                              'HandoverController',    '/handovers/...'],
                                ['Master Data',  'Vendor, VendorContact, Client, ClientProject, Employee',        'VendorController, ClientController, EmployeeController', '/vendors, /clients, /employees'],
                                ['Tickets',      'Ticket, TicketComment, TicketCategory',                         'TicketController',      '/tickets/...'],
                                ['Recovery',     'RecoveryCase, CallLog',                                         'RecoveryController',    '/recovery/...'],
                                ['Insurance',    'InsurancePolicy, InsuranceClaim, DeviceInsurance, InsuranceProvider', 'InsuranceController', '/insurance/...'],
                                ['Repairs',      'RepairOrder, ServiceCenter',                                    'RepairController',      '/repairs/...'],
                                ['Reports',      'Aggregated queries across all models',                          'ReportController',      '/reports/...'],
                            ];
                            @endphp
                            @foreach($moduleMap as $m)
                            <tr>
                                <td class="fw-semibold">{{ $m[0] }}</td>
                                <td class="text-muted font-monospace" style="font-size:.75rem">{{ $m[1] }}</td>
                                <td class="font-monospace" style="font-size:.75rem">{{ $m[2] }}</td>
                                <td class="text-primary font-monospace" style="font-size:.75rem">{{ $m[3] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        </div>{{-- /module-map-section --}}

        <div id="flow-footer" class="text-center text-muted py-3 small">
            <i class="bi bi-diagram-3 me-1"></i>
            Asset Lifecycle Management · 17-Phase Flow Document · {{ now()->year }}
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
// Highlight active TOC item on scroll
const sections = document.querySelectorAll('[id]');
const navLinks = document.querySelectorAll('.toc-nav .nav-link');
window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(s => { if (window.scrollY >= s.offsetTop - 120) current = s.id; });
    navLinks.forEach(l => {
        l.classList.remove('active');
        if (l.getAttribute('href') === '#' + current) l.classList.add('active');
    });
});
</script>
@endpush
