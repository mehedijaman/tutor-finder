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
use App\Http\Controllers\Admin\Finance\InvoiceController as FinanceInvoiceController;
use App\Http\Controllers\Admin\Finance\LedgerController as FinanceLedgerController;
use App\Http\Controllers\Admin\Finance\PaymentController as FinancePaymentController;
use App\Http\Controllers\Admin\Finance\RefundRequestController as FinanceRefundRequestController;
use App\Http\Controllers\Admin\GuardianManagementController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\JobController as AdminJobController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\Tuition\Taxonomies\AreaController as TaxonomyAreaController;
use App\Http\Controllers\Admin\Tuition\Taxonomies\CategoryController as TaxonomyCategoryController;
use App\Http\Controllers\Admin\Tuition\Taxonomies\CityController as TaxonomyCityController;
use App\Http\Controllers\Admin\Tuition\Taxonomies\CountryController as TaxonomyCountryController;
use App\Http\Controllers\Admin\Tuition\Taxonomies\SchoolClassController as TaxonomySchoolClassController;
use App\Http\Controllers\Admin\Tuition\Taxonomies\SubjectController as TaxonomySubjectController;
use App\Http\Controllers\Admin\Tuition\Taxonomies\TuitionTypeController as TaxonomyTuitionTypeController;
use App\Http\Controllers\Admin\TutorManagementController;
use App\Http\Controllers\Admin\VerificationRequestController;
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

        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/', [AdminJobController::class, 'index'])
                ->middleware('permission:job-view')
                ->name('index');
            Route::get('/pending', [AdminJobController::class, 'pending'])
                ->middleware('permission:job-view')
                ->name('pending');
            Route::get('/live', [AdminJobController::class, 'live'])
                ->middleware('permission:job-view')
                ->name('live');
            Route::get('/expired', [AdminJobController::class, 'expired'])
                ->middleware('permission:job-view')
                ->name('expired');
            Route::get('/confirmed', [AdminJobController::class, 'confirmed'])
                ->middleware('permission:job-view')
                ->name('confirmed');
            Route::get('/cancelled', [AdminJobController::class, 'cancelled'])
                ->middleware('permission:job-view')
                ->name('cancelled');
            Route::get('/{job}/applications', [AdminJobController::class, 'applications'])
                ->middleware('permission:job-view')
                ->name('applications');

            Route::get('/create', [AdminJobController::class, 'create'])
                ->middleware('permission:job-create')
                ->name('create');
            Route::post('/', [AdminJobController::class, 'store'])
                ->middleware('permission:job-create')
                ->name('store');

            Route::get('/{job}/edit', [AdminJobController::class, 'edit'])
                ->middleware('permission:job-update')
                ->name('edit');
            Route::put('/{job}', [AdminJobController::class, 'update'])
                ->middleware('permission:job-update')
                ->name('update');

            Route::patch('/{job}/approve', [AdminJobController::class, 'approve'])
                ->middleware('permission:job-approve')
                ->name('approve');
            Route::patch('/{job}/status', [AdminJobController::class, 'status'])
                ->middleware('permission:job-update')
                ->name('status');

            Route::delete('/{job}', [AdminJobController::class, 'destroy'])
                ->middleware('permission:job-delete')
                ->name('destroy');
            Route::patch('/{job}/restore', [AdminJobController::class, 'restore'])
                ->middleware('permission:job-restore')
                ->withTrashed()
                ->name('restore');
            Route::delete('/{job}/force', [AdminJobController::class, 'forceDelete'])
                ->middleware('permission:job-force-delete')
                ->withTrashed()
                ->name('force-delete');
            Route::delete('/recycle-bin/empty', [AdminJobController::class, 'emptyRecycleBin'])
                ->middleware('permission:job-force-delete')
                ->name('empty-recycle-bin');
        });

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

        Route::prefix('tuition/taxonomies')->name('tuition.taxonomies.')->group(function () {
            Route::get('/countries', [TaxonomyCountryController::class, 'index'])
                ->middleware('permission:country-view')
                ->name('countries.index');
            Route::get('/countries/create', [TaxonomyCountryController::class, 'create'])
                ->middleware('permission:country-create')
                ->name('countries.create');
            Route::post('/countries', [TaxonomyCountryController::class, 'store'])
                ->middleware('permission:country-create')
                ->name('countries.store');
            Route::get('/countries/{country}/edit', [TaxonomyCountryController::class, 'edit'])
                ->middleware('permission:country-update')
                ->name('countries.edit');
            Route::put('/countries/{country}', [TaxonomyCountryController::class, 'update'])
                ->middleware('permission:country-update')
                ->name('countries.update');
            Route::patch('/countries/{country}/status', [TaxonomyCountryController::class, 'updateStatus'])
                ->middleware('permission:country-update')
                ->name('countries.status');
            Route::delete('/countries/{country}', [TaxonomyCountryController::class, 'destroy'])
                ->middleware('permission:country-delete')
                ->name('countries.destroy');
            Route::patch('/countries/{country}/restore', [TaxonomyCountryController::class, 'restore'])
                ->middleware('permission:country-restore')
                ->withTrashed()
                ->name('countries.restore');
            Route::delete('/countries/{country}/force', [TaxonomyCountryController::class, 'forceDelete'])
                ->middleware('permission:country-force-delete')
                ->withTrashed()
                ->name('countries.force-delete');
            Route::delete('/countries/recycle-bin/empty', [TaxonomyCountryController::class, 'emptyRecycleBin'])
                ->middleware('permission:country-force-delete')
                ->name('countries.empty-recycle-bin');

            Route::get('/cities', [TaxonomyCityController::class, 'index'])
                ->middleware('permission:city-view')
                ->name('cities.index');
            Route::get('/cities/create', [TaxonomyCityController::class, 'create'])
                ->middleware('permission:city-create')
                ->name('cities.create');
            Route::post('/cities', [TaxonomyCityController::class, 'store'])
                ->middleware('permission:city-create')
                ->name('cities.store');
            Route::get('/cities/{city}/edit', [TaxonomyCityController::class, 'edit'])
                ->middleware('permission:city-update')
                ->name('cities.edit');
            Route::put('/cities/{city}', [TaxonomyCityController::class, 'update'])
                ->middleware('permission:city-update')
                ->name('cities.update');
            Route::patch('/cities/{city}/status', [TaxonomyCityController::class, 'updateStatus'])
                ->middleware('permission:city-update')
                ->name('cities.status');
            Route::delete('/cities/{city}', [TaxonomyCityController::class, 'destroy'])
                ->middleware('permission:city-delete')
                ->name('cities.destroy');
            Route::patch('/cities/{city}/restore', [TaxonomyCityController::class, 'restore'])
                ->middleware('permission:city-restore')
                ->withTrashed()
                ->name('cities.restore');
            Route::delete('/cities/{city}/force', [TaxonomyCityController::class, 'forceDelete'])
                ->middleware('permission:city-force-delete')
                ->withTrashed()
                ->name('cities.force-delete');
            Route::delete('/cities/recycle-bin/empty', [TaxonomyCityController::class, 'emptyRecycleBin'])
                ->middleware('permission:city-force-delete')
                ->name('cities.empty-recycle-bin');

            Route::get('/areas', [TaxonomyAreaController::class, 'index'])
                ->middleware('permission:area-view')
                ->name('areas.index');
            Route::get('/areas/create', [TaxonomyAreaController::class, 'create'])
                ->middleware('permission:area-create')
                ->name('areas.create');
            Route::post('/areas', [TaxonomyAreaController::class, 'store'])
                ->middleware('permission:area-create')
                ->name('areas.store');
            Route::get('/areas/{area}/edit', [TaxonomyAreaController::class, 'edit'])
                ->middleware('permission:area-update')
                ->name('areas.edit');
            Route::put('/areas/{area}', [TaxonomyAreaController::class, 'update'])
                ->middleware('permission:area-update')
                ->name('areas.update');
            Route::patch('/areas/{area}/status', [TaxonomyAreaController::class, 'updateStatus'])
                ->middleware('permission:area-update')
                ->name('areas.status');
            Route::delete('/areas/{area}', [TaxonomyAreaController::class, 'destroy'])
                ->middleware('permission:area-delete')
                ->name('areas.destroy');
            Route::patch('/areas/{area}/restore', [TaxonomyAreaController::class, 'restore'])
                ->middleware('permission:area-restore')
                ->withTrashed()
                ->name('areas.restore');
            Route::delete('/areas/{area}/force', [TaxonomyAreaController::class, 'forceDelete'])
                ->middleware('permission:area-force-delete')
                ->withTrashed()
                ->name('areas.force-delete');
            Route::delete('/areas/recycle-bin/empty', [TaxonomyAreaController::class, 'emptyRecycleBin'])
                ->middleware('permission:area-force-delete')
                ->name('areas.empty-recycle-bin');

            Route::get('/categories', [TaxonomyCategoryController::class, 'index'])
                ->middleware('permission:category-view')
                ->name('categories.index');
            Route::get('/categories/create', [TaxonomyCategoryController::class, 'create'])
                ->middleware('permission:category-create')
                ->name('categories.create');
            Route::post('/categories', [TaxonomyCategoryController::class, 'store'])
                ->middleware('permission:category-create')
                ->name('categories.store');
            Route::get('/categories/{category}/edit', [TaxonomyCategoryController::class, 'edit'])
                ->middleware('permission:category-update')
                ->name('categories.edit');
            Route::put('/categories/{category}', [TaxonomyCategoryController::class, 'update'])
                ->middleware('permission:category-update')
                ->name('categories.update');
            Route::patch('/categories/{category}/status', [TaxonomyCategoryController::class, 'updateStatus'])
                ->middleware('permission:category-update')
                ->name('categories.status');
            Route::delete('/categories/{category}', [TaxonomyCategoryController::class, 'destroy'])
                ->middleware('permission:category-delete')
                ->name('categories.destroy');
            Route::patch('/categories/{category}/restore', [TaxonomyCategoryController::class, 'restore'])
                ->middleware('permission:category-restore')
                ->withTrashed()
                ->name('categories.restore');
            Route::delete('/categories/{category}/force', [TaxonomyCategoryController::class, 'forceDelete'])
                ->middleware('permission:category-force-delete')
                ->withTrashed()
                ->name('categories.force-delete');
            Route::delete('/categories/recycle-bin/empty', [TaxonomyCategoryController::class, 'emptyRecycleBin'])
                ->middleware('permission:category-force-delete')
                ->name('categories.empty-recycle-bin');

            Route::get('/classes', [TaxonomySchoolClassController::class, 'index'])
                ->middleware('permission:class-view')
                ->name('classes.index');
            Route::get('/classes/create', [TaxonomySchoolClassController::class, 'create'])
                ->middleware('permission:class-create')
                ->name('classes.create');
            Route::post('/classes', [TaxonomySchoolClassController::class, 'store'])
                ->middleware('permission:class-create')
                ->name('classes.store');
            Route::get('/classes/{schoolClass}/edit', [TaxonomySchoolClassController::class, 'edit'])
                ->middleware('permission:class-update')
                ->name('classes.edit');
            Route::put('/classes/{schoolClass}', [TaxonomySchoolClassController::class, 'update'])
                ->middleware('permission:class-update')
                ->name('classes.update');
            Route::patch('/classes/{schoolClass}/status', [TaxonomySchoolClassController::class, 'updateStatus'])
                ->middleware('permission:class-update')
                ->name('classes.status');
            Route::delete('/classes/{schoolClass}', [TaxonomySchoolClassController::class, 'destroy'])
                ->middleware('permission:class-delete')
                ->name('classes.destroy');
            Route::patch('/classes/{schoolClass}/restore', [TaxonomySchoolClassController::class, 'restore'])
                ->middleware('permission:class-restore')
                ->withTrashed()
                ->name('classes.restore');
            Route::delete('/classes/{schoolClass}/force', [TaxonomySchoolClassController::class, 'forceDelete'])
                ->middleware('permission:class-force-delete')
                ->withTrashed()
                ->name('classes.force-delete');
            Route::delete('/classes/recycle-bin/empty', [TaxonomySchoolClassController::class, 'emptyRecycleBin'])
                ->middleware('permission:class-force-delete')
                ->name('classes.empty-recycle-bin');

            Route::get('/subjects', [TaxonomySubjectController::class, 'index'])
                ->middleware('permission:subject-view')
                ->name('subjects.index');
            Route::get('/subjects/create', [TaxonomySubjectController::class, 'create'])
                ->middleware('permission:subject-create')
                ->name('subjects.create');
            Route::post('/subjects', [TaxonomySubjectController::class, 'store'])
                ->middleware('permission:subject-create')
                ->name('subjects.store');
            Route::get('/subjects/{subject}/edit', [TaxonomySubjectController::class, 'edit'])
                ->middleware('permission:subject-update')
                ->name('subjects.edit');
            Route::put('/subjects/{subject}', [TaxonomySubjectController::class, 'update'])
                ->middleware('permission:subject-update')
                ->name('subjects.update');
            Route::patch('/subjects/{subject}/status', [TaxonomySubjectController::class, 'updateStatus'])
                ->middleware('permission:subject-update')
                ->name('subjects.status');
            Route::delete('/subjects/{subject}', [TaxonomySubjectController::class, 'destroy'])
                ->middleware('permission:subject-delete')
                ->name('subjects.destroy');
            Route::patch('/subjects/{subject}/restore', [TaxonomySubjectController::class, 'restore'])
                ->middleware('permission:subject-restore')
                ->withTrashed()
                ->name('subjects.restore');
            Route::delete('/subjects/{subject}/force', [TaxonomySubjectController::class, 'forceDelete'])
                ->middleware('permission:subject-force-delete')
                ->withTrashed()
                ->name('subjects.force-delete');
            Route::delete('/subjects/recycle-bin/empty', [TaxonomySubjectController::class, 'emptyRecycleBin'])
                ->middleware('permission:subject-force-delete')
                ->name('subjects.empty-recycle-bin');

            Route::get('/tuition-types', [TaxonomyTuitionTypeController::class, 'index'])
                ->middleware('permission:tuition-type-view')
                ->name('tuition-types.index');
            Route::get('/tuition-types/create', [TaxonomyTuitionTypeController::class, 'create'])
                ->middleware('permission:tuition-type-create')
                ->name('tuition-types.create');
            Route::post('/tuition-types', [TaxonomyTuitionTypeController::class, 'store'])
                ->middleware('permission:tuition-type-create')
                ->name('tuition-types.store');
            Route::get('/tuition-types/{tuitionType}/edit', [TaxonomyTuitionTypeController::class, 'edit'])
                ->middleware('permission:tuition-type-update')
                ->name('tuition-types.edit');
            Route::put('/tuition-types/{tuitionType}', [TaxonomyTuitionTypeController::class, 'update'])
                ->middleware('permission:tuition-type-update')
                ->name('tuition-types.update');
            Route::patch('/tuition-types/{tuitionType}/status', [TaxonomyTuitionTypeController::class, 'updateStatus'])
                ->middleware('permission:tuition-type-update')
                ->name('tuition-types.status');
            Route::delete('/tuition-types/{tuitionType}', [TaxonomyTuitionTypeController::class, 'destroy'])
                ->middleware('permission:tuition-type-delete')
                ->name('tuition-types.destroy');
            Route::patch('/tuition-types/{tuitionType}/restore', [TaxonomyTuitionTypeController::class, 'restore'])
                ->middleware('permission:tuition-type-restore')
                ->withTrashed()
                ->name('tuition-types.restore');
            Route::delete('/tuition-types/{tuitionType}/force', [TaxonomyTuitionTypeController::class, 'forceDelete'])
                ->middleware('permission:tuition-type-force-delete')
                ->withTrashed()
                ->name('tuition-types.force-delete');
            Route::delete('/tuition-types/recycle-bin/empty', [TaxonomyTuitionTypeController::class, 'emptyRecycleBin'])
                ->middleware('permission:tuition-type-force-delete')
                ->name('tuition-types.empty-recycle-bin');
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
        Route::get('/tutors/create', [TutorManagementController::class, 'create'])
            ->middleware('permission:tutor-create')
            ->name('tutors.create');
        Route::post('/tutors', [TutorManagementController::class, 'store'])
            ->middleware('permission:tutor-create')
            ->name('tutors.store');
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
        Route::get('/guardians/create', [GuardianManagementController::class, 'create'])
            ->middleware('permission:guardian-create')
            ->name('guardians.create');
        Route::post('/guardians', [GuardianManagementController::class, 'store'])
            ->middleware('permission:guardian-create')
            ->name('guardians.store');
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

        Route::get('/verifications', [VerificationRequestController::class, 'index'])
            ->middleware('permission:verification-request-view')
            ->name('verifications.index');
        Route::get('/profile-verification/pending', [VerificationRequestController::class, 'pendingProfiles'])
            ->middleware('permission:verification-request-view')
            ->name('profile-verification.pending');
        Route::get('/profile-verification/unverified', [VerificationRequestController::class, 'unverifiedProfiles'])
            ->middleware('permission:verification-request-view')
            ->name('profile-verification.unverified');
        Route::get('/profile-verification/verified', [VerificationRequestController::class, 'verifiedProfiles'])
            ->middleware('permission:verification-request-view')
            ->name('profile-verification.verified');
        Route::get('/verifications/{verificationRequest}', [VerificationRequestController::class, 'show'])
            ->middleware('permission:verification-request-view')
            ->name('verifications.show');
        Route::patch('/verifications/{verificationRequest}/approve', [VerificationRequestController::class, 'approve'])
            ->middleware('permission:verification-request-update')
            ->name('verifications.approve');
        Route::patch('/verifications/{verificationRequest}/reject', [VerificationRequestController::class, 'reject'])
            ->middleware('permission:verification-request-update')
            ->name('verifications.reject');
        Route::post('/verifications/{verificationRequest}/invoice', [VerificationRequestController::class, 'createInvoice'])
            ->middleware('permission:invoice-create')
            ->name('verifications.invoice');
        Route::patch('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])
            ->middleware('permission:invoice-update')
            ->name('invoices.mark-paid');

        // Notice Management
        Route::get('/notices', [NoticeController::class, 'index'])
            ->middleware('permission:notice-view')
            ->name('notices.index');
        Route::get('/notices/create', [NoticeController::class, 'create'])
            ->middleware('permission:notice-create')
            ->name('notices.create');
        Route::post('/notices', [NoticeController::class, 'store'])
            ->middleware('permission:notice-create')
            ->name('notices.store');
        Route::get('/notices/{notice}/edit', [NoticeController::class, 'edit'])
            ->middleware('permission:notice-update')
            ->name('notices.edit');
        Route::put('/notices/{notice}', [NoticeController::class, 'update'])
            ->middleware('permission:notice-update')
            ->name('notices.update');
        Route::delete('/notices/{notice}', [NoticeController::class, 'destroy'])
            ->middleware('permission:notice-delete')
            ->name('notices.destroy');
        Route::patch('/notices/{notice}/restore', [NoticeController::class, 'restore'])
            ->middleware('permission:notice-restore')
            ->withTrashed()
            ->name('notices.restore');
        Route::delete('/notices/{notice}/force', [NoticeController::class, 'forceDelete'])
            ->middleware('permission:notice-force-delete')
            ->withTrashed()
            ->name('notices.force-delete');
        Route::delete('/notices/recycle-bin/empty', [NoticeController::class, 'emptyRecycleBin'])
            ->middleware('permission:notice-force-delete')
            ->name('notices.empty-recycle-bin');

        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/invoices', [FinanceInvoiceController::class, 'index'])
                ->middleware('permission:finance-invoice-view')
                ->name('invoices.index');
            Route::get('/payments', [FinancePaymentController::class, 'index'])
                ->middleware('permission:finance-payment-view')
                ->name('payments.index');
            Route::get('/refund-requests', [FinanceRefundRequestController::class, 'index'])
                ->middleware('permission:finance-refund-view')
                ->name('refund-requests.index');
            Route::patch('/refund-requests/{refundRequest}/decision', [FinanceRefundRequestController::class, 'decide'])
                ->middleware('permission:finance-refund-decide')
                ->name('refund-requests.decision');
            Route::patch('/refund-requests/{refundRequest}/mark-paid', [FinanceRefundRequestController::class, 'markPaid'])
                ->middleware('permission:finance-refund-pay')
                ->name('refund-requests.mark-paid');
            Route::get('/ledger', [FinanceLedgerController::class, 'index'])
                ->middleware('permission:finance-ledger-view')
                ->name('ledger.index');
        });

    });
});
