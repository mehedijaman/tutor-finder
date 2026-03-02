<?php

use App\Http\Controllers\Guardian\DashboardController as GuardianDashboardController;
use App\Http\Controllers\Guardian\JobApplicationController;
use App\Http\Controllers\Guardian\NotificationController;
use App\Http\Controllers\Guardian\TuitionJobController;
use Illuminate\Support\Facades\Route;

Route::prefix('guardian')
    ->name('guardian.')
    ->middleware(['auth', 'ensure.role:guardian', 'ensure.active'])
    ->group(function () {
        Route::redirect('/', '/guardian/dashboard')->name('home');
        Route::get('/dashboard', GuardianDashboardController::class)->name('dashboard');
        Route::get('/jobs', [TuitionJobController::class, 'index'])->name('jobs.index');
        Route::get('/jobs/create', [TuitionJobController::class, 'create'])->name('jobs.create');
        Route::post('/jobs', [TuitionJobController::class, 'store'])->name('jobs.store');
        Route::get('/jobs/{tuitionJob}/applications', [JobApplicationController::class, 'index'])
            ->name('jobs.applications.index');
        Route::patch('/jobs/{tuitionJob}/applications/{tuitionJobApplication}/status', [JobApplicationController::class, 'updateStatus'])
            ->name('jobs.applications.status');
        Route::patch('/jobs/{tuitionJob}/applications/{tuitionJobApplication}/confirm', [JobApplicationController::class, 'confirm'])
            ->name('jobs.applications.confirm');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::patch('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    });
