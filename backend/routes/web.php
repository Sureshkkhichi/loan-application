<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ApplicationController;
use App\Livewire\Auth\Login as LivewireLogin;
use App\Livewire\Dashboard as LivewireDashboard;
use App\Livewire\Applications\ApplicationDetail as LivewireApplicationDetail;
use App\Livewire\Home as LivewireHome;
use Illuminate\Support\Facades\Route;

// Public Home Portal (Livewire Reactive Landing & EMI Calculator)
Route::get('/', LivewireHome::class)->name('home');

// Authentication routes (Livewire interactive login + fallback POST)
Route::middleware('guest')->group(function () {
    Route::get('/login', LivewireLogin::class)->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Staff Web Portal routes (Livewire Full-Page Components)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', LivewireDashboard::class)->name('dashboard');

    Route::prefix('applications')->name('applications.')->group(function () {
        Route::get('/{id}', LivewireApplicationDetail::class)->name('show');

        // Form POST endpoints for automated test & backward compatibility
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
