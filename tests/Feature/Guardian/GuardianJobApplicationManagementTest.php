<?php

use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\TuitionJobAssignment;
use App\Models\User;
use App\Notifications\JobLifecycleNotification;
use Inertia\Testing\AssertableInertia as Assert;

it('guardian can view own job applications and update status', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(10),
    ]);

    $application = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => TuitionJobApplication::STATUS_APPLIED,
    ]);

    $this->actingAs($guardian)
        ->get(route('guardian.jobs.applications.index', ['tuitionJob' => $job->id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/jobs/Applications')
            ->where('job.id', $job->id)
            ->has('items.data', 1));

    $this->actingAs($guardian)
        ->patch(route('guardian.jobs.applications.status', [
            'tuitionJob' => $job->id,
            'tuitionJobApplication' => $application->id,
        ]), [
            'status' => TuitionJobApplication::STATUS_SHORTLISTED,
            'cancel_reason' => null,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tuition_job_applications', [
        'id' => $application->id,
        'status' => TuitionJobApplication::STATUS_SHORTLISTED,
        'cancel_reason' => null,
    ]);

    $tutor->refresh();
    expect($tutor->unreadNotifications)->toHaveCount(1);
    expect($tutor->unreadNotifications->first()->data['event'])->toBe('job-application-status-updated');
});

it('guardian cannot manage applications of another guardians job', function () {
    $owner = User::factory()->guardian()->create();
    $otherGuardian = User::factory()->guardian()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $owner->id,
        'expires_at' => now()->addDays(7),
    ]);

    $application = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'status' => TuitionJobApplication::STATUS_APPLIED,
    ]);

    $this->actingAs($otherGuardian)
        ->get(route('guardian.jobs.applications.index', ['tuitionJob' => $job->id]))
        ->assertForbidden();

    $this->actingAs($otherGuardian)
        ->patch(route('guardian.jobs.applications.status', [
            'tuitionJob' => $job->id,
            'tuitionJobApplication' => $application->id,
        ]), [
            'status' => TuitionJobApplication::STATUS_CANCELLED,
        ])
        ->assertForbidden();
});

it('guardian cannot shortlist when assignment already exists', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $selectedTutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(7),
    ]);

    $application = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => TuitionJobApplication::STATUS_APPLIED,
    ]);

    TuitionJobAssignment::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $selectedTutor->id,
    ]);

    $this->actingAs($guardian)
        ->from(route('guardian.jobs.applications.index', ['tuitionJob' => $job->id]))
        ->patch(route('guardian.jobs.applications.status', [
            'tuitionJob' => $job->id,
            'tuitionJobApplication' => $application->id,
        ]), [
            'status' => TuitionJobApplication::STATUS_SHORTLISTED,
        ])
        ->assertRedirect(route('guardian.jobs.applications.index', ['tuitionJob' => $job->id], false))
        ->assertSessionHasErrors(['status']);
});

it('guardian applications page marks management as locked for expired live jobs', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->subMinute(),
    ]);

    TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => TuitionJobApplication::STATUS_APPLIED,
    ]);

    $this->actingAs($guardian)
        ->get(route('guardian.jobs.applications.index', ['tuitionJob' => $job->id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/jobs/Applications')
            ->where('job.is_expired', true)
            ->where('job.can_manage_applications', false));
});

it('guardian can confirm shortlisted tutor engagement and close other open applications', function () {
    $guardian = User::factory()->guardian()->create();
    $selectedTutor = User::factory()->tutor()->create();
    $otherTutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(15),
    ]);

    $selectedApplication = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $selectedTutor->id,
        'status' => TuitionJobApplication::STATUS_SHORTLISTED,
    ]);

    $otherApplication = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $otherTutor->id,
        'status' => TuitionJobApplication::STATUS_APPLIED,
    ]);

    $this->actingAs($guardian)
        ->patch(route('guardian.jobs.applications.confirm', [
            'tuitionJob' => $job->id,
            'tuitionJobApplication' => $selectedApplication->id,
        ]))
        ->assertRedirect();

    $this->assertDatabaseHas('tuition_jobs', [
        'id' => $job->id,
        'status' => TuitionJob::STATUS_CONFIRMED,
        'confirmed_by' => $guardian->id,
    ]);

    $this->assertDatabaseHas('tuition_job_assignments', [
        'job_id' => $job->id,
        'tutor_user_id' => $selectedTutor->id,
    ]);

    $assignment = TuitionJobAssignment::query()
        ->where('job_id', $job->id)
        ->firstOrFail();

    expect($assignment->appointed_at)->not->toBeNull();
    expect($assignment->confirmed_at)->not->toBeNull();
    expect($assignment->appointed_at?->equalTo($assignment->confirmed_at))->toBeTrue();

    $this->assertDatabaseHas('tuition_job_applications', [
        'id' => $selectedApplication->id,
        'status' => TuitionJobApplication::STATUS_CONFIRMED,
    ]);

    $this->assertDatabaseHas('tuition_job_applications', [
        'id' => $otherApplication->id,
        'status' => TuitionJobApplication::STATUS_CANCELLED,
    ]);

    $selectedTutor->refresh();
    $otherTutor->refresh();

    expect($selectedTutor->unreadNotifications)->toHaveCount(1);
    expect($selectedTutor->unreadNotifications->first()->data['event'])->toBe('job-engagement-confirmed');
    expect($otherTutor->unreadNotifications)->toHaveCount(1);
    expect($otherTutor->unreadNotifications->first()->data['event'])->toBe('job-application-status-updated');
});

it('guardian cannot confirm engagement from non shortlisted application', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(8),
    ]);

    $application = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => TuitionJobApplication::STATUS_APPLIED,
    ]);

    $this->actingAs($guardian)
        ->from(route('guardian.jobs.applications.index', ['tuitionJob' => $job->id]))
        ->patch(route('guardian.jobs.applications.confirm', [
            'tuitionJob' => $job->id,
            'tuitionJobApplication' => $application->id,
        ]))
        ->assertRedirect(route('guardian.jobs.applications.index', ['tuitionJob' => $job->id], false))
        ->assertSessionHasErrors(['status']);

    $this->assertDatabaseHas('tuition_jobs', [
        'id' => $job->id,
        'status' => TuitionJob::STATUS_LIVE,
    ]);

    $this->assertDatabaseMissing('tuition_job_assignments', [
        'job_id' => $job->id,
    ]);
});

it('guardian second confirm attempt fails cleanly after first assignment exists', function () {
    $guardian = User::factory()->guardian()->create();
    $selectedTutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(8),
    ]);

    $application = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $selectedTutor->id,
        'status' => TuitionJobApplication::STATUS_SHORTLISTED,
    ]);

    $this->actingAs($guardian)
        ->patch(route('guardian.jobs.applications.confirm', [
            'tuitionJob' => $job->id,
            'tuitionJobApplication' => $application->id,
        ]))
        ->assertRedirect();

    $this->actingAs($guardian)
        ->from(route('guardian.jobs.applications.index', ['tuitionJob' => $job->id]))
        ->patch(route('guardian.jobs.applications.confirm', [
            'tuitionJob' => $job->id,
            'tuitionJobApplication' => $application->id,
        ]))
        ->assertRedirect(route('guardian.jobs.applications.index', ['tuitionJob' => $job->id], false))
        ->assertSessionHasErrors(['status']);

    expect(TuitionJobAssignment::query()->where('job_id', $job->id)->count())->toBe(1);
});

it('guardian confirm keeps already confirmed applications unchanged during bulk cancellation', function () {
    $guardian = User::factory()->guardian()->create();
    $selectedTutor = User::factory()->tutor()->create();
    $alreadyConfirmedTutor = User::factory()->tutor()->create();
    $openTutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(12),
    ]);

    $selectedApplication = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $selectedTutor->id,
        'status' => TuitionJobApplication::STATUS_SHORTLISTED,
    ]);

    $alreadyConfirmedApplication = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $alreadyConfirmedTutor->id,
        'status' => TuitionJobApplication::STATUS_CONFIRMED,
    ]);

    $openApplication = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $openTutor->id,
        'status' => TuitionJobApplication::STATUS_APPLIED,
    ]);

    $this->actingAs($guardian)
        ->patch(route('guardian.jobs.applications.confirm', [
            'tuitionJob' => $job->id,
            'tuitionJobApplication' => $selectedApplication->id,
        ]))
        ->assertRedirect();

    $this->assertDatabaseHas('tuition_job_applications', [
        'id' => $selectedApplication->id,
        'status' => TuitionJobApplication::STATUS_CONFIRMED,
    ]);

    $this->assertDatabaseHas('tuition_job_applications', [
        'id' => $alreadyConfirmedApplication->id,
        'status' => TuitionJobApplication::STATUS_CONFIRMED,
    ]);

    $this->assertDatabaseHas('tuition_job_applications', [
        'id' => $openApplication->id,
        'status' => TuitionJobApplication::STATUS_CANCELLED,
    ]);
});

it('guardian can view notifications and mark all as read', function () {
    $guardian = User::factory()->guardian()->create();

    $guardian->notify(new JobLifecycleNotification(
        event: 'job-application-submitted',
        title: 'New Application',
        message: 'A tutor has applied to your job.',
        url: '/guardian/jobs',
    ));

    $this->actingAs($guardian)
        ->get(route('guardian.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/Dashboard')
            ->where('notificationCounts.unread', 1));

    $this->actingAs($guardian)
        ->get(route('guardian.notifications.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/notifications/Index')
            ->has('items.data', 1)
            ->where('counts.unread', 1));

    $this->actingAs($guardian)
        ->patch(route('guardian.notifications.read-all'))
        ->assertRedirect();

    expect($guardian->fresh()->unreadNotifications)->toHaveCount(0);

    $this->actingAs($guardian)
        ->get(route('guardian.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/Dashboard')
            ->where('notificationCounts.unread', 0));
});
