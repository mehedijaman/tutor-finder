<?php

use App\Models\SiteSetting;
use App\Models\User;
use App\Support\SiteSettingsResolver;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('shares a minimal site settings payload on public pages', function () {
    SiteSetting::factory()->create([
        'id' => 1,
        'site_name' => 'Public Site Name',
        'phone_numbers' => ['+15550000077'],
        'emails' => ['contact@example.com'],
        'addresses' => [
            ['label' => 'HQ', 'address' => 'Dhaka, Bangladesh', 'map_url' => 'https://maps.example.com'],
        ],
        'social_details' => [
            'facebook' => 'https://facebook.com/public-page',
            'youtube' => 'https://youtube.com/@public',
        ],
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('siteSettings.site_name', 'Public Site Name')
            ->where('siteSettings.primary_phone', '+15550000077')
            ->where('siteSettings.primary_email', 'contact@example.com')
            ->where('siteSettings.primary_address', 'Dhaka, Bangladesh')
            ->where('siteSettings.social_details.facebook', 'https://facebook.com/public-page')
            ->where('siteSettings.social_details.youtube', 'https://youtube.com/@public')
            ->where('siteSettings', function ($payload): bool {
                $data = $payload instanceof \Illuminate\Support\Collection
                    ? $payload->all()
                    : (array) $payload;

                expect($data)->toHaveKeys([
                    'site_name',
                    'logo_url',
                    'primary_phone',
                    'primary_email',
                    'primary_address',
                    'social_details',
                ]);

                expect($data)->not->toHaveKeys([
                    'description',
                    'slogan',
                    'tin_no',
                    'bin_no',
                    'trade_licence_no',
                    'addresses',
                    'emails',
                    'phone_numbers',
                ]);

                return true;
            }),
        );
});

it('invalidates shared site settings cache after admin update', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    SiteSetting::factory()->create([
        'id' => 1,
        'site_name' => 'Cached Name',
        'phone_numbers' => ['+15550000123'],
        'emails' => ['cached@example.com'],
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('siteSettings.site_name', 'Cached Name'),
        );

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('site-setting-update');

    $this->actingAs($admin)
        ->put(route('admin.site-settings.update'), [
            'site_name' => 'Fresh Name',
            'remove_logo' => '0',
            'phone_numbers' => ['+15550000999'],
            'emails' => ['fresh@example.com'],
        ])
        ->assertRedirect(route('admin.site-settings.edit', absolute: false));

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('siteSettings.site_name', 'Fresh Name')
            ->where('siteSettings.primary_phone', '+15550000999')
            ->where('siteSettings.primary_email', 'fresh@example.com'),
        );

    app(SiteSettingsResolver::class)->flush();

    expect(SiteSetting::current()->site_name)->toBe('Fresh Name');
});
