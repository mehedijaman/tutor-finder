<?php

use App\Http\Controllers\Auth\RoleDashboardRedirectController;
use App\Http\Controllers\Auth\VerifyOtpController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'index'])->name('home');
Route::get('/jobs', [JobController::class, 'index'])->name('jobs');
Route::get('/jobs/{slug}', [JobController::class, 'show'])->name('jobs.show');
Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');
Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact-form')
    ->name('contact.store');
Route::get('/privacy-policy', [SiteController::class, 'privacy'])->name('privacy-policy');
Route::get('/terms-of-service', [SiteController::class, 'terms'])->name('terms-of-service');

Route::middleware('auth')->group(function () {
    Route::get('/verify-otp', [VerifyOtpController::class, 'create'])->name('otp.verify');
    Route::post('/verify-otp', [VerifyOtpController::class, 'store'])
        ->middleware('throttle:otp-verify')
        ->name('otp.verify.store');

    Route::get('/dashboard', RoleDashboardRedirectController::class)->name('dashboard');
    Route::post('/impersonation/leave', [ImpersonationController::class, 'destroy'])->name('impersonation.leave');
});

require __DIR__.'/tutor.php';
require __DIR__.'/guardian.php';
require __DIR__.'/admin.php';

require __DIR__.'/settings.php';
