<?php

use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Gate;

it('admin with log viewer view permission can access log viewer', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('log-viewer-view');

    $this->actingAs($admin)
        ->get(route('log-viewer.index'))
        ->assertSuccessful();
});

it('admin with log viewer view permission can access log viewer api routes', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('log-viewer-view');

    $this->actingAs($admin)
        ->get(route('log-viewer.folders'))
        ->assertSuccessful();
});

it('admin without log viewer view permission cannot access log viewer', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('log-viewer.index'))
        ->assertForbidden();
});

it('log viewer download and delete gates follow assigned permissions', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();

    expect(Gate::forUser($admin)->allows('downloadLogFile'))->toBeFalse();
    expect(Gate::forUser($admin)->allows('deleteLogFile'))->toBeFalse();

    $admin->givePermissionTo('log-viewer-download');
    expect(Gate::forUser($admin)->allows('downloadLogFile'))->toBeTrue();
    expect(Gate::forUser($admin)->allows('deleteLogFile'))->toBeFalse();

    $admin->givePermissionTo('log-viewer-delete');
    expect(Gate::forUser($admin)->allows('downloadLogFile'))->toBeTrue();
    expect(Gate::forUser($admin)->allows('deleteLogFile'))->toBeTrue();
});
