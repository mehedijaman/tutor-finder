<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController as AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GuardianManagementController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TutorManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthenticatedSessionController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'ensure.role:admin', 'ensure.active'])->group(function () {
        Route::redirect('/', '/admin/dashboard')->name('home');
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])
            ->middleware('permission:activity-log-view')
            ->name('activity-logs.index');

        Route::get('/users', [AdminUserController::class, 'index'])
            ->middleware('permission:admin-user-view')
            ->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])
            ->middleware('permission:admin-user-create')
            ->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])
            ->middleware('permission:admin-user-create')
            ->name('users.store');
        Route::delete('/users/recycle-bin/empty', [AdminUserController::class, 'emptyRecycleBin'])
            ->middleware('permission:admin-user-delete')
            ->name('users.empty-recycle-bin');
        Route::patch('/users/recycle-bin/restore-all', [AdminUserController::class, 'restoreAll'])
            ->middleware('permission:admin-user-delete')
            ->name('users.restore-all');
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
        Route::delete('/users/{user}/force', [AdminUserController::class, 'forceDelete'])
            ->middleware('permission:admin-user-delete')
            ->withTrashed()
            ->name('users.force-delete');

        Route::get('/roles', [RoleController::class, 'index'])
            ->middleware('permission:role-view')
            ->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])
            ->middleware('permission:role-create')
            ->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])
            ->middleware('permission:role-create')
            ->name('roles.store');
        Route::delete('/roles/recycle-bin/empty', [RoleController::class, 'emptyRecycleBin'])
            ->middleware('permission:role-delete')
            ->name('roles.empty-recycle-bin');
        Route::patch('/roles/recycle-bin/restore-all', [RoleController::class, 'restoreAll'])
            ->middleware('permission:role-delete')
            ->name('roles.restore-all');
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
        Route::delete('/roles/{role}/force', [RoleController::class, 'forceDelete'])
            ->middleware('permission:role-delete')
            ->withTrashed()
            ->name('roles.force-delete');

        Route::get('/tutors', [TutorManagementController::class, 'index'])
            ->middleware('permission:tutor-view')
            ->name('tutors.index');
        Route::delete('/tutors/recycle-bin/empty', [TutorManagementController::class, 'emptyRecycleBin'])
            ->middleware('permission:tutor-delete')
            ->name('tutors.empty-recycle-bin');
        Route::patch('/tutors/recycle-bin/restore-all', [TutorManagementController::class, 'restoreAll'])
            ->middleware('permission:tutor-delete')
            ->name('tutors.restore-all');
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
        Route::put('/tutors/{user}/password', [TutorManagementController::class, 'resetPassword'])
            ->middleware('permission:tutor-password-reset')
            ->name('tutors.reset-password');
        Route::patch('/tutors/{user}/restore', [TutorManagementController::class, 'restore'])
            ->middleware('permission:tutor-delete')
            ->withTrashed()
            ->name('tutors.restore');
        Route::delete('/tutors/{user}/force', [TutorManagementController::class, 'forceDelete'])
            ->middleware('permission:tutor-delete')
            ->withTrashed()
            ->name('tutors.force-delete');

        Route::get('/guardians', [GuardianManagementController::class, 'index'])
            ->middleware('permission:guardian-view')
            ->name('guardians.index');
        Route::delete('/guardians/recycle-bin/empty', [GuardianManagementController::class, 'emptyRecycleBin'])
            ->middleware('permission:guardian-delete')
            ->name('guardians.empty-recycle-bin');
        Route::patch('/guardians/recycle-bin/restore-all', [GuardianManagementController::class, 'restoreAll'])
            ->middleware('permission:guardian-delete')
            ->name('guardians.restore-all');
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
        Route::put('/guardians/{user}/password', [GuardianManagementController::class, 'resetPassword'])
            ->middleware('permission:guardian-password-reset')
            ->name('guardians.reset-password');
        Route::patch('/guardians/{user}/restore', [GuardianManagementController::class, 'restore'])
            ->middleware('permission:guardian-delete')
            ->withTrashed()
            ->name('guardians.restore');
        Route::delete('/guardians/{user}/force', [GuardianManagementController::class, 'forceDelete'])
            ->middleware('permission:guardian-delete')
            ->withTrashed()
            ->name('guardians.force-delete');

    });
});
