<?php

use App\Http\Controllers\Guardian\DashboardController as GuardianDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('guardian')
    ->name('guardian.')
    ->middleware(['auth', 'ensure.role:guardian', 'ensure.active'])
    ->group(function () {
        Route::redirect('/', '/guardian/dashboard')->name('home');
        Route::get('/dashboard', GuardianDashboardController::class)->name('dashboard');
    });
