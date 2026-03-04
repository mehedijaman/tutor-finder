<?php

use App\Models\TuitionJob;
use App\Models\User;

it('allows guardian to view their own job', function () {
    $guardian = User::factory()->guardian()->create();
    $job = TuitionJob::factory()->create(['guardian_id' => $guardian->id]);

    expect($guardian->can('view', $job))->toBeTrue();
});

it('forbids guardian from viewing another guardians job', function () {
    $guardian = User::factory()->guardian()->create();
    $otherGuardian = User::factory()->guardian()->create();
    $job = TuitionJob::factory()->create(['guardian_id' => $otherGuardian->id]);

    expect($guardian->can('view', $job))->toBeFalse();
});

it('forbids tutor from viewing a job via policy', function () {
    $tutor = User::factory()->tutor()->create();
    $job = TuitionJob::factory()->create();

    expect($tutor->can('view', $job))->toBeFalse();
});

it('allows guardian to create a job', function () {
    $guardian = User::factory()->guardian()->create();

    expect($guardian->can('create', TuitionJob::class))->toBeTrue();
});

it('forbids tutor from creating a job', function () {
    $tutor = User::factory()->tutor()->create();

    expect($tutor->can('create', TuitionJob::class))->toBeFalse();
});

it('allows guardian to update their own job', function () {
    $guardian = User::factory()->guardian()->create();
    $job = TuitionJob::factory()->create(['guardian_id' => $guardian->id]);

    expect($guardian->can('update', $job))->toBeTrue();
});

it('forbids guardian from updating another guardians job', function () {
    $guardian = User::factory()->guardian()->create();
    $otherGuardian = User::factory()->guardian()->create();
    $job = TuitionJob::factory()->create(['guardian_id' => $otherGuardian->id]);

    expect($guardian->can('update', $job))->toBeFalse();
});

it('allows guardian to manage applications for their own job', function () {
    $guardian = User::factory()->guardian()->create();
    $job = TuitionJob::factory()->create(['guardian_id' => $guardian->id]);

    expect($guardian->can('manageApplications', $job))->toBeTrue();
});

it('forbids guardian from managing applications for another guardians job', function () {
    $guardian = User::factory()->guardian()->create();
    $otherGuardian = User::factory()->guardian()->create();
    $job = TuitionJob::factory()->create(['guardian_id' => $otherGuardian->id]);

    expect($guardian->can('manageApplications', $job))->toBeFalse();
});

it('forbids tutor from managing applications', function () {
    $tutor = User::factory()->tutor()->create();
    $job = TuitionJob::factory()->create();

    expect($tutor->can('manageApplications', $job))->toBeFalse();
});
