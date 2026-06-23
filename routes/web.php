<?php

use App\Http\Controllers\ClientController;
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
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\ClientUserController;
use App\Http\Controllers\MdmController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

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
    Route::resource('employees', EmployeeController::class);

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
    });

    // Client Portal User Management
    Route::resource('client-users', ClientUserController::class)->except(['show']);

    // Global Search
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Flow Document
    Route::get('/flow-document', fn () => view('flow'))->name('flow');

    // MDM Portal
    Route::prefix('mdm')->name('mdm.')->group(function () {
        Route::get('/',              [MdmController::class, 'index'])->name('index');
        Route::get('/devices',       [MdmController::class, 'devices'])->name('devices');
        Route::get('/employees',     [MdmController::class, 'employees'])->name('employees');
        Route::get('/import',        [MdmController::class, 'import'])->name('import');
        Route::post('/import',       [MdmController::class, 'processImport'])->name('import.process');
        Route::post('/auto-match',   [MdmController::class, 'autoMatch'])->name('auto-match');
        Route::get('/map',           [MdmController::class, 'map'])->name('map');
        Route::get('/{mdm}',         [MdmController::class, 'show'])->name('show');
        Route::post('/{mdm}/link-employee', [MdmController::class, 'linkEmployee'])->name('link-employee');
    });
});

require __DIR__.'/auth.php';
