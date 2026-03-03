<?php

use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

it('enforces admin finance permissions on admin panel routes', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.finance.invoices.index'))
        ->assertForbidden();

    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get(route('admin.finance.invoices.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/finance/Invoices'));
});

it('renders tutor and guardian finance centers and blocks cross-role access', function () {
    $tutor = User::factory()->tutor()->create();
    $guardian = User::factory()->guardian()->create();

    $this->actingAs($tutor)
        ->get(route('tutor.finance.invoices'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/finance/Invoices'));

    $this->actingAs($tutor)
        ->get(route('tutor.finance.refunds.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/finance/RefundRequests'));

    $this->actingAs($guardian)
        ->get(route('guardian.finance.invoices'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/finance/Invoices'));

    $this->actingAs($tutor)
        ->get(route('guardian.finance.invoices'))
        ->assertForbidden();

    $this->actingAs($guardian)
        ->get(route('tutor.finance.invoices'))
        ->assertForbidden();
});
