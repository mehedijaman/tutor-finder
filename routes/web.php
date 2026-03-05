<?php

use App\Http\Controllers\Auth\RoleDashboardRedirectController;
use App\Http\Controllers\Auth\VerifyOtpController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Public\TutorController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\TutorialController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'index'])->name('home');
Route::get('/jobs', [JobController::class, 'index'])->name('jobs');
Route::get('/jobs/{slug}', [JobController::class, 'show'])->name('jobs.show');
Route::get('/tutors', [TutorController::class, 'index'])->name('tutors');
Route::get('/tutors/{id}', [TutorController::class, 'show'])->name('tutors.show');
Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');
Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact-form')
    ->name('contact.store');
Route::get('/tutorials', [TutorialController::class, 'index'])->name('tutorials');
Route::get('/privacy-policy', [SiteController::class, 'privacy'])->name('privacy-policy');
Route::get('/terms-of-service', [SiteController::class, 'terms'])->name('terms-of-service');
Route::get('/refund-policy', [SiteController::class, 'refundPolicy'])->name('refund-policy');
Route::get('/pages/{slug}', [SiteController::class, 'showPage'])->name('pages.show');

Route::middleware('auth')->group(function () {
    Route::get('/verify-otp', [VerifyOtpController::class, 'create'])->name('otp.verify');
    Route::post('/verify-otp', [VerifyOtpController::class, 'store'])
        ->middleware('throttle:otp-verify')
        ->name('otp.verify.store');
    Route::post('/verify-otp/resend', [VerifyOtpController::class, 'resend'])
        ->middleware('throttle:otp-resend')
        ->name('otp.verify.resend');

    Route::get('/dashboard', RoleDashboardRedirectController::class)->name('dashboard');
    Route::post('/impersonation/leave', [ImpersonationController::class, 'destroy'])->name('impersonation.leave');

    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
    Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');

    Route::post('/payment/bkash/{invoice}', [PaymentController::class, 'startBkash'])
        ->whereNumber('invoice')
        ->name('payment.bkash.start');
    Route::post('/payment/sslcommerz/{invoice}', [PaymentController::class, 'startSslCommerz'])
        ->whereNumber('invoice')
        ->name('payment.sslcommerz.start');
});

Route::get('/payment/bkash/callback', [PaymentController::class, 'bkashCallback'])->name('payment.bkash.callback');
Route::post('/payment/sslcommerz/ipn', [PaymentController::class, 'sslIpn'])->name('payment.sslcommerz.ipn');
Route::get('/payment/sslcommerz/success', [PaymentController::class, 'sslSuccess'])->name('payment.sslcommerz.success');
Route::get('/payment/sslcommerz/fail', [PaymentController::class, 'sslFail'])->name('payment.sslcommerz.fail');
Route::get('/payment/sslcommerz/cancel', [PaymentController::class, 'sslCancel'])->name('payment.sslcommerz.cancel');

require __DIR__.'/tutor.php';
require __DIR__.'/guardian.php';
require __DIR__.'/admin.php';

require __DIR__.'/settings.php';
