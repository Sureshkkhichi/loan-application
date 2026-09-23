<?php

use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\CustomerApplicationController;
use App\Http\Controllers\Api\CustomerPendencyController;
use App\Http\Controllers\Api\LoanTypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - LoanDesk Platform
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public Routes
    Route::get('/loan-types', [LoanTypeController::class, 'index']);

    // Customer Authentication (Mobile + OTP)
    Route::prefix('auth')->group(function () {
        Route::post('/send-otp', [CustomerAuthController::class, 'sendOtp']);
        Route::post('/verify-otp', [CustomerAuthController::class, 'verifyOtp']);
    });

    // Authenticated Customer Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [CustomerAuthController::class, 'me']);
        Route::post('/auth/logout', [CustomerAuthController::class, 'logout']);

        // Application Management
        Route::get('/applications/active', [CustomerApplicationController::class, 'getActive']);
        Route::post('/applications', [CustomerApplicationController::class, 'store']);

        // Pendency Resolution
        Route::post('/pendencies/{id}/resolve', [CustomerPendencyController::class, 'resolve']);
    });
});
