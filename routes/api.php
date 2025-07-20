<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;



Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/available-seats', [BookingController::class, 'availableSeats']);
    Route::post('/book', [BookingController::class, 'book']);

});
