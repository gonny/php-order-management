<?php

use App\Http\Controllers\Spa\ClientController;
use App\Http\Controllers\Spa\DashboardController;
use App\Http\Controllers\Spa\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SPA API Routes
|--------------------------------------------------------------------------
|
| These routes are for the Single Page Application (Svelte frontend)
| and use Laravel Sanctum for stateful session-based authentication.
| Users authenticate via standard Laravel auth, then Sanctum automatically
| handles API authentication for same-domain requests.
|
*/

// All SPA routes require authentication via existing session
Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/dashboard/metrics', [DashboardController::class, 'metrics']);
    
    // Orders
    Route::apiResource('orders', OrderController::class)->names([
        'index' => 'spa.orders.index',
        'store' => 'spa.orders.store',
        'show' => 'spa.orders.show',
        'update' => 'spa.orders.update',
        'destroy' => 'spa.orders.destroy',
    ]);
    Route::post('orders/{order}/transitions/{transition}', [OrderController::class, 'transition'])->name('spa.orders.transition');
    Route::post('orders/{order}/label', [OrderController::class, 'generateLabel'])->name('spa.orders.generateLabel');
    Route::post('orders/{order}/label/dpd', [OrderController::class, 'generateDpdLabel'])->name('spa.orders.generateDpdLabel');
    Route::delete('orders/{order}/shipment/dpd', [OrderController::class, 'deleteDpdShipment'])->name('spa.orders.deleteDpdShipment');
    Route::post('orders/{order}/tracking/refresh', [OrderController::class, 'refreshDpdTracking'])->name('spa.orders.refreshDpdTracking');
    Route::post('orders/{order}/pdf', [OrderController::class, 'generatePdf'])->name('spa.orders.generatePdf');
    
    // Clients
    Route::apiResource('clients', ClientController::class)->names([
        'index' => 'spa.clients.index',
        'store' => 'spa.clients.store',
        'show' => 'spa.clients.show',
        'update' => 'spa.clients.update',
        'destroy' => 'spa.clients.destroy',
    ]);
    
    // Label management
    Route::delete('labels/{label}', [OrderController::class, 'voidLabel'])->name('spa.labels.void');
    
    // User info endpoint for frontend
    Route::get('/auth/user', function () {
        return response()->json([
            'user' => auth()->user(),
        ]);
    });
});