<?php

use App\Http\Controllers\Api\DeviceApiController;
use App\Http\Controllers\Api\EmployeeApiController;
use Illuminate\Support\Facades\Route;

// ── Employee Master API (token-protected) ────────────────────────────────────
Route::middleware(['api.token'])->prefix('v1')->group(function () {
    Route::get('/employees',          [EmployeeApiController::class, 'index']);
    Route::get('/employees/{code}',   [EmployeeApiController::class, 'show']);
    Route::post('/employees/sync',    [EmployeeApiController::class, 'sync']);
});

// ── Device API (Sanctum) ─────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/devices',                    [DeviceApiController::class, 'index']);
    Route::get('/devices/{device}',           [DeviceApiController::class, 'show']);
    Route::get('/devices/{device}/timeline',  [DeviceApiController::class, 'timeline']);
    Route::patch('/devices/{device}/status',  [DeviceApiController::class, 'updateStatus']);
});

// ── MDM webhook (external, token-protected) ──────────────────────────────────
Route::post('/mdm/sync', [DeviceApiController::class, 'mdmSync'])
    ->middleware('throttle:120,1');
