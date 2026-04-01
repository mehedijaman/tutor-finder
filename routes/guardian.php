<?php

use App\Http\Controllers\Guardian\DashboardController as GuardianDashboardController;
use App\Http\Controllers\Guardian\FinanceController;
use App\Http\Controllers\Guardian\GuardianProfileController;
use App\Http\Controllers\Guardian\GuardianVerificationController;
use App\Http\Controllers\Guardian\JobApplicationController;
use App\Http\Controllers\Guardian\NotificationController;
use App\Http\Controllers\Guardian\SupportTicketController as GuardianSupportTicketController;
use App\Http\Controllers\Guardian\TermsOfServiceController as GuardianTermsOfServiceController;
use App\Http\Controllers\Guardian\TuitionJobController;
use App\Http\Controllers\Guardian\TutorialController as GuardianTutorialController;
use App\Http\Controllers\Guardian\TutorReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('guardian')
    ->name('guardian.')
    ->middleware(['auth', 'ensure.role:guardian', 'ensure.active'])
    ->group(function () {
        Route::redirect('/', '/guardian/dashboard')->name('home');
        Route::get('/dashboard', GuardianDashboardController::class)->name('dashboard');
        Route::get('/jobs', [TuitionJobController::class, 'index'])->name('jobs.index');
        Route::get('/jobs/pending', [TuitionJobController::class, 'pending'])->name('jobs.pending');
        Route::get('/jobs/live', [TuitionJobController::class, 'live'])->name('jobs.live');
        Route::get('/jobs/confirmed', [TuitionJobController::class, 'confirmed'])->name('jobs.confirmed');
        Route::get('/jobs/cancelled', [TuitionJobController::class, 'cancelled'])->name('jobs.cancelled');
        Route::get('/jobs/closed', [TuitionJobController::class, 'closed'])->name('jobs.closed');
        Route::get('/jobs/create', [TuitionJobController::class, 'create'])->name('jobs.create');
        Route::post('/jobs', [TuitionJobController::class, 'store'])->name('jobs.store');
        Route::post('/jobs/{tuitionJob}/request-tutor', [TuitionJobController::class, 'requestTutor'])->name('jobs.request-tutor');
        Route::get('/jobs/{tuitionJob}/applications', [JobApplicationController::class, 'index'])
            ->name('jobs.applications.index');
        Route::patch('/jobs/{tuitionJob}/applications/{tuitionJobApplication}/status', [JobApplicationController::class, 'updateStatus'])
            ->name('jobs.applications.status');
        Route::patch('/jobs/{tuitionJob}/applications/{tuitionJobApplication}/confirm', [JobApplicationController::class, 'confirm'])
            ->name('jobs.applications.confirm');
        Route::get('/profile', [GuardianProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [GuardianProfileController::class, 'update'])->name('profile.update');
        Route::get('/verification', [GuardianVerificationController::class, 'show'])->name('verification.show');
        Route::post('/verification/request', [GuardianVerificationController::class, 'store'])->name('verification.request');
        Route::get('/finance/invoices', [FinanceController::class, 'invoices'])->name('finance.invoices');
        Route::get('/support-tickets', [GuardianSupportTicketController::class, 'index'])->name('tickets.index');
        Route::get('/support-tickets/create', [GuardianSupportTicketController::class, 'create'])->name('tickets.create');
        Route::post('/support-tickets', [GuardianSupportTicketController::class, 'store'])->name('tickets.store');
        Route::get('/support-tickets/{supportTicket}', [GuardianSupportTicketController::class, 'show'])->name('tickets.show');
        Route::post('/support-tickets/{supportTicket}/reply', [GuardianSupportTicketController::class, 'reply'])->name('tickets.reply');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::patch('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

        Route::get('/reviews', [TutorReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews', [TutorReviewController::class, 'store'])->name('reviews.store');
        Route::patch('/reviews/restore-all', [TutorReviewController::class, 'restoreAll'])
            ->name('reviews.restore-all');
        Route::delete('/reviews/empty-trash', [TutorReviewController::class, 'emptyTrash'])
            ->name('reviews.empty-trash');
        Route::put('/reviews/{tutorReview}', [TutorReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{tutorReview}', [TutorReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::patch('/reviews/{tutorReview}/restore', [TutorReviewController::class, 'restore'])
            ->withTrashed()
            ->name('reviews.restore');
        Route::delete('/reviews/{tutorReview}/force-delete', [TutorReviewController::class, 'forceDelete'])
            ->withTrashed()
            ->name('reviews.force-delete');

        Route::get('/tutorials', [GuardianTutorialController::class, 'index'])->name('tutorials.index');
        Route::get('/terms-of-service', GuardianTermsOfServiceController::class)->name('terms-of-service');
    });
