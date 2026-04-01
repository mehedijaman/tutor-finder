<?php

use App\Http\Controllers\JobController;
use App\Http\Controllers\Tutor\DashboardController as TutorDashboardController;
use App\Http\Controllers\Tutor\FinanceController;
use App\Http\Controllers\Tutor\JobApplicationController;
use App\Http\Controllers\Tutor\NotificationController;
use App\Http\Controllers\Tutor\RefundRequestController;
use App\Http\Controllers\Tutor\SupportTicketController as TutorSupportTicketController;
use App\Http\Controllers\Tutor\TermsOfServiceController as TutorTermsOfServiceController;
use App\Http\Controllers\Tutor\TutorialController as TutorTutorialController;
use App\Http\Controllers\Tutor\TutorProfileController;
use App\Http\Controllers\Tutor\TutorVerificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('tutor')
    ->name('tutor.')
    ->middleware(['auth', 'ensure.role:tutor', 'ensure.active'])
    ->group(function () {
        Route::redirect('/', '/tutor/dashboard')->name('home');
        Route::get('/dashboard', TutorDashboardController::class)->name('dashboard');
        Route::get('/jobs', [JobController::class, 'tutorIndex'])->name('jobs.index');
        Route::get('/jobs/{id}', [JobController::class, 'tutorShow'])->whereNumber('id')->name('jobs.show');
        Route::get('/job-applications', [JobApplicationController::class, 'index'])->name('job-applications.index');
        Route::redirect('/job-applications/applied', '/tutor/job-applications')->name('job-applications.applied');
        Route::get('/job-applications/shortlisted', [JobApplicationController::class, 'shortlisted'])->name('job-applications.shortlisted');
        Route::get('/job-applications/appointed', [JobApplicationController::class, 'appointed'])->name('job-applications.appointed');
        Route::get('/job-applications/confirmed', [JobApplicationController::class, 'confirmed'])->name('job-applications.confirmed');
        Route::get('/job-applications/cancelled', [JobApplicationController::class, 'cancelled'])->name('job-applications.cancelled');
        Route::post('/jobs/{tuitionJob}/apply', [JobApplicationController::class, 'store'])->whereNumber('tuitionJob')->name('jobs.apply');
        Route::patch('/job-applications/{tuitionJobApplication}/withdraw', [JobApplicationController::class, 'withdraw'])
            ->name('job-applications.withdraw');
        Route::get('/profile/download-cv', [TutorProfileController::class, 'downloadCv'])->name('profile.download-cv');
        Route::get('/profile/view-as-guardian', [TutorProfileController::class, 'viewAsGuardian'])->name('profile.view-as-guardian');
        Route::get('/profile', [TutorProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [TutorProfileController::class, 'update'])->name('profile.update');
        Route::get('/verification', [TutorVerificationController::class, 'show'])->name('verification.show');
        Route::post('/verification/request', [TutorVerificationController::class, 'store'])->name('verification.request');
        Route::get('/finance/invoices', [FinanceController::class, 'invoices'])->name('finance.invoices');
        Route::get('/finance/refunds', [RefundRequestController::class, 'index'])->name('finance.refunds.index');
        Route::post('/finance/refunds/{assignment}', [RefundRequestController::class, 'store'])->name('finance.refunds.store');
        Route::get('/support-tickets', [TutorSupportTicketController::class, 'index'])->name('tickets.index');
        Route::get('/support-tickets/create', [TutorSupportTicketController::class, 'create'])->name('tickets.create');
        Route::post('/support-tickets', [TutorSupportTicketController::class, 'store'])->name('tickets.store');
        Route::get('/support-tickets/{supportTicket}', [TutorSupportTicketController::class, 'show'])->name('tickets.show');
        Route::post('/support-tickets/{supportTicket}/reply', [TutorSupportTicketController::class, 'reply'])->name('tickets.reply');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::patch('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

        Route::get('/tutorials', [TutorTutorialController::class, 'index'])->name('tutorials.index');
        Route::get('/terms-of-service', TutorTermsOfServiceController::class)->name('terms-of-service');
    });
