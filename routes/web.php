<?php

use App\Http\Controllers\Auth\RoleDashboardRedirectController;
use App\Http\Controllers\Auth\VerifyOtpController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'index'])->name('home');
Route::get('/jobs', [SiteController::class, 'jobs'])->name('jobs');
Route::get('/faq', [SiteController::class, 'faq'])->name('faq');
Route::get('/blog', [SiteController::class, 'blog'])->name('blog');
Route::get('/contact', [SiteController::class, 'contact'])->name('contact');
Route::get('/privacy-policy', [SiteController::class, 'privacy'])->name('privacy-policy');
Route::get('/terms-of-service', [SiteController::class, 'terms'])->name('terms-of-service');

Route::middleware('auth')->group(function () {
    Route::get('/verify-otp', [VerifyOtpController::class, 'create'])->name('otp.verify');
    Route::post('/verify-otp', [VerifyOtpController::class, 'store'])
        ->middleware('throttle:otp-verify')
        ->name('otp.verify.store');

    Route::get('/dashboard', RoleDashboardRedirectController::class)->name('dashboard');
});

require __DIR__.'/tutor.php';
require __DIR__.'/guardian.php';
require __DIR__.'/admin.php';

require __DIR__.'/settings.php';
