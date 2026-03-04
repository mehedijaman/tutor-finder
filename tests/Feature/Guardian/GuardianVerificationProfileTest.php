<?php

use App\Enums\VerificationRole;
use App\Enums\VerificationStatus;
use App\Models\GuardianProfile;
use App\Models\User;
use App\Models\VerificationRequest;

it('guardian can update profile information', function () {
    $guardian = User::factory()->guardian()->create();

    GuardianProfile::factory()->create([
        'user_id' => $guardian->id,
    ]);

    $response = $this->actingAs($guardian)->put(route('guardian.profile.update'), [
        'name' => 'Updated Guardian',
        'phone' => '+8801912345678',
        'phone_alt' => '+8801711111111',
        'guardian_name' => 'Parent Name',
        'address' => 'Uttara, Dhaka',
        'occupation' => 'Teacher',
        'notes' => 'Available after office hours',
        'status' => 'active',
    ]);

    $response->assertRedirect(route('guardian.profile.edit', absolute: false));

    $guardian->refresh();

    expect($guardian->name)->toBe('Updated Guardian');
    expect($guardian->phone)->toBe('+8801912345678');

    $profile = GuardianProfile::query()->where('user_id', $guardian->id)->firstOrFail();

    expect($profile->phone_alt)->toBe('+8801711111111');
    expect($profile->address)->toBe('Uttara, Dhaka');
});

it('guardian profile update creates profile when missing', function () {
    $guardian = User::factory()->guardian()->create();

    expect(GuardianProfile::query()->where('user_id', $guardian->id)->exists())->toBeFalse();

    $response = $this->actingAs($guardian)->put(route('guardian.profile.update'), [
        'name' => 'First Time Guardian',
        'phone' => '+8801612345678',
        'phone_alt' => '+8801511111111',
        'guardian_name' => 'Guardian Parent',
        'address' => 'Dhanmondi, Dhaka',
        'occupation' => 'Banker',
        'notes' => 'Evening calls preferred',
        'status' => 'active',
    ]);

    $response->assertRedirect(route('guardian.profile.edit', absolute: false));

    $profile = GuardianProfile::query()->where('user_id', $guardian->id)->first();

    expect($profile)->not->toBeNull();
    expect($profile?->guardian_name)->toBe('Guardian Parent');
    expect($profile?->occupation)->toBe('Banker');
});

it('guardian can submit verification request', function () {
    $guardian = User::factory()->guardian()->create();

    $response = $this->actingAs($guardian)
        ->post(route('guardian.verification.request'));

    $response->assertRedirect(route('guardian.verification.show', absolute: false));

    $request = VerificationRequest::query()->where('user_id', $guardian->id)->first();

    expect($request)->not->toBeNull();
    expect($request->role)->toBe(VerificationRole::Guardian);
    expect($request->status)->toBe(VerificationStatus::Pending);

    $guardian->refresh();

    expect($guardian->verification_status)->toBe(VerificationStatus::Pending);
    expect($guardian->verification_type)->toBe(VerificationRole::Guardian->value);
});
