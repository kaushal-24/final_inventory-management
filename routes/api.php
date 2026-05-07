<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PurchaseOrderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::apiResource('products', ProductController::class)->names([
        'index' => 'api.products.index',
        'store' => 'api.products.store',
        'show' => 'api.products.show',
        'update' => 'api.products.update',
        'destroy' => 'api.products.destroy',
    ]);
    
    Route::prefix('inventory')->group(function () {
        Route::get('/stock-levels', [InventoryController::class, 'stockLevels'])->name('api.inventory.stock-levels');
        Route::post('/update-stock', [InventoryController::class, 'updateStock'])->name('api.inventory.update-stock');
        Route::get('/transactions', [InventoryController::class, 'transactions'])->name('api.inventory.transactions');
    });
    
    Route::apiResource('purchase-orders', PurchaseOrderController::class)->names([
        'index' => 'api.purchase-orders.index',
        'store' => 'api.purchase-orders.store',
        'show' => 'api.purchase-orders.show',
        'update' => 'api.purchase-orders.update',
        'destroy' => 'api.purchase-orders.destroy',
    ]);
    Route::put('purchase-orders/{id}/status', [PurchaseOrderController::class, 'updateStatus'])->name('api.purchase-orders.status');
});
