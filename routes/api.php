<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WasteCategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Health Check
    Route::get('/health', HealthController::class);

    // Authentication
    Route::prefix('auth')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::put('/change-password', [AuthController::class, 'changePassword']);
        });
    });

    // Waste Categories (GET is accessible, modifications require auth)
    Route::get('/categories', [WasteCategoryController::class, 'index']);
    Route::get('/categories/{category}', [WasteCategoryController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function (): void {
        // Category management
        Route::post('/categories', [WasteCategoryController::class, 'store']);
        Route::put('/categories/{category}', [WasteCategoryController::class, 'update']);
        Route::patch('/categories/{category}/status', [WasteCategoryController::class, 'toggleStatus']);
        Route::delete('/categories/{category}', [WasteCategoryController::class, 'destroy']);

        // User management
        Route::apiResource('users', UserController::class);
        Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus']);
    });
});
