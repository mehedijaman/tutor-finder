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
        ])->flatMap(fn (string $resource): array => [
            "{$resource}-create",
            "{$resource}-view",
            "{$resource}-update",
            "{$resource}-delete",
        ])->push(
            'tutor-password-reset',
            'guardian-password-reset',
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
