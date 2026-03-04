<?php

use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;

it('admin can view activity logs in admin panel', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $subject = User::factory()->tutor()->create([
        'name' => 'Target Tutor',
    ]);
    $causer = User::factory()->admin()->create([
        'name' => 'Moderator Admin',
    ]);

    // Clear auto-logged User creation entries before adding manual ones
    Activity::query()->delete();

    $olderActivity = activity('admin')
        ->causedBy($causer)
        ->performedOn($subject)
        ->event('updated')
        ->withProperties(['status' => 'active'])
        ->log('Updated tutor status');

    $latestActivity = activity('security')
        ->event('login')
        ->log('Admin logged in');

    $olderActivity->forceFill([
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ])->save();

    $latestActivity->forceFill([
        'created_at' => now(),
        'updated_at' => now(),
    ])->save();

    $this->actingAs($admin)
        ->get(route('admin.activity-logs.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/activity-logs/Index')
            ->where('filters.search', '')
            ->where('filters.sort', 'created_at')
            ->where('filters.direction', 'desc')
            ->has('items.data', 2)
            ->where('items.data.0.description', 'Admin logged in')
            ->where('items.data.1.description', 'Updated tutor status')
            ->where('items.data.1.causer', fn (string $value) => str_contains($value, 'Moderator Admin'))
            ->where('items.data.1.subject', fn (string $value) => str_contains($value, 'Target Tutor')),
        );
});

it('admin without activity log permission cannot view activity logs', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.activity-logs.index'))
        ->assertForbidden();
});
