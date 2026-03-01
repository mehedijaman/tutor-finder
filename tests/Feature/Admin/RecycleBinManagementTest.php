<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Spatie\Permission\Models\Permission;

it('seeds granular app wide permissions', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $permissionNames = [
        'tutor-create',
        'tutor-view',
        'tutor-update',
        'tutor-delete',
        'guardian-create',
        'guardian-view',
        'guardian-update',
        'guardian-delete',
        'admin-user-create',
        'admin-user-view',
        'admin-user-update',
        'admin-user-delete',
        'role-create',
        'role-view',
        'role-update',
        'role-delete',
        'sms-setting-create',
        'sms-setting-view',
        'sms-setting-update',
        'sms-setting-delete',
        'tutor-password-reset',
        'guardian-password-reset',
    ];

    foreach ($permissionNames as $permissionName) {
        expect(Permission::query()->where('name', $permissionName)->exists())->toBeTrue();
    }
});

it('can move and restore tutor from recycle bin', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');
    $tutor = User::factory()->tutor()->create();

    $this->actingAs($admin)->delete(route('admin.tutors.destroy', $tutor))
        ->assertRedirect();

    expect(User::withTrashed()->findOrFail($tutor->id)->trashed())->toBeTrue();

    $this->actingAs($admin)->patch(route('admin.tutors.restore', $tutor->id))
        ->assertRedirect();

    expect(User::withTrashed()->findOrFail($tutor->id)->trashed())->toBeFalse();
});

it('can move and restore guardian from recycle bin', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');
    $guardian = User::factory()->guardian()->create();

    $this->actingAs($admin)->delete(route('admin.guardians.destroy', $guardian))
        ->assertRedirect();

    expect(User::withTrashed()->findOrFail($guardian->id)->trashed())->toBeTrue();

    $this->actingAs($admin)->patch(route('admin.guardians.restore', $guardian->id))
        ->assertRedirect();

    expect(User::withTrashed()->findOrFail($guardian->id)->trashed())->toBeFalse();
});

it('can move and restore admin user from recycle bin', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $superAdmin = User::factory()->admin()->create();
    $superAdmin->assignRole('super-admin');
    $otherAdmin = User::factory()->admin()->create([
        'email' => 'restorable-admin@example.com',
    ]);
    $otherAdmin->assignRole('admin');

    $this->actingAs($superAdmin)->delete(route('admin.users.destroy', $otherAdmin))
        ->assertRedirect();

    expect(User::withTrashed()->findOrFail($otherAdmin->id)->trashed())->toBeTrue();

    $this->actingAs($superAdmin)->patch(route('admin.users.restore', $otherAdmin->id))
        ->assertRedirect();

    expect(User::withTrashed()->findOrFail($otherAdmin->id)->trashed())->toBeFalse();
});

it('can move and restore role from recycle bin', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');
    $role = Role::query()->create([
        'name' => 'temp-role',
        'guard_name' => 'web',
    ]);

    $this->actingAs($admin)->delete(route('admin.roles.destroy', $role))
        ->assertRedirect();

    expect(Role::withTrashed()->findOrFail($role->id)->trashed())->toBeTrue();

    $this->actingAs($admin)->patch(route('admin.roles.restore', $role->id))
        ->assertRedirect();

    expect(Role::withTrashed()->findOrFail($role->id)->trashed())->toBeFalse();
});

it('can restore all trashed tutors from recycle bin', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $firstTutor = User::factory()->tutor()->create();
    $secondTutor = User::factory()->tutor()->create();

    $this->actingAs($admin)->delete(route('admin.tutors.destroy', $firstTutor))->assertRedirect();
    $this->actingAs($admin)->delete(route('admin.tutors.destroy', $secondTutor))->assertRedirect();

    $this->actingAs($admin)->patch(route('admin.tutors.restore-all'))->assertRedirect();

    expect(User::withTrashed()->findOrFail($firstTutor->id)->trashed())->toBeFalse();
    expect(User::withTrashed()->findOrFail($secondTutor->id)->trashed())->toBeFalse();
});

it('can restore all trashed guardians from recycle bin', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $firstGuardian = User::factory()->guardian()->create();
    $secondGuardian = User::factory()->guardian()->create();

    $this->actingAs($admin)->delete(route('admin.guardians.destroy', $firstGuardian))->assertRedirect();
    $this->actingAs($admin)->delete(route('admin.guardians.destroy', $secondGuardian))->assertRedirect();

    $this->actingAs($admin)->patch(route('admin.guardians.restore-all'))->assertRedirect();

    expect(User::withTrashed()->findOrFail($firstGuardian->id)->trashed())->toBeFalse();
    expect(User::withTrashed()->findOrFail($secondGuardian->id)->trashed())->toBeFalse();
});

it('can restore all trashed admin users from recycle bin', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $superAdmin = User::factory()->admin()->create();
    $superAdmin->assignRole('super-admin');

    $firstAdmin = User::factory()->admin()->create([
        'email' => 'restore-all-admin-1@example.com',
    ]);
    $secondAdmin = User::factory()->admin()->create([
        'email' => 'restore-all-admin-2@example.com',
    ]);

    $this->actingAs($superAdmin)->delete(route('admin.users.destroy', $firstAdmin))->assertRedirect();
    $this->actingAs($superAdmin)->delete(route('admin.users.destroy', $secondAdmin))->assertRedirect();

    $this->actingAs($superAdmin)->patch(route('admin.users.restore-all'))->assertRedirect();

    expect(User::withTrashed()->findOrFail($firstAdmin->id)->trashed())->toBeFalse();
    expect(User::withTrashed()->findOrFail($secondAdmin->id)->trashed())->toBeFalse();
});

it('can restore all trashed roles from recycle bin', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $firstRole = Role::query()->create([
        'name' => 'restore-all-role-1',
        'guard_name' => 'web',
    ]);
    $secondRole = Role::query()->create([
        'name' => 'restore-all-role-2',
        'guard_name' => 'web',
    ]);

    $this->actingAs($admin)->delete(route('admin.roles.destroy', $firstRole))->assertRedirect();
    $this->actingAs($admin)->delete(route('admin.roles.destroy', $secondRole))->assertRedirect();

    $this->actingAs($admin)->patch(route('admin.roles.restore-all'))->assertRedirect();

    expect(Role::withTrashed()->findOrFail($firstRole->id)->trashed())->toBeFalse();
    expect(Role::withTrashed()->findOrFail($secondRole->id)->trashed())->toBeFalse();
});
