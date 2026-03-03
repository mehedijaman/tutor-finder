<?php

use App\Models\PaymentGateway;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('admin with permission can view payment settings page', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('payment-setting-view');

    PaymentGateway::ensureDefaults();

    $this->actingAs($admin)
        ->get(route('admin.payment-settings.edit'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/payment-settings/Edit')
            ->where('paymentSettings.bkash.status', 'active')
            ->where('paymentSettings.sslcommerz.status', 'active')
            ->where('paymentSettings.manual.status', 'active'));
});

it('admin with permission can update payment settings while preserving secrets', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('payment-setting-update');

    PaymentGateway::query()->updateOrCreate(
        ['gateway' => PaymentGateway::GATEWAY_BKASH],
        [
            'name' => 'bKash',
            'status' => 'active',
            'credentials' => [
                'app_key' => 'old-key',
                'app_secret' => 'old-secret',
                'username' => 'old-user',
                'password' => 'old-password',
                'base_url' => 'https://tokenized.sandbox.bka.sh',
            ],
        ],
    );

    PaymentGateway::query()->updateOrCreate(
        ['gateway' => PaymentGateway::GATEWAY_SSLCOMMERZ],
        [
            'name' => 'SSLCommerz',
            'status' => 'active',
            'credentials' => [
                'store_id' => 'old-store',
                'store_password' => 'old-store-password',
                'mode' => 'sandbox',
            ],
        ],
    );

    PaymentGateway::query()->updateOrCreate(
        ['gateway' => PaymentGateway::GATEWAY_MANUAL],
        [
            'name' => 'Manual',
            'status' => 'active',
            'credentials' => [],
            'notes' => 'Old note',
        ],
    );

    $this->actingAs($admin)
        ->put(route('admin.payment-settings.update'), [
            'bkash' => [
                'status' => 'active',
                'app_key' => 'new-key',
                'app_secret' => '',
                'username' => 'new-user',
                'password' => '',
                'base_url' => 'https://tokenized.pay.bka.sh',
            ],
            'sslcommerz' => [
                'status' => 'inactive',
                'store_id' => 'new-store',
                'store_password' => '',
                'mode' => 'live',
            ],
            'manual' => [
                'status' => 'active',
                'notes' => 'Manual payment will be approved by admin.',
            ],
        ])
        ->assertRedirect(route('admin.payment-settings.edit', absolute: false));

    $bkash = PaymentGateway::query()->where('gateway', PaymentGateway::GATEWAY_BKASH)->firstOrFail();
    $sslCommerz = PaymentGateway::query()->where('gateway', PaymentGateway::GATEWAY_SSLCOMMERZ)->firstOrFail();
    $manual = PaymentGateway::query()->where('gateway', PaymentGateway::GATEWAY_MANUAL)->firstOrFail();

    expect($bkash->status)->toBe('active');
    expect($bkash->credentials['app_key'])->toBe('new-key');
    expect($bkash->credentials['username'])->toBe('new-user');
    expect($bkash->credentials['base_url'])->toBe('https://tokenized.pay.bka.sh');
    expect($bkash->credentials['app_secret'])->toBe('old-secret');
    expect($bkash->credentials['password'])->toBe('old-password');

    expect($sslCommerz->status)->toBe('inactive');
    expect($sslCommerz->credentials['store_id'])->toBe('new-store');
    expect($sslCommerz->credentials['mode'])->toBe('live');
    expect($sslCommerz->credentials['store_password'])->toBe('old-store-password');

    expect($manual->notes)->toBe('Manual payment will be approved by admin.');
});

it('admin without payment setting permission cannot access payment settings page', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.payment-settings.edit'))
        ->assertForbidden();
});
