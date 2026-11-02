<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WasteCategoryController;
use App\Http\Controllers\Api\V1\WasteReportController;
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

    // Waste Categories (public read, auth write)
    Route::get('/categories', [WasteCategoryController::class, 'index']);
    Route::get('/categories/{category}', [WasteCategoryController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function (): void {
        // Category management
        Route::post('/categories', [WasteCategoryController::class, 'store']);
        Route::put('/categories/{category}', [WasteCategoryController::class, 'update']);
        Route::patch('/categories/{category}/status', [WasteCategoryController::class, 'toggleStatus']);
        Route::delete('/categories/{category}', [WasteCategoryController::class, 'destroy']);

        // Dashboard
        Route::get('/dashboard/admin', [DashboardController::class, 'admin']);
        Route::get('/dashboard/user', [DashboardController::class, 'user']);

        // User management
        Route::apiResource('users', UserController::class);
        Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus']);

        // Waste Reports
        Route::get('/reports', [WasteReportController::class, 'index']);
        Route::post('/reports', [WasteReportController::class, 'store']);
        Route::get('/reports/{report}', [WasteReportController::class, 'show']);
        Route::put('/reports/{report}', [WasteReportController::class, 'update']);
        Route::delete('/reports/{report}', [WasteReportController::class, 'destroy']);
        Route::patch('/reports/{report}/status', [WasteReportController::class, 'transitionStatus']);
        Route::get('/reports/{report}/history', [WasteReportController::class, 'history']);
        Route::post('/reports/{report}/images', [WasteReportController::class, 'uploadImages']);
        Route::delete('/reports/{report}/images/{image}', [WasteReportController::class, 'deleteImage']);
    });
});
