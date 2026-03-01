<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;

it('can permanently delete trashed tutor', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $tutor = User::factory()->tutor()->create();

    $this->actingAs($admin)
        ->delete(route('admin.tutors.destroy', $tutor))
        ->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('admin.tutors.force-delete', $tutor->id))
        ->assertRedirect();

    expect(User::withTrashed()->find($tutor->id))->toBeNull();
});

it('can permanently delete trashed guardian', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $guardian = User::factory()->guardian()->create();

    $this->actingAs($admin)
        ->delete(route('admin.guardians.destroy', $guardian))
        ->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('admin.guardians.force-delete', $guardian->id))
        ->assertRedirect();

    expect(User::withTrashed()->find($guardian->id))->toBeNull();
});

it('can permanently delete trashed admin user', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $superAdmin = User::factory()->admin()->create();
    $superAdmin->assignRole('super-admin');

    $otherAdmin = User::factory()->admin()->create([
        'email' => 'to-delete-admin@example.com',
    ]);
    $otherAdmin->assignRole('admin');

    $this->actingAs($superAdmin)
        ->delete(route('admin.users.destroy', $otherAdmin))
        ->assertRedirect();

    $this->actingAs($superAdmin)
        ->delete(route('admin.users.force-delete', $otherAdmin->id))
        ->assertRedirect();

    expect(User::withTrashed()->find($otherAdmin->id))->toBeNull();
});

it('can permanently delete trashed role', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $role = Role::query()->create([
        'name' => 'temporary-role-to-force-delete',
        'guard_name' => 'web',
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.roles.destroy', $role))
        ->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('admin.roles.force-delete', $role->id))
        ->assertRedirect();

    expect(Role::withTrashed()->find($role->id))->toBeNull();
});

it('can empty tutor recycle bin and action is idempotent', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $firstTutor = User::factory()->tutor()->create();
    $secondTutor = User::factory()->tutor()->create();

    $this->actingAs($admin)->delete(route('admin.tutors.destroy', $firstTutor))->assertRedirect();
    $this->actingAs($admin)->delete(route('admin.tutors.destroy', $secondTutor))->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('admin.tutors.empty-recycle-bin'))
        ->assertRedirect();

    expect(User::withTrashed()->whereIn('id', [$firstTutor->id, $secondTutor->id])->exists())->toBeFalse();

    $this->actingAs($admin)
        ->delete(route('admin.tutors.empty-recycle-bin'))
        ->assertRedirect();
});

it('admin without delete permissions cannot force delete or empty recycle bin', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $adminWithoutPermission = User::factory()->admin()->create();
    $tutor = User::factory()->tutor()->create();

    $superAdmin = User::factory()->admin()->create();
    $superAdmin->assignRole('super-admin');

    $this->actingAs($superAdmin)
        ->delete(route('admin.tutors.destroy', $tutor))
        ->assertRedirect();

    $this->actingAs($adminWithoutPermission)
        ->delete(route('admin.tutors.force-delete', $tutor->id))
        ->assertForbidden();

    $this->actingAs($adminWithoutPermission)
        ->delete(route('admin.tutors.empty-recycle-bin'))
        ->assertForbidden();

    $this->actingAs($adminWithoutPermission)
        ->patch(route('admin.tutors.restore-all'))
        ->assertForbidden();
});
