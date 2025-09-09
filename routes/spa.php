<?php

use App\Http\Controllers\Spa\AuditLogController;
use App\Http\Controllers\Spa\ClientController;
use App\Http\Controllers\Spa\DashboardController;
use App\Http\Controllers\Spa\OrderController;
use App\Http\Controllers\Spa\QueueController;
use App\Http\Controllers\Spa\WebhookController;
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
Route::group([], function () {
    // Dashboard
    Route::get('/dashboard/metrics', [DashboardController::class, 'metrics'])->name('spa.dashboard.metrics');

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

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('spa.audit-logs.index');
    Route::get('/audit-logs/stats', [AuditLogController::class, 'stats'])->name('spa.audit-logs.stats');
    Route::get('/orders/{order}/audit-logs', [AuditLogController::class, 'orderAuditLogs'])->name('spa.orders.audit-logs');

    // Webhooks
    Route::get('/webhooks', [WebhookController::class, 'index'])->name('spa.webhooks.index');
    Route::get('/webhooks/{webhook}', [WebhookController::class, 'show'])->name('spa.webhooks.show');
    Route::post('/webhooks/{webhook}/reprocess', [WebhookController::class, 'reprocess'])->name('spa.webhooks.reprocess');

    // Queues
    Route::get('/queues/stats', [QueueController::class, 'stats'])->name('spa.queues.stats');
    Route::get('/queues/failed', [QueueController::class, 'failedJobs'])->name('spa.queues.failed');
    Route::get('/queues/recent', [QueueController::class, 'recentJobs'])->name('spa.queues.recent');
    Route::post('/queues/failed/{jobId}/retry', [QueueController::class, 'retryJob'])->name('spa.queues.retry');
    Route::delete('/queues/failed/{jobId}', [QueueController::class, 'deleteFailedJob'])->name('spa.queues.delete');

    // User info endpoint for frontend
    Route::get('/auth/user', function () {
        return response()->json([
            'user' => auth()->user(),
        ]);
    });
});
