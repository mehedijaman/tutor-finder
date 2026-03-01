<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController as AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GuardianManagementController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SmsSettingController;
use App\Http\Controllers\Admin\TutorManagementController;
use App\Http\Controllers\Auth\RoleDashboardRedirectController;
use App\Http\Controllers\Auth\VerifyOtpController;
use App\Http\Controllers\Guardian\DashboardController as GuardianDashboardController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\Tutor\DashboardController as TutorDashboardController;
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

Route::prefix('tutor')
    ->name('tutor.')
    ->middleware(['auth', 'ensure.role:tutor', 'ensure.active'])
    ->group(function () {
        Route::redirect('/', '/tutor/dashboard')->name('home');
        Route::get('/dashboard', TutorDashboardController::class)->name('dashboard');
    });

Route::prefix('guardian')
    ->name('guardian.')
    ->middleware(['auth', 'ensure.role:guardian', 'ensure.active'])
    ->group(function () {
        Route::redirect('/', '/guardian/dashboard')->name('home');
        Route::get('/dashboard', GuardianDashboardController::class)->name('dashboard');
    });

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthenticatedSessionController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'ensure.role:admin', 'ensure.active'])->group(function () {
        Route::redirect('/', '/admin/dashboard')->name('home');
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

        Route::get('/users', [AdminUserController::class, 'index'])
            ->middleware('permission:admin-user-view')
            ->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])
            ->middleware('permission:admin-user-create')
            ->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])
            ->middleware('permission:admin-user-create')
            ->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])
            ->middleware('permission:admin-user-update')
            ->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])
            ->middleware('permission:admin-user-update')
            ->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])
            ->middleware('permission:admin-user-delete')
            ->name('users.destroy');
        Route::patch('/users/{user}/restore', [AdminUserController::class, 'restore'])
            ->middleware('permission:admin-user-delete')
            ->withTrashed()
            ->name('users.restore');

        Route::get('/roles', [RoleController::class, 'index'])
            ->middleware('permission:role-view')
            ->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])
            ->middleware('permission:role-create')
            ->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
            ->middleware('permission:role-update')
            ->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])
            ->middleware('permission:role-update')
            ->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
            ->middleware('permission:role-delete')
            ->name('roles.destroy');
        Route::patch('/roles/{role}/restore', [RoleController::class, 'restore'])
            ->middleware('permission:role-delete')
            ->withTrashed()
            ->name('roles.restore');

        Route::get('/tutors', [TutorManagementController::class, 'index'])
            ->middleware('permission:tutor-view')
            ->name('tutors.index');
        Route::get('/tutors/{user}', [TutorManagementController::class, 'show'])
            ->middleware('permission:tutor-view')
            ->name('tutors.show');
        Route::get('/tutors/{user}/edit', [TutorManagementController::class, 'edit'])
            ->middleware('permission:tutor-update')
            ->name('tutors.edit');
        Route::put('/tutors/{user}', [TutorManagementController::class, 'update'])
            ->middleware('permission:tutor-update')
            ->name('tutors.update');
        Route::patch('/tutors/{user}/status', [TutorManagementController::class, 'updateStatus'])
            ->middleware('permission:tutor-update')
            ->name('tutors.status');
        Route::delete('/tutors/{user}', [TutorManagementController::class, 'destroy'])
            ->middleware('permission:tutor-delete')
            ->name('tutors.destroy');
        Route::patch('/tutors/{user}/restore', [TutorManagementController::class, 'restore'])
            ->middleware('permission:tutor-delete')
            ->withTrashed()
            ->name('tutors.restore');

        Route::get('/guardians', [GuardianManagementController::class, 'index'])
            ->middleware('permission:guardian-view')
            ->name('guardians.index');
        Route::get('/guardians/{user}', [GuardianManagementController::class, 'show'])
            ->middleware('permission:guardian-view')
            ->name('guardians.show');
        Route::get('/guardians/{user}/edit', [GuardianManagementController::class, 'edit'])
            ->middleware('permission:guardian-update')
            ->name('guardians.edit');
        Route::put('/guardians/{user}', [GuardianManagementController::class, 'update'])
            ->middleware('permission:guardian-update')
            ->name('guardians.update');
        Route::patch('/guardians/{user}/status', [GuardianManagementController::class, 'updateStatus'])
            ->middleware('permission:guardian-update')
            ->name('guardians.status');
        Route::delete('/guardians/{user}', [GuardianManagementController::class, 'destroy'])
            ->middleware('permission:guardian-delete')
            ->name('guardians.destroy');
        Route::patch('/guardians/{user}/restore', [GuardianManagementController::class, 'restore'])
            ->middleware('permission:guardian-delete')
            ->withTrashed()
            ->name('guardians.restore');

        Route::get('/sms-settings', [SmsSettingController::class, 'index'])
            ->middleware('permission:sms-setting-view')
            ->name('sms-settings.index');
        Route::get('/sms-settings/create', [SmsSettingController::class, 'create'])
            ->middleware('permission:sms-setting-create')
            ->name('sms-settings.create');
        Route::post('/sms-settings', [SmsSettingController::class, 'store'])
            ->middleware('permission:sms-setting-create')
            ->name('sms-settings.store');
        Route::get('/sms-settings/{smsSetting}/edit', [SmsSettingController::class, 'edit'])
            ->middleware('permission:sms-setting-update')
            ->name('sms-settings.edit');
        Route::put('/sms-settings/{smsSetting}', [SmsSettingController::class, 'update'])
            ->middleware('permission:sms-setting-update')
            ->name('sms-settings.update');
    });
});

require __DIR__.'/settings.php';
