<?php

use App\Http\Controllers\Tutor\DashboardController as TutorDashboardController;
use App\Http\Controllers\Tutor\JobApplicationController;
use App\Http\Controllers\Tutor\NotificationController;
use App\Http\Controllers\Tutor\TutorProfileController;
use App\Http\Controllers\Tutor\TutorVerificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('tutor')
    ->name('tutor.')
    ->middleware(['auth', 'ensure.role:tutor', 'ensure.active'])
    ->group(function () {
        Route::redirect('/', '/tutor/dashboard')->name('home');
        Route::get('/dashboard', TutorDashboardController::class)->name('dashboard');
        Route::get('/job-applications', [JobApplicationController::class, 'index'])->name('job-applications.index');
        Route::get('/job-applications/applied', [JobApplicationController::class, 'applied'])->name('job-applications.applied');
        Route::get('/job-applications/shortlisted', [JobApplicationController::class, 'shortlisted'])->name('job-applications.shortlisted');
        Route::get('/job-applications/appointed', [JobApplicationController::class, 'appointed'])->name('job-applications.appointed');
        Route::get('/job-applications/confirmed', [JobApplicationController::class, 'confirmed'])->name('job-applications.confirmed');
        Route::get('/job-applications/cancelled', [JobApplicationController::class, 'cancelled'])->name('job-applications.cancelled');
        Route::post('/jobs/{tuitionJob:slug}/apply', [JobApplicationController::class, 'store'])->name('jobs.apply');
        Route::patch('/job-applications/{tuitionJobApplication}/withdraw', [JobApplicationController::class, 'withdraw'])
            ->name('job-applications.withdraw');
        Route::get('/profile', [TutorProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [TutorProfileController::class, 'update'])->name('profile.update');
        Route::get('/verification', [TutorVerificationController::class, 'show'])->name('verification.show');
        Route::post('/verification/request', [TutorVerificationController::class, 'store'])->name('verification.request');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::patch('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    });
