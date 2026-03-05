<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AdminRolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect([
            'tutor',
            'guardian',
            'admin-user',
            'role',
            'sms-setting',
            'smtp-setting',
            'faq',
            'blog-category',
            'blog-tag',
            'blog-post',
            'country',
            'city',
            'area',
            'category',
            'class',
            'subject',
            'tuition-type',
            'job',
            'notice',
            'ticket',
        ])->flatMap(fn (string $resource): array => [
            "{$resource}-create",
            "{$resource}-view",
            "{$resource}-update",
            "{$resource}-delete",
        ])->push(
            'tutor-password-reset',
            'guardian-password-reset',
            'site-setting-view',
            'site-setting-update',
            'payment-setting-view',
            'payment-setting-update',
            'activity-log-view',
            'backup-view',
            'backup-run',
            'backup-clean',
            'backup-download',
            'backup-delete',
            'log-viewer-view',
            'log-viewer-download',
            'log-viewer-delete',
            'contact-message-view',
            'contact-message-update',
            'faq-restore',
            'faq-force-delete',
            'blog-category-restore',
            'blog-category-force-delete',
            'blog-tag-restore',
            'blog-tag-force-delete',
            'blog-post-restore',
            'blog-post-force-delete',
            'blog-post-publish',
            'country-restore',
            'country-force-delete',
            'city-restore',
            'city-force-delete',
            'area-restore',
            'area-force-delete',
            'category-restore',
            'category-force-delete',
            'class-restore',
            'class-force-delete',
            'subject-restore',
            'subject-force-delete',
            'tuition-type-restore',
            'tuition-type-force-delete',
            'job-restore',
            'job-force-delete',
            'job-approve',
            'notice-restore',
            'notice-force-delete',
            'verification-request-view',
            'verification-request-update',
            'invoice-view',
            'invoice-create',
            'invoice-update',
            'finance-invoice-view',
            'finance-payment-view',
            'finance-refund-view',
            'finance-refund-decide',
            'finance-refund-pay',
            'finance-ledger-view',
            'ticket-close',
            'ticket-assign',
        )->values()->all();

        Permission::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', $permissions)
            ->delete();

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $superAdminRole = Role::query()->firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        $adminRole = Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $allPermissions = Permission::query()->pluck('name')->all();

        $superAdminRole->syncPermissions($allPermissions);
        $adminRole->syncPermissions($allPermissions);
    }
}
