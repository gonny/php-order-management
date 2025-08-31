<?php

use App\Http\Controllers\PdfController;
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

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
