<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InventoryItemController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\StockTransferController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public inventory routes (read-only)
Route::get('/inventory-items', [InventoryItemController::class, 'index']);
Route::get('/inventory-items/{id}', [InventoryItemController::class, 'show']);
Route::get('/warehouses', [WarehouseController::class, 'index']);
Route::get('/warehouses/{id}', [WarehouseController::class, 'show']);
Route::get('/warehouses/{id}/inventory', [WarehouseController::class, 'inventory']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Inventory Items Management
    Route::post('/inventory-items', [InventoryItemController::class, 'store']);
    Route::put('/inventory-items/{id}', [InventoryItemController::class, 'update']);
    Route::delete('/inventory-items/{id}', [InventoryItemController::class, 'destroy']);
    
    // Warehouses Management
    Route::post('/warehouses', [WarehouseController::class, 'store']);
    
    // Stock Management
    Route::get('/stocks', [StockController::class, 'index']);
    Route::post('/stocks', [StockController::class, 'store']);
    Route::get('/stocks/{id}', [StockController::class, 'show']);
    Route::put('/stocks/{id}', [StockController::class, 'update']);
    Route::delete('/stocks/{id}', [StockController::class, 'destroy']);
    
    // Stock Transfers Management
    Route::get('/stock-transfers', [StockTransferController::class, 'index']);
    Route::post('/stock-transfers', [StockTransferController::class, 'store']);
    Route::get('/stock-transfers/{id}', [StockTransferController::class, 'show']);
    Route::post('/stock-transfers/{id}/cancel', [StockTransferController::class, 'cancel']);
});
