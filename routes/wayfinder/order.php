<?php

use App\Http\Controllers\Spa\OrderController;
use Laravel\Wayfinder\Wayfinder;

Wayfinder::group([
    'prefix' => 'spa/v1/orders',
    'middleware' => ['web', 'auth:sanctum'],
], function () {
    Wayfinder::get('/', [OrderController::class, 'index'])->name('spa.orders.index');
    Wayfinder::post('/', [OrderController::class, 'store'])->name('spa.orders.store');
    Wayfinder::get('/{order}', [OrderController::class, 'show'])->name('spa.orders.show');
    Wayfinder::put('/{order}', [OrderController::class, 'update'])->name('spa.orders.update');
    Wayfinder::delete('/{order}', [OrderController::class, 'destroy'])->name('spa.orders.destroy');
    
    // Order transitions
    Wayfinder::post('/{order}/transition', [OrderController::class, 'transition'])->name('spa.orders.transition');
    
    // Order items (if managed separately)
    Wayfinder::post('/{order}/items', [OrderController::class, 'addItem'])->name('spa.orders.items.store');
    Wayfinder::put('/{order}/items/{item}', [OrderController::class, 'updateItem'])->name('spa.orders.items.update');
    Wayfinder::delete('/{order}/items/{item}', [OrderController::class, 'removeItem'])->name('spa.orders.items.destroy');
});