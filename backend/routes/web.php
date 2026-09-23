<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ApplicationController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard/login
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Staff Web Portal routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('applications')->name('applications.')->group(function () {
        Route::get('/{id}', [ApplicationController::class, 'show'])->name('show');
        Route::post('/{id}/assign', [ApplicationController::class, 'assign'])->name('assign');
        Route::post('/{id}/update-details', [ApplicationController::class, 'updateDetails'])->name('update-details');
        Route::post('/{id}/upload-doc', [ApplicationController::class, 'uploadDocument'])->name('upload-doc');
        Route::post('/{id}/submit-review', [ApplicationController::class, 'submitReview'])->name('submit-review');

        // Manager / Admin Specific Actions
        Route::post('/{id}/ready-for-bank', [ApplicationController::class, 'readyForBank'])->name('ready-for-bank');
        Route::post('/{id}/reject', [ApplicationController::class, 'reject'])->name('reject');
        Route::post('/{id}/add-pendency', [ApplicationController::class, 'addPendency'])->name('add-pendency');
        Route::post('/{id}/complete', [ApplicationController::class, 'complete'])->name('complete');
    });
});
