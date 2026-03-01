<?php

use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;

it('admin can login and is redirected to admin dashboard', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create([
        'email' => 'admin1@example.com',
        'password' => 'password',
    ]);
    $admin->assignRole('super-admin');

    $response = $this->post(route('admin.login.store'), [
        'email' => 'admin1@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('admin.dashboard', absolute: false));
    $this->assertAuthenticatedAs($admin);
});
