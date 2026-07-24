<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Web\Sales\SalesPageController;
use App\Http\Controllers\Web\Sales\StoreSaleController;
use App\Http\Controllers\Api\DashboardController;

        use App\Http\Controllers\Api\CashRegisterController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth')->group(function () {

    Route::apiResource('products', ProductController::class);

    Route::post('/sales', StoreSaleController::class)
        ->name('api.sales.store');

    Route::get('/cash-register/current', [CashRegisterController::class, 'current']);
    Route::post('/cash-register/open', [CashRegisterController::class, 'open']);
    Route::post('/cash-register/close', [CashRegisterController::class, 'close']);
    Route::get('/cash-register/report/{id}', [CashRegisterController::class, 'report']);
    Route::get('/cash-register/history', [CashRegisterController::class, 'history']);
    Route::get('/dashboard/stats', [DashboardController::class, 'index']);

});
