<?php

use App\Http\Controllers\Tutor\DashboardController as TutorDashboardController;
use App\Http\Controllers\Tutor\JobApplicationController;
use App\Http\Controllers\Tutor\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('tutor')
    ->name('tutor.')
    ->middleware(['auth', 'ensure.role:tutor', 'ensure.active'])
    ->group(function () {
        Route::redirect('/', '/tutor/dashboard')->name('home');
        Route::get('/dashboard', TutorDashboardController::class)->name('dashboard');
        Route::get('/job-applications', [JobApplicationController::class, 'index'])->name('job-applications.index');
        Route::post('/jobs/{tuitionJob:slug}/apply', [JobApplicationController::class, 'store'])->name('jobs.apply');
        Route::patch('/job-applications/{tuitionJobApplication}/withdraw', [JobApplicationController::class, 'withdraw'])
            ->name('job-applications.withdraw');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::patch('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    });
