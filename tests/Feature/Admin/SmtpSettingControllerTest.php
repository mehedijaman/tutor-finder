<?php

use App\Models\SmtpSetting;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;

it('admin can view smtp settings index', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)->get(route('admin.smtp-settings.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('admin/smtp-settings/Index'));
});

it('admin can create an smtp setting', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)->post(route('admin.smtp-settings.store'), [
        'name' => 'Primary SMTP Gateway',
        'driver' => 'smtp',
        'from_address' => 'noreply@example.com',
        'from_name' => 'Test App',
        'credential_items' => [
            ['key' => 'host', 'value' => 'smtp.example.com'],
            ['key' => 'port', 'value' => '587'],
            ['key' => 'username', 'value' => 'user@example.com'],
            ['key' => 'password', 'value' => 'secret'],
            ['key' => 'encryption', 'value' => 'tls'],
        ],
        'is_default' => '1',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.smtp-settings.index', absolute: false));

    $smtpSetting = SmtpSetting::query()->first();

    expect($smtpSetting)->not->toBeNull();
    expect($smtpSetting?->name)->toBe('Primary SMTP Gateway');
    expect($smtpSetting?->driver)->toBe('smtp');
    expect($smtpSetting?->from_address)->toBe('noreply@example.com');
    expect($smtpSetting?->from_name)->toBe('Test App');
    expect($smtpSetting?->is_default)->toBeTrue();
    expect($smtpSetting?->is_active)->toBeTrue();
});

it('switches default smtp setting when a new default is saved', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $first = SmtpSetting::factory()->defaultSetting()->create([
        'name' => 'Gateway One',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.smtp-settings.store'), [
        'name' => 'Gateway Two',
        'driver' => 'smtp',
        'credential_items' => [
            ['key' => 'host', 'value' => 'smtp.two.com'],
            ['key' => 'port', 'value' => '587'],
            ['key' => 'username', 'value' => 'two@example.com'],
            ['key' => 'password', 'value' => 'secret2'],
            ['key' => 'encryption', 'value' => 'tls'],
        ],
        'is_default' => '1',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.smtp-settings.index', absolute: false));

    $first->refresh();
    $second = SmtpSetting::query()->where('name', 'Gateway Two')->firstOrFail();

    expect($first->is_default)->toBeFalse();
    expect($second->is_default)->toBeTrue();
});

it('marks first active smtp setting as default when none exists', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)->post(route('admin.smtp-settings.store'), [
        'name' => 'Fallback Gateway',
        'driver' => 'log',
        'credential_items' => [],
        'is_default' => '0',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.smtp-settings.index', absolute: false));

    $setting = SmtpSetting::query()->where('name', 'Fallback Gateway')->firstOrFail();

    expect($setting->is_default)->toBeTrue();
});

it('non admin user cannot access smtp settings index', function () {
    $user = User::factory()->guardian()->create();

    $response = $this->actingAs($user)->get(route('admin.smtp-settings.index'));

    $response->assertForbidden();
});

it('requires smtp mandatory credentials when active', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)->post(route('admin.smtp-settings.store'), [
        'name' => 'Incomplete SMTP',
        'driver' => 'smtp',
        'credential_items' => [
            ['key' => 'host', 'value' => 'smtp.example.com'],
        ],
        'is_default' => '0',
        'is_active' => '1',
    ]);

    $response->assertSessionHasErrors('credentials.port');
});

it('admin can delete an smtp setting', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $smtpSetting = SmtpSetting::factory()->create([
        'name' => 'To Be Deleted',
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.smtp-settings.destroy', $smtpSetting));

    $response->assertRedirect(route('admin.smtp-settings.index', absolute: false));

    expect(SmtpSetting::query()->find($smtpSetting->id))->toBeNull();
});

it('admin can update an smtp setting', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $smtpSetting = SmtpSetting::factory()->create([
        'name' => 'Original Name',
        'driver' => 'smtp',
    ]);

    $response = $this->actingAs($admin)->put(route('admin.smtp-settings.update', $smtpSetting), [
        'name' => 'Updated Name',
        'driver' => 'log',
        'from_address' => 'updated@example.com',
        'from_name' => 'Updated App',
        'credential_items' => [],
        'is_default' => '0',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.smtp-settings.index', absolute: false));

    $smtpSetting->refresh();

    expect($smtpSetting->name)->toBe('Updated Name');
    expect($smtpSetting->driver)->toBe('log');
    expect($smtpSetting->from_address)->toBe('updated@example.com');
});

it('forbids non admin user from creating smtp setting', function () {
    $user = User::factory()->guardian()->create();

    $response = $this->actingAs($user)->post(route('admin.smtp-settings.store'), [
        'name' => 'Unauthorized',
        'driver' => 'smtp',
        'credential_items' => [],
        'is_default' => '0',
        'is_active' => '0',
    ]);

    $response->assertForbidden();
});
