<?php

use App\Http\Controllers\Tutor\DashboardController as TutorDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('tutor')
    ->name('tutor.')
    ->middleware(['auth', 'ensure.role:tutor', 'ensure.active'])
    ->group(function () {
        Route::redirect('/', '/tutor/dashboard')->name('home');
        Route::get('/dashboard', TutorDashboardController::class)->name('dashboard');
    });
