<?php

use App\Models\User;
use App\Models\VerificationRequest;

it('tutor can submit a verification request', function () {
    $tutor = User::factory()->tutor()->create();

    $response = $this->actingAs($tutor)
        ->post(route('tutor.verification.request'));

    $response->assertRedirect(route('tutor.verification.show', absolute: false));

    $request = VerificationRequest::query()->where('user_id', $tutor->id)->first();

    expect($request)->not->toBeNull();
    expect($request->role)->toBe(VerificationRequest::ROLE_TUTOR);
    expect($request->status)->toBe(VerificationRequest::STATUS_PENDING);

    $tutor->refresh();

    expect($tutor->verification_status)->toBe(User::VERIFICATION_STATUS_PENDING);
    expect($tutor->verification_type)->toBe(VerificationRequest::ROLE_TUTOR);
    expect($tutor->verified_at)->toBeNull();
});

it('blocks duplicate active verification requests for tutor', function () {
    $tutor = User::factory()->tutor()->create([
        'verification_status' => User::VERIFICATION_STATUS_PENDING,
        'verification_type' => VerificationRequest::ROLE_TUTOR,
    ]);

    VerificationRequest::factory()->create([
        'user_id' => $tutor->id,
        'role' => VerificationRequest::ROLE_TUTOR,
        'status' => VerificationRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($tutor)
        ->from(route('tutor.verification.show'))
        ->post(route('tutor.verification.request'));

    $response
        ->assertRedirect(route('tutor.verification.show', absolute: false))
        ->assertSessionHasErrors('verification');

    expect(
        VerificationRequest::query()
            ->where('user_id', $tutor->id)
            ->count()
    )->toBe(1);
});
