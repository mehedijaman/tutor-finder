<?php

use App\Http\Controllers\Admin\PaymentGatewaySettingController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SmsSettingController;
use App\Http\Controllers\Admin\SmtpSettingController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');
});

Route::middleware(['auth', 'ensure.role:admin', 'ensure.active'])->group(function () {
    Route::get('settings/site', [SiteSettingController::class, 'edit'])
        ->middleware('permission:site-setting-view')
        ->name('admin.site-settings.edit');
    Route::put('settings/site', [SiteSettingController::class, 'update'])
        ->middleware('permission:site-setting-update')
        ->name('admin.site-settings.update');
    Route::get('settings/payment', [PaymentGatewaySettingController::class, 'edit'])
        ->middleware('permission:payment-setting-view')
        ->name('admin.payment-settings.edit');
    Route::put('settings/payment', [PaymentGatewaySettingController::class, 'update'])
        ->middleware('permission:payment-setting-update')
        ->name('admin.payment-settings.update');

    Route::get('settings/sms', [SmsSettingController::class, 'index'])
        ->middleware('permission:sms-setting-view')
        ->name('admin.sms-settings.index');
    Route::get('settings/sms/create', [SmsSettingController::class, 'create'])
        ->middleware('permission:sms-setting-create')
        ->name('admin.sms-settings.create');
    Route::post('settings/sms', [SmsSettingController::class, 'store'])
        ->middleware('permission:sms-setting-create')
        ->name('admin.sms-settings.store');
    Route::get('settings/sms/{smsSetting}/edit', [SmsSettingController::class, 'edit'])
        ->middleware('permission:sms-setting-update')
        ->name('admin.sms-settings.edit');
    Route::put('settings/sms/{smsSetting}', [SmsSettingController::class, 'update'])
        ->middleware('permission:sms-setting-update')
        ->name('admin.sms-settings.update');
    Route::post('settings/sms/test', [SmsSettingController::class, 'testSms'])
        ->middleware('permission:sms-setting-update')
        ->name('admin.sms-settings.test');

    Route::get('settings/smtp', [SmtpSettingController::class, 'index'])
        ->middleware('permission:smtp-setting-view')
        ->name('admin.smtp-settings.index');
    Route::get('settings/smtp/create', [SmtpSettingController::class, 'create'])
        ->middleware('permission:smtp-setting-create')
        ->name('admin.smtp-settings.create');
    Route::post('settings/smtp', [SmtpSettingController::class, 'store'])
        ->middleware('permission:smtp-setting-create')
        ->name('admin.smtp-settings.store');
    Route::get('settings/smtp/{smtpSetting}/edit', [SmtpSettingController::class, 'edit'])
        ->middleware('permission:smtp-setting-update')
        ->name('admin.smtp-settings.edit');
    Route::put('settings/smtp/{smtpSetting}', [SmtpSettingController::class, 'update'])
        ->middleware('permission:smtp-setting-update')
        ->name('admin.smtp-settings.update');
    Route::delete('settings/smtp/{smtpSetting}', [SmtpSettingController::class, 'destroy'])
        ->middleware('permission:smtp-setting-delete')
        ->name('admin.smtp-settings.destroy');
    Route::post('settings/smtp/test', [SmtpSettingController::class, 'testEmail'])
        ->middleware('permission:smtp-setting-update')
        ->name('admin.smtp-settings.test');
});
