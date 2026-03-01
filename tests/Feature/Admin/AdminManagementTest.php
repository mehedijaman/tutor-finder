<?php

use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;

it('admin can create another admin and assign role', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create([
        'email' => 'super@example.com',
    ]);
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Second Admin',
        'email' => 'admin2@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => ['admin'],
        'permissions' => ['tutor-view'],
    ]);

    $response->assertRedirect(route('admin.users.index', absolute: false));

    $newAdmin = User::query()->where('email', 'admin2@example.com')->first();

    expect($newAdmin)->not->toBeNull();
    expect($newAdmin->role)->toBe('admin');
    expect($newAdmin->hasRole('admin'))->toBeTrue();
    expect($newAdmin->can('tutor-view'))->toBeTrue();
});

it('admin can view create role page', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create([
        'email' => 'role-creator@example.com',
    ]);
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get(route('admin.roles.create'))
        ->assertSuccessful();
});
