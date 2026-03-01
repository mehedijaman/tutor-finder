<?php

use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

it('authorized admin can view site settings edit page', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('site-setting-view');

    SiteSetting::factory()->create([
        'id' => 1,
        'site_name' => 'Tutor Finder Pro',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.site-settings.edit'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/site-settings/Edit')
            ->where('siteSettingsFull.site_name', 'Tutor Finder Pro')
            ->has('siteSettingsFull.social_details')
            ->has('siteSettingsFull.addresses'),
        );
});

it('admin without site settings view permission cannot access site settings page', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.site-settings.edit'))
        ->assertForbidden();
});

it('authorized admin can update and normalize site settings values', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo(['site-setting-view', 'site-setting-update']);

    Storage::disk('public')->put('site-settings/old-logo.png', 'old-logo');

    $siteSetting = SiteSetting::factory()->create([
        'id' => 1,
        'site_name' => 'Old Name',
        'logo_path' => 'site-settings/old-logo.png',
        'phone_numbers' => ['+15550000001'],
        'emails' => ['old@example.com'],
        'addresses' => [
            ['label' => 'Old Office', 'address' => 'Old Address', 'map_url' => null],
        ],
        'social_details' => ['facebook' => 'https://facebook.com/old'],
    ]);

    $response = $this->actingAs($admin)
        ->put(route('admin.site-settings.update'), [
            'site_name' => '  New Site Name  ',
            'slogan' => '  Better Learning ',
            'description' => '  Trusted platform for tutors  ',
            'trade_licence_no' => ' TL-12345 ',
            'tin_no' => ' TIN-111 ',
            'bin_no' => ' BIN-222 ',
            'remove_logo' => '0',
            'logo' => UploadedFile::fake()->image('new-logo.png'),
            'phone_numbers' => ['+15550000002', '', ' +15550000003 '],
            'emails' => ['INFO@EXAMPLE.COM', '', 'support@example.com'],
            'addresses' => [
                ['label' => '', 'address' => '', 'map_url' => ''],
                ['label' => ' Head Office ', 'address' => ' Dhaka ', 'map_url' => 'https://maps.example.com/hq'],
                ['label' => 'Branch', 'address' => '  ', 'map_url' => 'https://maps.example.com/branch'],
            ],
            'social_details' => [
                ['platform' => 'Facebook', 'url' => 'https://facebook.com/new-page'],
                ['platform' => 'facebook', 'url' => 'https://facebook.com/final-page'],
                ['platform' => '', 'url' => ''],
                ['platform' => ' YouTube ', 'url' => 'https://youtube.com/@channel'],
            ],
        ]);

    $response->assertRedirect(route('admin.site-settings.edit', absolute: false));

    $siteSetting->refresh();

    expect($siteSetting->site_name)->toBe('New Site Name');
    expect($siteSetting->slogan)->toBe('Better Learning');
    expect($siteSetting->description)->toBe('Trusted platform for tutors');
    expect($siteSetting->trade_licence_no)->toBe('TL-12345');
    expect($siteSetting->tin_no)->toBe('TIN-111');
    expect($siteSetting->bin_no)->toBe('BIN-222');
    expect($siteSetting->phone_numbers)->toBe(['+15550000002', '+15550000003']);
    expect($siteSetting->emails)->toBe(['info@example.com', 'support@example.com']);
    expect($siteSetting->addresses)->toBe([
        [
            'label' => 'Head Office',
            'address' => 'Dhaka',
            'map_url' => 'https://maps.example.com/hq',
        ],
    ]);
    expect($siteSetting->social_details)->toBe([
        'facebook' => 'https://facebook.com/final-page',
        'youtube' => 'https://youtube.com/@channel',
    ]);

    expect($siteSetting->logo_path)->not->toBeNull();
    expect(Storage::disk('public')->exists('site-settings/old-logo.png'))->toBeFalse();
    expect(Storage::disk('public')->exists((string) $siteSetting->logo_path))->toBeTrue();
});

it('authorized admin can remove existing logo without uploading a replacement', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('site-setting-update');

    Storage::disk('public')->put('site-settings/current-logo.png', 'logo-content');

    $siteSetting = SiteSetting::factory()->create([
        'id' => 1,
        'site_name' => 'Sample Site',
        'logo_path' => 'site-settings/current-logo.png',
    ]);

    $response = $this->actingAs($admin)
        ->put(route('admin.site-settings.update'), [
            'site_name' => 'Sample Site',
            'remove_logo' => '1',
        ]);

    $response->assertRedirect(route('admin.site-settings.edit', absolute: false));

    expect(Storage::disk('public')->exists('site-settings/current-logo.png'))->toBeFalse();
    expect($siteSetting->refresh()->logo_path)->toBeNull();
});
