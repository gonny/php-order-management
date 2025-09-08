<?php

use App\Http\Controllers\Spa\ClientController;
use Laravel\Wayfinder\Wayfinder;

Wayfinder::group([
    'prefix' => 'spa/v1/clients',
    'middleware' => ['web', 'auth:sanctum'],
], function () {
    Wayfinder::get('/', [ClientController::class, 'index'])->name('spa.clients.index');
    Wayfinder::post('/', [ClientController::class, 'store'])->name('spa.clients.store');
    Wayfinder::get('/{client}', [ClientController::class, 'show'])->name('spa.clients.show');
    Wayfinder::put('/{client}', [ClientController::class, 'update'])->name('spa.clients.update');
    Wayfinder::delete('/{client}', [ClientController::class, 'destroy'])->name('spa.clients.destroy');
});