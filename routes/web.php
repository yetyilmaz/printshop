<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\PublicController::class, 'home'])->name('home');
Route::get('/portfolio', [App\Http\Controllers\PublicController::class, 'portfolio'])->name('portfolio');
Route::get('/portfolio/{slug}', [App\Http\Controllers\PublicController::class, 'portfolioItem'])->name('portfolio.show');
Route::get('/materials', [App\Http\Controllers\PublicController::class, 'materialsGuide'])->name('materials.guide');
Route::get('/order', [App\Http\Controllers\OrderController::class, 'create'])->name('order.create');
Route::post('/order', [App\Http\Controllers\OrderController::class, 'store'])->name('order.store');
Route::get('/order/success', [App\Http\Controllers\OrderController::class, 'success'])->name('order.success');


Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('categories', App\Http\Controllers\Admin\PortfolioCategoryController::class);
        Route::resource('portfolio', App\Http\Controllers\Admin\PortfolioController::class);
        Route::resource('orders', App\Http\Controllers\Admin\OrderController::class);
        Route::get('orders/file/{file}', [App\Http\Controllers\Admin\OrderController::class, 'download'])->name('orders.download');
        Route::post('orders/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.status');
        Route::resource('materials', App\Http\Controllers\Admin\MaterialController::class);
        
        Route::put('calculator/update', [App\Http\Controllers\Admin\CalculatorController::class, 'bulkUpdate'])->name('calculator.bulk-update');
        Route::resource('calculator', App\Http\Controllers\Admin\CalculatorController::class)->except(['show', 'edit', 'create', 'update']);
    });
});

require __DIR__ . '/auth.php';
