<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::middleware(['jwt.auth'])->group(function () {
        // Superadmin routes
        Route::middleware(['role:superadmin'])->group(function () {
            Route::apiResource('company', CompanyController::class);
        });

        // Manager routes
        Route::middleware(['role:manager'])->group(function () {
            Route::apiResource('user', UserController::class);
        });

        // Manager and Employee routes
        Route::middleware(['role:manager|employee'])->group(function () {
            Route::get('user', [UserController::class, 'index'])->name('users');
        });
    });
});

// Health check route
Route::get('/healthcheck', function () {
    return response()->json([
        'message' => 'App is running',
        'env' => config('app.env'),
        'sha' => config('app.sha'),
    ], 200);
});
