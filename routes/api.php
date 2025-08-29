<?php

use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Middleware\HmacAuthenticationMiddleware;
use App\Http\Middleware\RateLimitApiMiddleware;
use Illuminate\Support\Facades\Route;

// API v1 routes with HMAC authentication and rate limiting
Route::prefix("api/v1")->middleware([
    HmacAuthenticationMiddleware::class,
    RateLimitApiMiddleware::class . ":60",
])->group(function () {
    
    // Health check endpoint (no auth required)
    Route::get("/health", function () {
        return response()->json([
            "status" => "ok",
            "timestamp" => now()->toISOString(),
            "version" => "1.0.0",
        ]);
    });
    
    // Order management endpoints
    Route::apiResource("orders", OrderController::class);
    Route::post("orders/{order}/transition", [OrderController::class, "transition"]);
    Route::post("orders/{order}/label", [OrderController::class, "generateLabel"]);
    
    // Client management endpoints
    Route::apiResource("clients", ClientController::class)->only(["store", "show"]);
    
    // Label management
    Route::post("labels/{label}/void", [OrderController::class, "voidLabel"]);
    
    // Webhook endpoints
    Route::post("webhooks/incoming/{source}", [WebhookController::class, "receive"]);
});

// Webhook endpoints without authentication (they have their own signature validation)
Route::prefix("api/v1/webhooks")->group(function () {
    Route::post("balikovna", [WebhookController::class, "balikovna"]);
    Route::post("dpd", [WebhookController::class, "dpd"]);
    Route::post("payment", [WebhookController::class, "payment"]);
});
