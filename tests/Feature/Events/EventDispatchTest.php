<?php

use App\Enums\ApplicationStatus;
use App\Enums\FeePaymentMode;
use App\Events\ApplicationStatusUpdated;
use App\Events\ApplicationSubmitted;
use App\Events\ApplicationWithdrawn;
use App\Events\HireConfirmed;
use App\Models\SiteSetting;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\User;
use Illuminate\Support\Facades\Event;

it('dispatches ApplicationSubmitted when tutor applies to a job', function () {
    Event::fake([ApplicationSubmitted::class]);

    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(5),
    ]);

    $this->actingAs($tutor)
        ->post(route('tutor.jobs.apply', ['tuitionJob' => $job->slug]), [
            'cover_letter' => 'I am interested.',
            'expected_salary_amount' => 10000,
        ])
        ->assertRedirect();

    Event::assertDispatched(ApplicationSubmitted::class, function (ApplicationSubmitted $event) use ($job, $tutor) {
        return $event->tuitionJob->id === $job->id
            && $event->tutor->id === $tutor->id
            && $event->resubmitted === false;
    });
});

it('dispatches ApplicationSubmitted with resubmitted flag on reapply', function () {
    Event::fake([ApplicationSubmitted::class]);

    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(5),
    ]);

    TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Cancelled,
    ]);

    $this->actingAs($tutor)
        ->post(route('tutor.jobs.apply', ['tuitionJob' => $job->slug]), [
            'cover_letter' => 'Reapplying.',
            'expected_salary_amount' => 12000,
        ])
        ->assertRedirect();

    Event::assertDispatched(ApplicationSubmitted::class, function (ApplicationSubmitted $event) use ($job) {
        return $event->tuitionJob->id === $job->id
            && $event->resubmitted === true;
    });
});

it('dispatches ApplicationWithdrawn when tutor withdraws', function () {
    Event::fake([ApplicationWithdrawn::class]);

    $tutor = User::factory()->tutor()->create();

    $application = TuitionJobApplication::factory()->create([
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Applied,
    ]);

    $this->actingAs($tutor)
        ->patch(route('tutor.job-applications.withdraw', ['tuitionJobApplication' => $application->id]))
        ->assertRedirect();

    Event::assertDispatched(ApplicationWithdrawn::class, function (ApplicationWithdrawn $event) use ($application, $tutor) {
        return $event->application->id === $application->id
            && $event->tutor->id === $tutor->id;
    });
});

it('dispatches ApplicationStatusUpdated when guardian updates application status', function () {
    Event::fake([ApplicationStatusUpdated::class]);

    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(10),
    ]);

    $application = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Applied,
    ]);

    $this->actingAs($guardian)
        ->patch(route('guardian.jobs.applications.status', [
            'tuitionJob' => $job->id,
            'tuitionJobApplication' => $application->id,
        ]), [
            'status' => ApplicationStatus::Shortlisted->value,
            'cancel_reason' => null,
        ])
        ->assertRedirect();

    Event::assertDispatched(ApplicationStatusUpdated::class, function (ApplicationStatusUpdated $event) use ($job, $application) {
        return $event->tuitionJob->id === $job->id
            && $event->application->id === $application->id
            && $event->status === ApplicationStatus::Shortlisted->value;
    });
});

it('dispatches HireConfirmed when guardian confirms engagement', function () {
    Event::fake([HireConfirmed::class]);

    $guardian = User::factory()->guardian()->create();
    $platformUser = User::factory()->admin()->create([
        'role' => 'platform',
        'status' => 'active',
    ]);
    $selectedTutor = User::factory()->tutor()->create();
    $otherTutor = User::factory()->tutor()->create();

    SiteSetting::query()->updateOrCreate(
        ['id' => 1],
        [
            'site_name' => 'Tutor Finder',
            'phone_numbers' => [],
            'emails' => [],
            'addresses' => [],
            'social_details' => [],
            'platform_owner_user_id' => $platformUser->id,
            'platform_service_fee_rate' => 0.50000,
            'platform_service_fee_due_days' => 10,
            'default_fee_currency' => 'BDT',
            'default_fee_payment_mode' => FeePaymentMode::PayBefore->value,
        ],
    );

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'salary_amount' => 15000,
        'expires_at' => now()->addDays(15),
    ]);

    $selectedApplication = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $selectedTutor->id,
        'status' => ApplicationStatus::Shortlisted,
    ]);

    TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $otherTutor->id,
        'status' => ApplicationStatus::Applied,
    ]);

    $this->actingAs($guardian)
        ->patch(route('guardian.jobs.applications.confirm', [
            'tuitionJob' => $job->id,
            'tuitionJobApplication' => $selectedApplication->id,
        ]), [
            'month1_escrow_required' => false,
            'month1_escrow_amount' => null,
            'notes' => null,
        ])
        ->assertRedirect();

    Event::assertDispatched(HireConfirmed::class, function (HireConfirmed $event) use ($job, $selectedTutor, $otherTutor) {
        return $event->tuitionJob->id === $job->id
            && $event->selectedTutorUserId === $selectedTutor->id
            && in_array($otherTutor->id, $event->rejectedTutorUserIds);
    });
});
