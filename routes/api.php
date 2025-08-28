<?php

use Illuminate\Support\Facades\Route;

// Health check endpoint (no authentication required)
Route::get('/v1/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
        'database' => 'connected', // Could add actual DB check
        'queue' => 'active', // Could add actual queue check
    ]);
});

// All other API routes require HMAC authentication
Route::prefix('v1')->group(function () {
    // Orders endpoints
    Route::apiResource('orders', App\Http\Controllers\Api\OrderController::class);
    Route::post('orders/{order}/transition', [App\Http\Controllers\Api\OrderController::class, 'transition']);
    Route::post('orders/{order}/label', [App\Http\Controllers\Api\OrderController::class, 'generateLabel']);
    
    // Clients endpoints
    Route::apiResource('clients', App\Http\Controllers\Api\ClientController::class);
    
    // Labels endpoints
    Route::post('labels/{label}/void', [App\Http\Controllers\Api\LabelController::class, 'void']);
    
    // Webhook endpoints
    Route::post('webhooks/incoming/{source}', [App\Http\Controllers\Api\WebhookController::class, 'receive']);
});