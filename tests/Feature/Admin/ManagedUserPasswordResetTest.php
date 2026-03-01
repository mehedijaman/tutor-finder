<?php

use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

it('admin with tutor-password-reset permission can reset tutor password', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $tutor = User::factory()->tutor()->create([
        'password' => 'old-password',
    ]);

    $response = $this->actingAs($admin)->put(route('admin.tutors.reset-password', $tutor), [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();

    $tutor->refresh();

    expect(Hash::check('new-password', $tutor->password))->toBeTrue();
});

it('admin with guardian-password-reset permission can reset guardian password', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $guardian = User::factory()->guardian()->create([
        'password' => 'old-password',
    ]);

    $response = $this->actingAs($admin)->put(route('admin.guardians.reset-password', $guardian), [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();

    $guardian->refresh();

    expect(Hash::check('new-password', $guardian->password))->toBeTrue();
});

it('admin without tutor-password-reset permission cannot reset tutor password', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $adminWithoutPermission = User::factory()->admin()->create();
    $tutor = User::factory()->tutor()->create();

    $response = $this->actingAs($adminWithoutPermission)->put(route('admin.tutors.reset-password', $tutor), [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertForbidden();
});

it('admin without guardian-password-reset permission cannot reset guardian password', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $adminWithoutPermission = User::factory()->admin()->create();
    $guardian = User::factory()->guardian()->create();

    $response = $this->actingAs($adminWithoutPermission)->put(route('admin.guardians.reset-password', $guardian), [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertForbidden();
});
