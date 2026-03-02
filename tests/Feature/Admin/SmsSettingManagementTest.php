<?php

use App\Contracts\SmsSender;
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
        'credential_items' => [
            ['key' => 'api_token', 'value' => 'abc'],
            ['key' => 'sid', 'value' => 'SID'],
            ['key' => 'csms_id', 'value' => 'Sender'],
        ],
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
        'credential_items' => [
            ['key' => 'api_token', 'value' => 'two'],
            ['key' => 'sid', 'value' => 'SID'],
            ['key' => 'csms_id', 'value' => 'Sender'],
        ],
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
        'credential_items' => [
            ['key' => 'api_token', 'value' => 'fallback'],
            ['key' => 'sid', 'value' => 'FALLBACKSID'],
            ['key' => 'csms_id', 'value' => 'FALLBACKSMS'],
        ],
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

it('requires provider mandatory credentials from laravelbdsms docs', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)->post(route('admin.sms-settings.store'), [
        'name' => 'Incomplete SSL',
        'provider' => 'Ssl',
        'credential_items' => [
            ['key' => 'api_token', 'value' => 'abc'],
        ],
        'is_default' => '0',
        'is_active' => '1',
    ]);

    $response->assertSessionHasErrors('credentials');
});

it('admin can send a test sms using default gateway', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $calls = [];

    $this->app->instance(SmsSender::class, new class($calls) implements SmsSender
    {
        /**
         * @var array<int, array{phone: string, message: string}>
         */
        private array $calls = [];

        /**
         * @param  array<int, array{phone: string, message: string}>  $calls
         */
        public function __construct(array &$calls)
        {
            $this->calls = &$calls;
        }

        public function send(string $phone, string $message): void
        {
            $this->calls[] = [
                'phone' => $phone,
                'message' => $message,
            ];
        }
    });

    $response = $this->actingAs($admin)->post(route('admin.sms-settings.test'), [
        'mobile' => '+88 01712345678',
        'message' => '  Test SMS payload  ',
    ]);

    $response->assertRedirect(route('admin.sms-settings.index', absolute: false));
    $response->assertSessionHasNoErrors();

    expect($calls)->toBe([
        [
            'phone' => '+8801712345678',
            'message' => 'Test SMS payload',
        ],
    ]);
});

it('returns validation errors when sending test sms with invalid payload', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)->post(route('admin.sms-settings.test'), [
        'mobile' => 'invalid',
        'message' => '',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['mobile', 'message']);
});

it('returns gateway errors when sending test sms fails', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $this->app->instance(SmsSender::class, new class implements SmsSender
    {
        public function send(string $phone, string $message): void
        {
            throw new \RuntimeException('Failed to send SMS via configured gateway.');
        }
    });

    $response = $this->actingAs($admin)->post(route('admin.sms-settings.test'), [
        'mobile' => '01712345678',
        'message' => 'Gateway test',
    ]);

    $response->assertRedirect(route('admin.sms-settings.index', absolute: false));
    $response->assertSessionHasErrors('sms');
});

it('forbids non admin user from sending test sms', function () {
    $user = User::factory()->guardian()->create();

    $response = $this->actingAs($user)->post(route('admin.sms-settings.test'), [
        'mobile' => '01712345678',
        'message' => 'Not allowed',
    ]);

    $response->assertForbidden();
});
