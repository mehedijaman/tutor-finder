<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController as AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\BackupManagementController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\BlogTagController;
use App\Http\Controllers\Admin\BlogUploadController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\GuardianManagementController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TutorManagementController;
use App\Http\Controllers\ImpersonationController;
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
        Route::get('/backups', [BackupManagementController::class, 'index'])
            ->middleware('permission:backup-view')
            ->name('backups.index');
        Route::post('/backups/run', [BackupManagementController::class, 'run'])
            ->middleware('permission:backup-run')
            ->name('backups.run');
        Route::post('/backups/clean', [BackupManagementController::class, 'clean'])
            ->middleware('permission:backup-clean')
            ->name('backups.clean');
        Route::get('/backups/download', [BackupManagementController::class, 'download'])
            ->middleware('permission:backup-download')
            ->name('backups.download');
        Route::delete('/backups/file', [BackupManagementController::class, 'destroy'])
            ->middleware('permission:backup-delete')
            ->name('backups.destroy');
        Route::get('/contact-messages', [ContactMessageController::class, 'index'])
            ->middleware('permission:contact-message-view')
            ->name('contact-messages.index');
        Route::get('/contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])
            ->middleware('permission:contact-message-view')
            ->name('contact-messages.show');
        Route::patch('/contact-messages/{contactMessage}/status', [ContactMessageController::class, 'updateStatus'])
            ->middleware('permission:contact-message-update')
            ->name('contact-messages.status');
        Route::get('/faqs', [AdminFaqController::class, 'index'])
            ->middleware('permission:faq-view')
            ->name('faqs.index');
        Route::get('/faqs/create', [AdminFaqController::class, 'create'])
            ->middleware('permission:faq-create')
            ->name('faqs.create');
        Route::post('/faqs', [AdminFaqController::class, 'store'])
            ->middleware('permission:faq-create')
            ->name('faqs.store');
        Route::get('/faqs/{faq}/edit', [AdminFaqController::class, 'edit'])
            ->middleware('permission:faq-update')
            ->name('faqs.edit');
        Route::put('/faqs/{faq}', [AdminFaqController::class, 'update'])
            ->middleware('permission:faq-update')
            ->name('faqs.update');
        Route::patch('/faqs/{faq}/status', [AdminFaqController::class, 'updateStatus'])
            ->middleware('permission:faq-update')
            ->name('faqs.status');
        Route::delete('/faqs/{faq}', [AdminFaqController::class, 'destroy'])
            ->middleware('permission:faq-delete')
            ->name('faqs.destroy');
        Route::patch('/faqs/{faq}/restore', [AdminFaqController::class, 'restore'])
            ->middleware('permission:faq-restore')
            ->withTrashed()
            ->name('faqs.restore');
        Route::delete('/faqs/{faq}/force', [AdminFaqController::class, 'forceDelete'])
            ->middleware('permission:faq-force-delete')
            ->withTrashed()
            ->name('faqs.force-delete');
        Route::delete('/faqs/recycle-bin/empty', [AdminFaqController::class, 'emptyRecycleBin'])
            ->middleware('permission:faq-force-delete')
            ->name('faqs.empty-recycle-bin');

        Route::prefix('blog')->name('blog.')->group(function () {
            Route::post('/uploads/images', [BlogUploadController::class, 'storeImage'])
                ->middleware('permission:blog-post-update')
                ->name('uploads.images');

            Route::get('/categories', [BlogCategoryController::class, 'index'])
                ->middleware('permission:blog-category-view')
                ->name('categories.index');
            Route::get('/categories/create', [BlogCategoryController::class, 'create'])
                ->middleware('permission:blog-category-create')
                ->name('categories.create');
            Route::post('/categories', [BlogCategoryController::class, 'store'])
                ->middleware('permission:blog-category-create')
                ->name('categories.store');
            Route::get('/categories/{blogCategory}/edit', [BlogCategoryController::class, 'edit'])
                ->middleware('permission:blog-category-update')
                ->name('categories.edit');
            Route::put('/categories/{blogCategory}', [BlogCategoryController::class, 'update'])
                ->middleware('permission:blog-category-update')
                ->name('categories.update');
            Route::delete('/categories/{blogCategory}', [BlogCategoryController::class, 'destroy'])
                ->middleware('permission:blog-category-delete')
                ->name('categories.destroy');
            Route::patch('/categories/{blogCategory}/restore', [BlogCategoryController::class, 'restore'])
                ->middleware('permission:blog-category-restore')
                ->withTrashed()
                ->name('categories.restore');
            Route::delete('/categories/{blogCategory}/force', [BlogCategoryController::class, 'forceDelete'])
                ->middleware('permission:blog-category-force-delete')
                ->withTrashed()
                ->name('categories.force-delete');
            Route::delete('/categories/recycle-bin/empty', [BlogCategoryController::class, 'emptyRecycleBin'])
                ->middleware('permission:blog-category-force-delete')
                ->name('categories.empty-recycle-bin');

            Route::get('/tags', [BlogTagController::class, 'index'])
                ->middleware('permission:blog-tag-view')
                ->name('tags.index');
            Route::get('/tags/create', [BlogTagController::class, 'create'])
                ->middleware('permission:blog-tag-create')
                ->name('tags.create');
            Route::post('/tags', [BlogTagController::class, 'store'])
                ->middleware('permission:blog-tag-create')
                ->name('tags.store');
            Route::get('/tags/{blogTag}/edit', [BlogTagController::class, 'edit'])
                ->middleware('permission:blog-tag-update')
                ->name('tags.edit');
            Route::put('/tags/{blogTag}', [BlogTagController::class, 'update'])
                ->middleware('permission:blog-tag-update')
                ->name('tags.update');
            Route::delete('/tags/{blogTag}', [BlogTagController::class, 'destroy'])
                ->middleware('permission:blog-tag-delete')
                ->name('tags.destroy');
            Route::patch('/tags/{blogTag}/restore', [BlogTagController::class, 'restore'])
                ->middleware('permission:blog-tag-restore')
                ->withTrashed()
                ->name('tags.restore');
            Route::delete('/tags/{blogTag}/force', [BlogTagController::class, 'forceDelete'])
                ->middleware('permission:blog-tag-force-delete')
                ->withTrashed()
                ->name('tags.force-delete');
            Route::delete('/tags/recycle-bin/empty', [BlogTagController::class, 'emptyRecycleBin'])
                ->middleware('permission:blog-tag-force-delete')
                ->name('tags.empty-recycle-bin');

            Route::get('/posts', [BlogPostController::class, 'index'])
                ->middleware('permission:blog-post-view')
                ->name('posts.index');
            Route::get('/posts/create', [BlogPostController::class, 'create'])
                ->middleware('permission:blog-post-create')
                ->name('posts.create');
            Route::post('/posts', [BlogPostController::class, 'store'])
                ->middleware('permission:blog-post-create')
                ->name('posts.store');
            Route::get('/posts/{blogPost}/edit', [BlogPostController::class, 'edit'])
                ->middleware('permission:blog-post-update')
                ->name('posts.edit');
            Route::put('/posts/{blogPost}', [BlogPostController::class, 'update'])
                ->middleware('permission:blog-post-update')
                ->name('posts.update');
            Route::delete('/posts/{blogPost}', [BlogPostController::class, 'destroy'])
                ->middleware('permission:blog-post-delete')
                ->name('posts.destroy');
            Route::patch('/posts/{blogPost}/restore', [BlogPostController::class, 'restore'])
                ->middleware('permission:blog-post-restore')
                ->withTrashed()
                ->name('posts.restore');
            Route::delete('/posts/{blogPost}/force', [BlogPostController::class, 'forceDelete'])
                ->middleware('permission:blog-post-force-delete')
                ->withTrashed()
                ->name('posts.force-delete');
            Route::delete('/posts/recycle-bin/empty', [BlogPostController::class, 'emptyRecycleBin'])
                ->middleware('permission:blog-post-force-delete')
                ->name('posts.empty-recycle-bin');
        });

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
        Route::post('/impersonation/{user}', [ImpersonationController::class, 'store'])
            ->name('impersonation.store');

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
