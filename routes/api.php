<?php

use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\QueueController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Middleware\HmacAuthenticationMiddleware;
use App\Http\Middleware\RateLimitApiMiddleware;
use Illuminate\Support\Facades\Route;

// API v1 routes with HMAC authentication and rate limiting
Route::prefix('v1')->middleware([
    HmacAuthenticationMiddleware::class,
    RateLimitApiMiddleware::class . ':60',
])->group(function () {

    // Health check endpoint (no auth required)
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
        ]);
    });

    // Dashboard metrics endpoint
    Route::get('dashboard/metrics', [DashboardController::class, 'metrics']);

    // Order management endpoints
    Route::apiResource('orders', OrderController::class);
    Route::post('orders/{order}/transitions/{transition}', [OrderController::class, 'transition']);
    Route::post('orders/{order}/label', [OrderController::class, 'generateLabel']);
    Route::post('orders/{order}/label/dpd', [OrderController::class, 'generateDpdLabel']);
    Route::delete('orders/{order}/shipment/dpd', [OrderController::class, 'deleteDpdShipment']);
    Route::post('orders/{order}/tracking/refresh', [OrderController::class, 'refreshDpdTracking']);
    Route::post('orders/{order}/pdf', [OrderController::class, 'generatePdf'])->middleware(RateLimitApiMiddleware::class . ':10');
    Route::post('orders/{order}/pdf/r2', [OrderController::class, 'generatePdfFromR2'])->middleware(RateLimitApiMiddleware::class . ':10');

    // Client management endpoints (now with full CRUD)
    Route::apiResource('clients', ClientController::class);

    // Label management (fixed route pattern)
    Route::delete('labels/{label}', [OrderController::class, 'voidLabel']);

    // Queue management endpoints
    Route::get('queues/stats', [QueueController::class, 'stats']);
    Route::get('queues/failed', [QueueController::class, 'failedJobs']);
    Route::get('queues/recent', [QueueController::class, 'recentJobs']);
    Route::post('queues/failed/{jobId}/retry', [QueueController::class, 'retryJob']);
    Route::delete('queues/failed/{jobId}', [QueueController::class, 'deleteFailedJob']);
    Route::delete('queues/failed', [QueueController::class, 'clearFailedJobs']);

    // Webhook endpoints
    Route::get('webhooks', [WebhookController::class, 'index']);
    Route::post('webhooks/incoming/{source}', [WebhookController::class, 'receive']);
});

// Webhook endpoints without authentication (they have their own signature validation)
Route::prefix('v1/webhooks')->group(function () {
    Route::post('balikovna', [WebhookController::class, 'balikovna']);
    Route::post('dpd', [WebhookController::class, 'dpd']);
    Route::post('payment', [WebhookController::class, 'payment']);
});
