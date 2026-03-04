<?php

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\PaymentGatewayType;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->withoutVite();
});

it('admin payment index accepts valid status and rejects invalid status', function (): void {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get(route('admin.finance.payments.index', ['status' => PaymentStatus::Paid->value]))
        ->assertSuccessful();

    $this->actingAs($admin)
        ->get(route('admin.finance.payments.index', ['status' => 'invalid-status']))
        ->assertSuccessful();
});

it('admin payment index accepts valid gateway and rejects invalid gateway', function (): void {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get(route('admin.finance.payments.index', ['gateway' => PaymentGatewayType::Bkash->value]))
        ->assertSuccessful();

    $this->actingAs($admin)
        ->get(route('admin.finance.payments.index', ['gateway' => 'invalid-gateway']))
        ->assertSuccessful();
});

it('admin invoice index accepts valid status and type and rejects invalid', function (): void {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get(route('admin.finance.invoices.index', [
            'status' => InvoiceStatus::Paid->value,
            'type' => InvoiceType::PlatformServiceFee->value,
        ]))
        ->assertSuccessful();

    $this->actingAs($admin)
        ->get(route('admin.finance.invoices.index', [
            'status' => 'bogus',
            'type' => 'bogus',
        ]))
        ->assertSuccessful();
});

it('admin refund request index accepts valid status and rejects invalid', function (): void {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get(route('admin.finance.refund-requests.index', ['status' => RefundStatus::Pending->value]))
        ->assertSuccessful();

    $this->actingAs($admin)
        ->get(route('admin.finance.refund-requests.index', ['status' => 'invalid']))
        ->assertSuccessful();
});

it('tutor finance invoices accepts valid status and rejects invalid', function (): void {
    $tutor = User::factory()->tutor()->create();

    $this->actingAs($tutor)
        ->get(route('tutor.finance.invoices', ['status' => InvoiceStatus::Unpaid->value]))
        ->assertSuccessful();

    $this->actingAs($tutor)
        ->get(route('tutor.finance.invoices', ['status' => 'invalid']))
        ->assertSuccessful();
});

it('tutor refund request index accepts valid status and rejects invalid', function (): void {
    $tutor = User::factory()->tutor()->create();

    $this->actingAs($tutor)
        ->get(route('tutor.finance.refunds.index', ['status' => RefundStatus::Approved->value]))
        ->assertSuccessful();

    $this->actingAs($tutor)
        ->get(route('tutor.finance.refunds.index', ['status' => 'nonsense']))
        ->assertSuccessful();
});

it('guardian finance invoices accepts valid status and rejects invalid', function (): void {
    $guardian = User::factory()->guardian()->create();

    $this->actingAs($guardian)
        ->get(route('guardian.finance.invoices', ['status' => InvoiceStatus::Paid->value]))
        ->assertSuccessful();

    $this->actingAs($guardian)
        ->get(route('guardian.finance.invoices', ['status' => 'invalid']))
        ->assertSuccessful();
});
