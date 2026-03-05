<?php

use App\Enums\VerificationRole;
use App\Enums\VerificationStatus;
use App\Models\User;
use App\Models\VerificationRequest;

it('tutor can submit a verification request', function () {
    $tutor = User::factory()->tutor()->create();

    $response = $this->actingAs($tutor)
        ->post(route('tutor.verification.request'));

    $response->assertRedirect(route('tutor.profile.edit', absolute: false));

    $request = VerificationRequest::query()->where('user_id', $tutor->id)->first();

    expect($request)->not->toBeNull();
    expect($request->role)->toBe(VerificationRole::Tutor);
    expect($request->status)->toBe(VerificationStatus::Pending);

    $tutor->refresh();

    expect($tutor->verification_status)->toBe(VerificationStatus::Pending);
    expect($tutor->verification_type)->toBe(VerificationRole::Tutor->value);
    expect($tutor->verified_at)->toBeNull();
});

it('blocks duplicate active verification requests for tutor', function () {
    $tutor = User::factory()->tutor()->create([
        'verification_status' => VerificationStatus::Pending,
        'verification_type' => VerificationRole::Tutor,
    ]);

    VerificationRequest::factory()->create([
        'user_id' => $tutor->id,
        'role' => VerificationRole::Tutor,
        'status' => VerificationStatus::Pending,
    ]);

    $response = $this->actingAs($tutor)
        ->from(route('tutor.profile.edit'))
        ->post(route('tutor.verification.request'));

    $response
        ->assertRedirect(route('tutor.profile.edit', absolute: false))
        ->assertSessionHasErrors('verification');

    expect(
        VerificationRequest::query()
            ->where('user_id', $tutor->id)
            ->count()
    )->toBe(1);
});
