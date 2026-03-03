<?php

use App\Models\User;
use App\Models\VerificationRequest;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('admin can create another admin and assign role', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create([
        'email' => 'super@example.com',
    ]);
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Second Admin',
        'email' => 'admin2@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => ['admin'],
        'permissions' => ['tutor-view'],
    ]);

    $response->assertRedirect(route('admin.users.index', absolute: false));

    $newAdmin = User::query()->where('email', 'admin2@example.com')->first();

    expect($newAdmin)->not->toBeNull();
    expect($newAdmin->role)->toBe('admin');
    expect($newAdmin->hasRole('admin'))->toBeTrue();
    expect($newAdmin->can('tutor-view'))->toBeTrue();
});

it('admin can view create role page', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create([
        'email' => 'role-creator@example.com',
    ]);
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get(route('admin.roles.create'))
        ->assertSuccessful();
});

it('admin can create tutor account', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('tutor-create');

    $response = $this->actingAs($admin)->post(route('admin.tutors.store'), [
        'name' => 'New Tutor',
        'email' => 'new-tutor@example.com',
        'phone' => '01700000001',
        'password' => 'password',
        'password_confirmation' => 'password',
        'status' => 'active',
    ]);

    $response->assertRedirect(route('admin.tutors.index', absolute: false));

    $newTutor = User::query()->where('email', 'new-tutor@example.com')->first();

    expect($newTutor)->not->toBeNull();
    expect($newTutor->role)->toBe('tutor');
    expect($newTutor->verification_status)->toBe(User::VERIFICATION_STATUS_UNVERIFIED);
});

it('admin can create guardian account', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('guardian-create');

    $response = $this->actingAs($admin)->post(route('admin.guardians.store'), [
        'name' => 'New Guardian',
        'email' => 'new-guardian@example.com',
        'phone' => '01700000002',
        'password' => 'password',
        'password_confirmation' => 'password',
        'status' => 'active',
    ]);

    $response->assertRedirect(route('admin.guardians.index', absolute: false));

    $newGuardian = User::query()->where('email', 'new-guardian@example.com')->first();

    expect($newGuardian)->not->toBeNull();
    expect($newGuardian->role)->toBe('guardian');
    expect($newGuardian->verification_status)->toBe(User::VERIFICATION_STATUS_UNVERIFIED);
});

it('filters tutors by verification scope', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $pendingTutor = User::factory()->tutor()->create([
        'verification_status' => User::VERIFICATION_STATUS_PENDING,
    ]);

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $pendingTutor->id,
        'role' => VerificationRequest::ROLE_TUTOR,
        'status' => VerificationRequest::STATUS_PENDING,
    ]);

    User::factory()->tutor()->create([
        'verification_status' => User::VERIFICATION_STATUS_VERIFIED,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.tutors.index', ['verification' => 'pending']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/tutors/Index')
            ->where('filters.verification', 'pending')
            ->has('items.data', 1)
            ->where('items.data.0.verification_request_id', $verificationRequest->id)
            ->where('items.data.0.verification_request_status', VerificationRequest::STATUS_PENDING));
});

it('filters guardians by verification scope', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    User::factory()->guardian()->create([
        'verification_status' => User::VERIFICATION_STATUS_UNVERIFIED,
    ]);
    User::factory()->guardian()->create([
        'verification_status' => User::VERIFICATION_STATUS_VERIFIED,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.guardians.index', ['verification' => 'unverified']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/guardians/Index')
            ->where('filters.verification', 'unverified')
            ->has('items.data', 1));
});

it('filters guardians by pending verification scope with request metadata', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $pendingGuardian = User::factory()->guardian()->create([
        'verification_status' => User::VERIFICATION_STATUS_INVOICED,
    ]);

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $pendingGuardian->id,
        'role' => VerificationRequest::ROLE_GUARDIAN,
        'status' => VerificationRequest::STATUS_INVOICED,
    ]);

    User::factory()->guardian()->create([
        'verification_status' => User::VERIFICATION_STATUS_VERIFIED,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.guardians.index', ['verification' => 'pending']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/guardians/Index')
            ->where('filters.verification', 'pending')
            ->has('items.data', 1)
            ->where('items.data.0.verification_request_id', $verificationRequest->id)
            ->where('items.data.0.verification_request_status', VerificationRequest::STATUS_INVOICED));
});
