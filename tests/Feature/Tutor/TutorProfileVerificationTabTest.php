<?php

use App\Enums\VerificationRole;
use App\Enums\VerificationStatus;
use App\Models\User;
use App\Models\VerificationRequest;
use Spatie\LaravelPdf\Facades\Pdf;

it('tutor profile page includes verification data', function () {
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
        ->get(route('tutor.profile.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('tutor/Profile/Edit')
        ->has('verification')
        ->has('verificationStatus')
        ->has('verifiedAt')
    );
});

it('tutor profile page returns null verification when no request exists', function () {
    $tutor = User::factory()->tutor()->create();

    $response = $this->actingAs($tutor)
        ->get(route('tutor.profile.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('tutor/Profile/Edit')
        ->where('verification', null)
        ->has('verificationStatus')
    );
});

it('tutor verification show redirects to profile page', function () {
    $tutor = User::factory()->tutor()->create();

    $response = $this->actingAs($tutor)
        ->get(route('tutor.verification.show'));

    $response->assertRedirect(route('tutor.profile.edit', absolute: false));
});

it('tutor can download cv as pdf', function () {
    Pdf::fake();

    $tutor = User::factory()->tutor()->create();

    $response = $this->actingAs($tutor)
        ->get(route('tutor.profile.download-cv'));

    $response->assertOk();

    Pdf::assertRespondedWithPdf(function ($pdf) use ($tutor) {
        return $pdf->viewName === 'pdf.tutor-cv'
            && $pdf->downloadName === "tutor-cv-{$tutor->id}.pdf";
    });
});

it('tutor can open guardian profile preview', function () {
    $tutor = User::factory()->tutor()->create();

    $response = $this->actingAs($tutor)
        ->get(route('tutor.profile.view-as-guardian'));

    $response->assertRedirect(route('tutors.show', $tutor->id, absolute: false));
});
