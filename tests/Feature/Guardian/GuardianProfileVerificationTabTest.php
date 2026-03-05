<?php

use App\Enums\VerificationRole;
use App\Enums\VerificationStatus;
use App\Models\User;
use App\Models\VerificationRequest;

it('guardian profile page includes verification data', function () {
    $guardian = User::factory()->guardian()->create([
        'verification_status' => VerificationStatus::Pending,
        'verification_type' => VerificationRole::Guardian,
    ]);

    VerificationRequest::factory()->create([
        'user_id' => $guardian->id,
        'role' => VerificationRole::Guardian,
        'status' => VerificationStatus::Pending,
    ]);

    $response = $this->actingAs($guardian)
        ->get(route('guardian.profile.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('guardian/Profile/Edit')
        ->has('verification')
        ->has('verificationStatus')
        ->has('verifiedAt')
    );
});

it('guardian profile page returns null verification when no request exists', function () {
    $guardian = User::factory()->guardian()->create();

    $response = $this->actingAs($guardian)
        ->get(route('guardian.profile.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('guardian/Profile/Edit')
        ->where('verification', null)
        ->has('verificationStatus')
    );
});

it('guardian verification show redirects to profile page', function () {
    $guardian = User::factory()->guardian()->create();

    $response = $this->actingAs($guardian)
        ->get(route('guardian.verification.show'));

    $response->assertRedirect(route('guardian.profile.edit', absolute: false));
});
