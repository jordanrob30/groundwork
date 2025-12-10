<?php

use App\Http\Controllers\Api\ObservabilityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider and are assigned
| the "api" middleware group.
|
*/

// Observability endpoints for frontend metrics
Route::prefix('observability')->group(function () {
    Route::post('/web-vitals', [ObservabilityController::class, 'webVitals'])
        ->middleware('throttle:100,1')
        ->name('api.observability.web-vitals');

    Route::post('/js-error', [ObservabilityController::class, 'jsError'])
        ->middleware('throttle:50,1')
        ->name('api.observability.js-error');

    Route::post('/livewire-metrics', [ObservabilityController::class, 'livewireMetrics'])
        ->middleware('throttle:100,1')
        ->name('api.observability.livewire-metrics');

    Route::post('/page-load', [ObservabilityController::class, 'pageLoad'])
        ->middleware('throttle:100,1')
        ->name('api.observability.page-load');
});
