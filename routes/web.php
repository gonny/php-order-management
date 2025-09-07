<?php

use App\Http\Controllers\ClientWebController;
use App\Http\Controllers\OrderWebController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\QueueWebController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Order PDF routes (protected by auth middleware)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/orders/{order}/pdf', [PdfController::class, 'downloadOrderPdf'])->name('orders.pdf.download');
    Route::get('/orders/{order}/pdf-generation', [PdfController::class, 'showGenerationForm'])->name('orders.pdf.form');
    Route::get('/orders/{order}/label/dpd/download', [PdfController::class, 'downloadDpdLabel'])->name('orders.dpd.label.download');
});

// Order Management Web Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/orders', [OrderWebController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderWebController::class, 'create'])->name('orders.create');
    Route::get('/orders/{order}', [OrderWebController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/edit', [OrderWebController::class, 'edit'])->name('orders.edit');
});

// Client Management Web Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/clients', [ClientWebController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientWebController::class, 'create'])->name('clients.create');
    Route::get('/clients/{client}', [ClientWebController::class, 'show'])->name('clients.show');
    Route::get('/clients/{client}/edit', [ClientWebController::class, 'edit'])->name('clients.edit');
});

// Queue Management Web Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/queues', [QueueWebController::class, 'index'])->name('queues.index');
    Route::get('/queues/failed', [QueueWebController::class, 'failed'])->name('queues.failed');
});

// Audit Logs Web Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/audit-logs', function () {
        return Inertia::render('audit-logs/Index');
    })->name('audit-logs.index');
});

// Webhooks Management Web Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/webhooks', function () {
        return Inertia::render('webhooks/Index');
    })->name('webhooks.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
