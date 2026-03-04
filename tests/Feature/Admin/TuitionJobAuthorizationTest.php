<?php

use App\Models\TuitionJob;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;

it('forbids admins without permissions from tuition jobs module', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $job = TuitionJob::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.jobs.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('admin.jobs.store'), [])
        ->assertForbidden();

    $this->actingAs($admin)
        ->patch(route('admin.jobs.approve', $job))
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('admin.jobs.destroy', $job))
        ->assertForbidden();
});
