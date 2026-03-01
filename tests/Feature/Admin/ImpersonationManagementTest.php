<?php

use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('admin can impersonate admin, tutor, and guardian users', function (string $targetRole) {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create([
        'email' => 'impersonator@example.com',
    ]);

    $target = match ($targetRole) {
        'admin' => User::factory()->admin()->create([
            'email' => 'impersonated-admin@example.com',
        ]),
        'tutor' => User::factory()->tutor()->create(),
        default => User::factory()->guardian()->create(),
    };

    $this->actingAs($admin)
        ->post(route('admin.impersonation.store', $target))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($target);
    expect(session(config('laravel-impersonate.session_key')))->toBe($admin->id);

    $this->post(route('impersonation.leave'))
        ->assertRedirect(route('admin.dashboard', absolute: false));

    $this->assertAuthenticatedAs($admin);
    expect(session()->has(config('laravel-impersonate.session_key')))->toBeFalse();
})->with(['admin', 'tutor', 'guardian']);

it('admin cannot impersonate their own account', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.impersonation.store', $admin))
        ->assertRedirect()
        ->assertSessionHasErrors('impersonation');
});

it('non-admin users cannot start impersonation', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $tutor = User::factory()->tutor()->create();
    $guardian = User::factory()->guardian()->create();

    $this->actingAs($tutor)
        ->post(route('admin.impersonation.store', $guardian))
        ->assertForbidden();
});

it('leave impersonation endpoint is forbidden when not impersonating', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('impersonation.leave'))
        ->assertForbidden();
});

it('cannot impersonate users who are not active', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $suspendedTutor = User::factory()->tutor()->suspended()->create();

    $this->actingAs($admin)
        ->post(route('admin.impersonation.store', $suspendedTutor))
        ->assertForbidden();
});

it('shares impersonation state with inertia pages while impersonating', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $tutor = User::factory()->tutor()->create();

    $this->actingAs($admin)
        ->post(route('admin.impersonation.store', $tutor))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->get(route('dashboard'))
        ->assertRedirect(route('tutor.dashboard', absolute: false));

    $this->get(route('tutor.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/Dashboard')
            ->where('auth.impersonation.is_impersonating', true)
            ->where('auth.impersonation.impersonator_id', $admin->id)
            ->where('auth.impersonation.impersonator_name', $admin->name),
        );
});
