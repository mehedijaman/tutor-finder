<?php

use App\Enums\InvoiceStatus;
use App\Enums\PaymentGatewayType;
use App\Enums\VerificationRole;
use App\Enums\VerificationStatus;
use App\Models\Invoice;
use App\Models\User;
use App\Models\VerificationRequest;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('admin can approve, generate invoice, and mark payment paid for verification', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $tutor = User::factory()->tutor()->create([
        'verification_status' => VerificationStatus::Pending,
        'verification_type' => VerificationRole::Tutor,
    ]);

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $tutor->id,
        'role' => VerificationRole::Tutor,
        'status' => VerificationStatus::Pending,
        'fee_amount' => 500,
        'currency' => 'BDT',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.verifications.approve', $verificationRequest))
        ->assertRedirect();

    expect($verificationRequest->fresh()->status)->toBe(VerificationStatus::Approved);
    expect($tutor->fresh()->verification_status)->toBe(VerificationStatus::Approved);

    $this->actingAs($admin)
        ->post(route('admin.verifications.invoice', $verificationRequest), [
            'amount' => 500,
            'currency' => 'BDT',
            'due_at' => now()->addDays(5)->toDateTimeString(),
            'expires_at' => now()->addDays(7)->toDateTimeString(),
        ])
        ->assertRedirect();

    $invoice = Invoice::query()->where('invoiceable_type', VerificationRequest::class)
        ->where('invoiceable_id', $verificationRequest->id)
        ->latest('id')
        ->first();

    expect($invoice)->not->toBeNull();
    expect($invoice->status)->toBe(InvoiceStatus::Unpaid);
    expect($verificationRequest->fresh()->status)->toBe(VerificationStatus::Invoiced);
    expect($tutor->fresh()->verification_status)->toBe(VerificationStatus::Invoiced);

    $this->actingAs($admin)
        ->patch(route('admin.invoices.mark-paid', $invoice), [
            'payment_gateway' => PaymentGatewayType::Manual->value,
            'payment_method' => 'cash',
            'payment_reference' => 'ADMIN-REF-1001',
            'paid_at' => now()->toDateTimeString(),
        ])
        ->assertRedirect();

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid);
    expect($invoice->fresh()->transaction_id)->toBe('ADMIN-REF-1001');
    expect($verificationRequest->fresh()->status)->toBe(VerificationStatus::Verified);

    $tutor->refresh();

    expect($tutor->verification_status)->toBe(VerificationStatus::Verified);
    expect($tutor->verified_at)->not->toBeNull();
});

it('admin manual override can verify a failed invoice', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $guardian = User::factory()->guardian()->create([
        'verification_status' => VerificationStatus::Invoiced,
        'verification_type' => VerificationRole::Guardian,
    ]);

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $guardian->id,
        'role' => VerificationRole::Guardian,
        'status' => VerificationStatus::Invoiced,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $guardian->id,
        'status' => InvoiceStatus::Failed,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.invoices.mark-paid', $invoice), [
            'payment_gateway' => PaymentGatewayType::Manual->value,
            'payment_method' => 'manual',
            'payment_reference' => 'OVERRIDE-001',
            'paid_at' => now()->toDateTimeString(),
        ])
        ->assertRedirect();

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid);
    expect($verificationRequest->fresh()->status)->toBe(VerificationStatus::Verified);
    expect($guardian->fresh()->verification_status)->toBe(VerificationStatus::Verified);
});

it('admin without verification permissions cannot access verification routes', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.verifications.index'))
        ->assertForbidden();
});

it('admin can view pending profile verification menu', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    User::factory()->tutor()->create([
        'verification_status' => VerificationStatus::Pending,
    ]);
    User::factory()->guardian()->create([
        'verification_status' => VerificationStatus::Approved,
    ]);
    User::factory()->guardian()->create([
        'verification_status' => VerificationStatus::Invoiced,
    ]);
    User::factory()->guardian()->create([
        'verification_status' => VerificationStatus::Unverified,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.profile-verification.pending'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/verifications/ProfileVerificationIndex')
            ->where('bucket', 'pending')
            ->has('items.data', 3));
});

it('admin can view unverified profile verification menu', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    User::factory()->tutor()->create([
        'verification_status' => VerificationStatus::Unverified,
    ]);
    User::factory()->guardian()->create([
        'verification_status' => VerificationStatus::Verified,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.profile-verification.unverified'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/verifications/ProfileVerificationIndex')
            ->where('bucket', 'unverified')
            ->has('items.data', 1));
});

it('admin can view verified profile verification menu', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    User::factory()->tutor()->create([
        'verification_status' => VerificationStatus::Verified,
    ]);
    User::factory()->guardian()->create([
        'verification_status' => VerificationStatus::Pending,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.profile-verification.verified'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/verifications/ProfileVerificationIndex')
            ->where('bucket', 'verified')
            ->has('items.data', 1));
});
