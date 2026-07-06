<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientOnboardingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HandoverController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecoveryController;
use App\Http\Controllers\RepairController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ApiLogController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\ClientUserController;
use App\Http\Controllers\MdmController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WhatsAppCampaignController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\WhatsAppTemplateController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// ── WhatsApp Webhook (public — no auth) ──────────────────────────────────────
Route::prefix('webhook/whatsapp')->name('whatsapp.webhook.')->group(function () {
    Route::get('/',  [WhatsAppWebhookController::class, 'verify'])->name('verify');
    Route::post('/', [WhatsAppWebhookController::class, 'receive'])->name('receive');
});

// ── Client Portal ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'client'])->prefix('client-portal')->name('client.')->group(function () {
    Route::get('/',          [ClientPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/devices',   [ClientPortalController::class, 'devices'])->name('devices');
    Route::get('/devices/{device}', [ClientPortalController::class, 'show'])->name('devices.show');
    Route::get('/employees', [ClientPortalController::class, 'employees'])->name('employees');
    Route::get('/tickets',   [ClientPortalController::class, 'tickets'])->name('tickets');
});

// ── Admin / Staff ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'redirect.client'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Master Data
    Route::resource('vendors', VendorController::class);
    Route::post('/vendors/{vendor}/contacts', [VendorController::class, 'storeContact'])->name('vendors.contacts.store');
    Route::delete('/vendors/{vendor}/contacts/{contact}', [VendorController::class, 'destroyContact'])->name('vendors.contacts.destroy');
    Route::resource('clients', ClientController::class);
    Route::post('/clients/{client}/projects', [ClientController::class, 'storeProject'])->name('clients.projects.store');

    // Guided Client Onboarding
    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('/', [ClientOnboardingController::class, 'start'])->name('start');
        Route::post('/', [ClientOnboardingController::class, 'storeClient'])->name('store');
    });
    Route::prefix('clients/{client}/onboarding')->name('onboarding.')->group(function () {
        Route::get('/employees', [ClientOnboardingController::class, 'employees'])->name('employees');
        Route::post('/employees', [ClientOnboardingController::class, 'storeEmployee'])->name('employees.store');
        Route::get('/devices', [ClientOnboardingController::class, 'devices'])->name('devices');
        Route::post('/devices', [ClientOnboardingController::class, 'assignDevices'])->name('devices.assign');
        Route::get('/finish', [ClientOnboardingController::class, 'finish'])->name('finish');
    });
    Route::resource('employees', EmployeeController::class);
    Route::get('/employees-import', [EmployeeController::class, 'importForm'])->name('employees.import.form');
    Route::post('/employees-import', [EmployeeController::class, 'import'])->name('employees.import');
    Route::get('/employees-template', [EmployeeController::class, 'downloadTemplate'])->name('employees.template');

    // Bulk assignment (company → employee, device → employee)
    Route::get('/employees-bulk-assign', [EmployeeController::class, 'bulkAssignForm'])->name('employees.bulk-assign.form');
    Route::post('/employees-bulk-assign/company', [EmployeeController::class, 'bulkAssignCompany'])->name('employees.bulk-assign.company');
    Route::get('/employees-bulk-assign/company-template', [EmployeeController::class, 'bulkCompanyTemplate'])->name('employees.bulk-assign.company-template');
    Route::post('/employees-bulk-assign/device', [EmployeeController::class, 'bulkAssignDevice'])->name('employees.bulk-assign.device');
    Route::get('/employees-bulk-assign/device-template', [EmployeeController::class, 'bulkDeviceTemplate'])->name('employees.bulk-assign.device-template');

    // Procurement
    Route::prefix('procurement')->name('procurement.')->group(function () {
        Route::get('/', [ProcurementController::class, 'index'])->name('index');
        Route::get('/demand-requests', [ProcurementController::class, 'demandRequests'])->name('demand-requests');
        Route::get('/demand-requests/create', [ProcurementController::class, 'createDemandRequest'])->name('demand-requests.create');
        Route::post('/demand-requests', [ProcurementController::class, 'storeDemandRequest'])->name('demand-requests.store');
        Route::get('/demand-requests/{demandRequest}', [ProcurementController::class, 'showDemandRequest'])->name('demand-requests.show');
        Route::post('/demand-requests/{demandRequest}/approve', [ProcurementController::class, 'approveDemandRequest'])->name('demand-requests.approve');
        Route::get('/rfqs', [ProcurementController::class, 'rfqs'])->name('rfqs');
        Route::get('/rfqs/create', [ProcurementController::class, 'createRfq'])->name('rfqs.create');
        Route::post('/rfqs', [ProcurementController::class, 'storeRfq'])->name('rfqs.store');
        Route::get('/rfqs/{rfq}', [ProcurementController::class, 'showRfq'])->name('rfqs.show');
        Route::post('/rfqs/{rfq}/quotations', [ProcurementController::class, 'storeQuotation'])->name('rfqs.quotations.store');
        Route::get('/purchase-orders', [ProcurementController::class, 'purchaseOrders'])->name('purchase-orders');
        Route::get('/purchase-orders/create', [ProcurementController::class, 'createPo'])->name('purchase-orders.create');
        Route::post('/purchase-orders', [ProcurementController::class, 'storePo'])->name('purchase-orders.store');
        Route::get('/purchase-orders/{purchaseOrder}', [ProcurementController::class, 'showPo'])->name('purchase-orders.show');
        Route::post('/purchase-orders/{purchaseOrder}/approve', [ProcurementController::class, 'approvePo'])->name('purchase-orders.approve');
        Route::post('/purchase-orders/{purchaseOrder}/invoices', [ProcurementController::class, 'storeInvoice'])->name('purchase-orders.invoices.store');
    });

    // Inventory / Warehouse
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/grn', [InventoryController::class, 'grnList'])->name('grn');
        Route::get('/grn/create', [InventoryController::class, 'createGrn'])->name('grn.create');
        Route::post('/grn', [InventoryController::class, 'storeGrn'])->name('grn.store');
        Route::get('/grn/{grn}', [InventoryController::class, 'showGrn'])->name('grn.show');
        Route::post('/grn/{grn}/devices', [InventoryController::class, 'registerDevice'])->name('grn.device.store');
        Route::post('/grn/{grn}/devices/bulk', [InventoryController::class, 'bulkRegisterDevices'])->name('grn.device.bulk');
    });

    // Devices
    Route::get('/devices-import', [DeviceController::class, 'importForm'])->name('devices.import.form');
    Route::post('/devices-import', [DeviceController::class, 'import'])->name('devices.import');
    Route::get('/devices-template', [DeviceController::class, 'downloadTemplate'])->name('devices.template');
    Route::resource('devices', DeviceController::class);

    // Dispatch
    Route::resource('dispatches', DispatchController::class);

    // Handovers
    Route::resource('handovers', HandoverController::class);

    // Tickets / Service Desk
    Route::resource('tickets', TicketController::class);
    Route::post('/tickets/{ticket}/comment', [TicketController::class, 'addComment'])->name('tickets.comment');
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');

    // Recovery
    Route::resource('recovery', RecoveryController::class);
    Route::post('/recovery/{recoveryCase}/call-log', [RecoveryController::class, 'addCallLog'])->name('recovery.call-log');
    Route::post('/recovery/{recoveryCase}/follow-up', [RecoveryController::class, 'addFollowUp'])->name('recovery.follow-up');

    // Insurance — custom routes BEFORE resource to avoid {insurance} binding 'claims'
    Route::get('/insurance/claims', [InsuranceController::class, 'claims'])->name('insurance.claims');
    Route::resource('insurance', InsuranceController::class);
    Route::post('/insurance/{insurancePolicy}/claims', [InsuranceController::class, 'storeClaim'])->name('insurance.claims.store');

    // Repairs
    Route::resource('repairs', RepairController::class);

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',                  [ReportController::class, 'index'])->name('index');
        Route::get('/inventory',         [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/procurement',       [ReportController::class, 'procurement'])->name('procurement');
        Route::get('/recovery',          [ReportController::class, 'recovery'])->name('recovery');
        Route::get('/insurance',         [ReportController::class, 'insurance'])->name('insurance');
        Route::get('/financial',         [ReportController::class, 'financial'])->name('financial');
        Route::get('/device-lifecycle',  [ReportController::class, 'deviceLifecycle'])->name('device-lifecycle');
        Route::get('/device-tracking',   [ReportController::class, 'deviceTracking'])->name('device-tracking');
    });

    // Client Portal User Management
    Route::resource('client-users', ClientUserController::class)->except(['show']);

    // WhatsApp Messaging
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        // Messages
        Route::get('/',              [WhatsAppController::class, 'index'])->name('index');
        Route::get('/compose',       [WhatsAppController::class, 'create'])->name('create');
        Route::post('/',             [WhatsAppController::class, 'store'])->name('store');
        Route::post('/{whatsapp}/send',   [WhatsAppController::class, 'sendNow'])->name('send');
        Route::post('/{whatsapp}/cancel', [WhatsAppController::class, 'cancel'])->name('cancel');
        Route::post('/process-due',  [WhatsAppController::class, 'processDue'])->name('process-due');
        Route::get('/settings',      [WhatsAppController::class, 'settings'])->name('settings');

        // Templates
        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/',          [WhatsAppTemplateController::class, 'index'])->name('index');
            Route::get('/create',    [WhatsAppTemplateController::class, 'create'])->name('create');
            Route::post('/',         [WhatsAppTemplateController::class, 'store'])->name('store');
            Route::get('/{whatsappTemplate}', [WhatsAppTemplateController::class, 'show'])->name('show');
            Route::post('/sync',     [WhatsAppTemplateController::class, 'sync'])->name('sync');
            Route::delete('/{whatsappTemplate}', [WhatsAppTemplateController::class, 'destroy'])->name('destroy');
        });

        // Campaigns
        Route::prefix('campaigns')->name('campaigns.')->group(function () {
            Route::get('/',          [WhatsAppCampaignController::class, 'index'])->name('index');
            Route::get('/create',    [WhatsAppCampaignController::class, 'create'])->name('create');
            Route::post('/',         [WhatsAppCampaignController::class, 'store'])->name('store');
            Route::get('/{campaign}',[WhatsAppCampaignController::class, 'show'])->name('show');
            Route::post('/{campaign}/launch', [WhatsAppCampaignController::class, 'launch'])->name('launch');
            Route::post('/{campaign}/cancel', [WhatsAppCampaignController::class, 'cancel'])->name('cancel');
        });
    });

    // API Logs
    Route::prefix('api-logs')->name('api-logs.')->group(function () {
        Route::get('/',         [ApiLogController::class, 'index'])->name('index');
        Route::get('/{apiLog}', [ApiLogController::class, 'show'])->name('show');
    });

    // Global Search
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Flow Document
    Route::get('/flow-document', fn () => view('flow'))->name('flow');

    // MDM Portal
    Route::prefix('mdm')->name('mdm.')->group(function () {
        Route::get('/',                [MdmController::class, 'dashboard'])->name('index');
        Route::get('/sync',            [MdmController::class, 'sync'])->name('sync');
        Route::post('/sync/run',       [MdmController::class, 'runSync'])->name('sync.run');
        Route::get('/sync/progress',   [MdmController::class, 'syncProgress'])->name('sync.progress');
        Route::post('/sync/automatch', [MdmController::class, 'autoMatch'])->name('sync.automatch');
        Route::get('/devices',         [MdmController::class, 'devices'])->name('devices');
        Route::get('/devices/{mdm}',   [MdmController::class, 'show'])->name('show');
        Route::get('/link',            [MdmController::class, 'link'])->name('link');
        Route::post('/link/{mdm}',     [MdmController::class, 'saveLink'])->name('link.save');
        Route::get('/map',             [MdmController::class, 'map'])->name('map');
    });
});

require __DIR__.'/auth.php';
