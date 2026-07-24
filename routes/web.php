<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Onboarding\CompanyOnboardingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ProductPageController;
use App\Http\Controllers\Web\Sales\SalesPageController;
use Inertia\Inertia;
use App\Http\Controllers\Web\CashRegisterPageController;
use App\Http\Controllers\Web\DashboardPageController;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::middleware('onboarding.pending')->prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('/company', [CompanyOnboardingController::class, 'create'])->name('company');
        Route::post('/company', [CompanyOnboardingController::class, 'store']);
    });

    Route::middleware('onboarding.complete')->group(function () {
    Route::get('/dashboard', [DashboardPageController::class, 'index'])->name('dashboard');
    });



    Route::get('/products', [ProductPageController::class, 'index'])
        ->name('products.index');

    Route::get('/products/create', [ProductPageController::class, 'create'])
        ->name('products.create');

        Route::get('/products/{id}/edit', [ProductPageController::class, 'edit'])
    ->name('products.edit');

     Route::get('/sales', [
        SalesPageController::class,
        'index'
    ])
    ->name('sales.index');


    /*
    |--------------------------------------------------------------------------
    | Punto de venta
    |--------------------------------------------------------------------------
    */

    Route::get('/sales/pos', [
        SalesPageController::class,
        'pos'
    ])
    ->name('sales.pos');

    // Agrega esta línea después de las rutas de ventas
    Route::get('/sales/ticket/{id}', [SalesPageController::class, 'ticket'])->name('sales.ticket');

    Route::get('/sales/customer-view', function () {
        return Inertia::render('Sales/CustomerView');
    })->name('sales.customer-view');

    Route::get('/cash-register', [CashRegisterPageController::class, 'index'])->name('cash-register.index');
});
