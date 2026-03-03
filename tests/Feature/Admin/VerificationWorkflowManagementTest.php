<?php

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
        'verification_status' => User::VERIFICATION_STATUS_PENDING,
        'verification_type' => VerificationRequest::ROLE_TUTOR,
    ]);

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $tutor->id,
        'role' => VerificationRequest::ROLE_TUTOR,
        'status' => VerificationRequest::STATUS_PENDING,
        'fee_amount' => 500,
        'currency' => 'BDT',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.verifications.approve', $verificationRequest))
        ->assertRedirect();

    expect($verificationRequest->fresh()->status)->toBe(VerificationRequest::STATUS_APPROVED);
    expect($tutor->fresh()->verification_status)->toBe(User::VERIFICATION_STATUS_APPROVED);

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
    expect($invoice->status)->toBe(Invoice::STATUS_UNPAID);
    expect($verificationRequest->fresh()->status)->toBe(VerificationRequest::STATUS_INVOICED);
    expect($tutor->fresh()->verification_status)->toBe(User::VERIFICATION_STATUS_INVOICED);

    $this->actingAs($admin)
        ->patch(route('admin.invoices.mark-paid', $invoice), [
            'payment_gateway' => Invoice::GATEWAY_MANUAL,
            'payment_method' => 'cash',
            'payment_reference' => 'ADMIN-REF-1001',
            'paid_at' => now()->toDateTimeString(),
        ])
        ->assertRedirect();

    expect($invoice->fresh()->status)->toBe(Invoice::STATUS_PAID);
    expect($invoice->fresh()->transaction_id)->toBe('ADMIN-REF-1001');
    expect($verificationRequest->fresh()->status)->toBe(VerificationRequest::STATUS_VERIFIED);

    $tutor->refresh();

    expect($tutor->verification_status)->toBe(User::VERIFICATION_STATUS_VERIFIED);
    expect($tutor->verified_at)->not->toBeNull();
});

it('admin manual override can verify a failed invoice', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $guardian = User::factory()->guardian()->create([
        'verification_status' => User::VERIFICATION_STATUS_INVOICED,
        'verification_type' => VerificationRequest::ROLE_GUARDIAN,
    ]);

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $guardian->id,
        'role' => VerificationRequest::ROLE_GUARDIAN,
        'status' => VerificationRequest::STATUS_INVOICED,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $guardian->id,
        'status' => Invoice::STATUS_FAILED,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.invoices.mark-paid', $invoice), [
            'payment_gateway' => Invoice::GATEWAY_MANUAL,
            'payment_method' => 'manual',
            'payment_reference' => 'OVERRIDE-001',
            'paid_at' => now()->toDateTimeString(),
        ])
        ->assertRedirect();

    expect($invoice->fresh()->status)->toBe(Invoice::STATUS_PAID);
    expect($verificationRequest->fresh()->status)->toBe(VerificationRequest::STATUS_VERIFIED);
    expect($guardian->fresh()->verification_status)->toBe(User::VERIFICATION_STATUS_VERIFIED);
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
        'verification_status' => User::VERIFICATION_STATUS_PENDING,
    ]);
    User::factory()->guardian()->create([
        'verification_status' => User::VERIFICATION_STATUS_APPROVED,
    ]);
    User::factory()->guardian()->create([
        'verification_status' => User::VERIFICATION_STATUS_INVOICED,
    ]);
    User::factory()->guardian()->create([
        'verification_status' => User::VERIFICATION_STATUS_UNVERIFIED,
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
        'verification_status' => User::VERIFICATION_STATUS_UNVERIFIED,
    ]);
    User::factory()->guardian()->create([
        'verification_status' => User::VERIFICATION_STATUS_VERIFIED,
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
        'verification_status' => User::VERIFICATION_STATUS_VERIFIED,
    ]);
    User::factory()->guardian()->create([
        'verification_status' => User::VERIFICATION_STATUS_PENDING,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.profile-verification.verified'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/verifications/ProfileVerificationIndex')
            ->where('bucket', 'verified')
            ->has('items.data', 1));
});
