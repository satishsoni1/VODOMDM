<?php

use App\Http\Controllers\Api\DeviceApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    // Device lookup (search by serial, IMEI, asset tag)
    Route::get('/devices', [DeviceApiController::class, 'index']);
    Route::get('/devices/{device}', [DeviceApiController::class, 'show']);
    Route::get('/devices/{device}/timeline', [DeviceApiController::class, 'timeline']);
    Route::patch('/devices/{device}/status', [DeviceApiController::class, 'updateStatus']);

    // MDM webhook — no auth required for sync endpoint
});

// MDM sync endpoint (called by external MDM platform, uses token header)
Route::post('/mdm/sync', [DeviceApiController::class, 'mdmSync'])
    ->middleware('throttle:120,1');
