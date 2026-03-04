<?php

use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\User;

it('allows tutor to withdraw their own application', function () {
    $tutor = User::factory()->tutor()->create();
    $application = TuitionJobApplication::factory()->create(['tutor_user_id' => $tutor->id]);

    expect($tutor->can('withdraw', $application))->toBeTrue();
});

it('forbids tutor from withdrawing another tutors application', function () {
    $tutor = User::factory()->tutor()->create();
    $otherTutor = User::factory()->tutor()->create();
    $application = TuitionJobApplication::factory()->create(['tutor_user_id' => $otherTutor->id]);

    expect($tutor->can('withdraw', $application))->toBeFalse();
});

it('forbids guardian from withdrawing an application', function () {
    $guardian = User::factory()->guardian()->create();
    $application = TuitionJobApplication::factory()->create();

    expect($guardian->can('withdraw', $application))->toBeFalse();
});

it('allows tutor to view their own application', function () {
    $tutor = User::factory()->tutor()->create();
    $application = TuitionJobApplication::factory()->create(['tutor_user_id' => $tutor->id]);

    expect($tutor->can('view', $application))->toBeTrue();
});

it('forbids tutor from viewing another tutors application', function () {
    $tutor = User::factory()->tutor()->create();
    $otherTutor = User::factory()->tutor()->create();
    $application = TuitionJobApplication::factory()->create(['tutor_user_id' => $otherTutor->id]);

    expect($tutor->can('view', $application))->toBeFalse();
});

it('allows guardian to view application for their own job', function () {
    $guardian = User::factory()->guardian()->create();
    $job = TuitionJob::factory()->create(['guardian_id' => $guardian->id]);
    $application = TuitionJobApplication::factory()->create(['job_id' => $job->id]);

    expect($guardian->can('view', $application))->toBeTrue();
});

it('forbids guardian from viewing application for another guardians job', function () {
    $guardian = User::factory()->guardian()->create();
    $otherGuardian = User::factory()->guardian()->create();
    $job = TuitionJob::factory()->create(['guardian_id' => $otherGuardian->id]);
    $application = TuitionJobApplication::factory()->create(['job_id' => $job->id]);

    expect($guardian->can('view', $application))->toBeFalse();
});

it('allows tutor to create an application', function () {
    $tutor = User::factory()->tutor()->create();

    expect($tutor->can('create', TuitionJobApplication::class))->toBeTrue();
});

it('forbids guardian from creating an application', function () {
    $guardian = User::factory()->guardian()->create();

    expect($guardian->can('create', TuitionJobApplication::class))->toBeFalse();
});
