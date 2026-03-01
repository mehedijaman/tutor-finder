<?php

use App\Models\SmsSetting;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;

it('admin can create an sms setting', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)->post(route('admin.sms-settings.store'), [
        'name' => 'Primary OTP Gateway',
        'provider' => 'Ssl',
        'credentials_json' => '{"api_token":"abc","sid":"SID","csms_id":"Sender"}',
        'is_default' => '1',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.sms-settings.index', absolute: false));

    $smsSetting = SmsSetting::query()->first();

    expect($smsSetting)->not->toBeNull();
    expect($smsSetting?->name)->toBe('Primary OTP Gateway');
    expect($smsSetting?->provider)->toBe('Ssl');
    expect($smsSetting?->is_default)->toBeTrue();
    expect($smsSetting?->is_active)->toBeTrue();
});

it('switches default sms setting when a new default is saved', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $first = SmsSetting::factory()->defaultSetting()->create([
        'name' => 'Gateway One',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.sms-settings.store'), [
        'name' => 'Gateway Two',
        'provider' => 'Ssl',
        'credentials_json' => '{"api_token":"two","sid":"SID","csms_id":"Sender"}',
        'is_default' => '1',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.sms-settings.index', absolute: false));

    $first->refresh();
    $second = SmsSetting::query()->where('name', 'Gateway Two')->firstOrFail();

    expect($first->is_default)->toBeFalse();
    expect($second->is_default)->toBeTrue();
});

it('marks first active sms setting as default when none exists', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)->post(route('admin.sms-settings.store'), [
        'name' => 'Fallback Gateway',
        'provider' => 'Ssl',
        'credentials_json' => '{"api_token":"fallback"}',
        'is_default' => '0',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.sms-settings.index', absolute: false));

    $setting = SmsSetting::query()->where('name', 'Fallback Gateway')->firstOrFail();

    expect($setting->is_default)->toBeTrue();
});

it('non admin user cannot access sms settings index', function () {
    $user = User::factory()->guardian()->create();

    $response = $this->actingAs($user)->get(route('admin.sms-settings.index'));

    $response->assertForbidden();
});
