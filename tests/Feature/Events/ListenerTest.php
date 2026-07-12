<?php

use App\Enums\ApplicationStatus;
use App\Events\ApplicationStatusUpdated;
use App\Events\ApplicationSubmitted;
use App\Events\ApplicationWithdrawn;
use App\Events\HireConfirmed;
use App\Listeners\NotifyGuardianOfApplication;
use App\Listeners\NotifyGuardianOfWithdrawal;
use App\Listeners\NotifyTutorOfStatusChange;
use App\Listeners\NotifyTutorsOfHireOutcome;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\User;
use App\Notifications\JobLifecycleNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

it('NotifyGuardianOfApplication sends notification on new application', function () {
    Notification::fake();

    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(5),
    ]);

    $application = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Applied,
    ]);

    $event = new ApplicationSubmitted($job, $application, $tutor, false);
    $listener = new NotifyGuardianOfApplication;
    $listener->handle($event);

    Notification::assertSentTo($guardian, JobLifecycleNotification::class, function (JobLifecycleNotification $notification) {
        return $notification->event === 'job-application-submitted'
            && $notification->title === 'New Job Application';
    });
});

it('NotifyGuardianOfApplication sends resubmitted notification', function () {
    Notification::fake();

    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(5),
    ]);

    $application = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Applied,
    ]);

    $event = new ApplicationSubmitted($job, $application, $tutor, true);
    $listener = new NotifyGuardianOfApplication;
    $listener->handle($event);

    Notification::assertSentTo($guardian, JobLifecycleNotification::class, function (JobLifecycleNotification $notification) {
        return $notification->event === 'job-application-resubmitted'
            && $notification->title === 'Application Resubmitted';
    });
});

it('NotifyGuardianOfWithdrawal sends cancellation notification', function () {
    Notification::fake();

    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(5),
    ]);

    $application = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Cancelled,
    ]);

    $event = new ApplicationWithdrawn($job, $application, $tutor);
    $listener = new NotifyGuardianOfWithdrawal;
    $listener->handle($event);

    Notification::assertSentTo($guardian, JobLifecycleNotification::class, function (JobLifecycleNotification $notification) {
        return $notification->event === 'job-application-cancelled'
            && $notification->title === 'Application Cancelled';
    });
});

it('NotifyTutorOfStatusChange sends shortlisted notification', function () {
    Notification::fake();

    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(5),
    ]);

    $application = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Shortlisted,
    ]);

    $event = new ApplicationStatusUpdated($job, $application, ApplicationStatus::Shortlisted->value);
    $listener = new NotifyTutorOfStatusChange;
    $listener->handle($event);

    Notification::assertSentTo($tutor, JobLifecycleNotification::class, function (JobLifecycleNotification $notification) {
        return $notification->event === 'job-application-status-updated'
            && $notification->title === 'Application Shortlisted';
    });
});

it('NotifyTutorOfStatusChange sends cancelled notification', function () {
    Notification::fake();

    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(5),
    ]);

    $application = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Cancelled,
    ]);

    $event = new ApplicationStatusUpdated($job, $application, ApplicationStatus::Cancelled->value);
    $listener = new NotifyTutorOfStatusChange;
    $listener->handle($event);

    Notification::assertSentTo($tutor, JobLifecycleNotification::class, function (JobLifecycleNotification $notification) {
        return $notification->event === 'job-application-status-updated'
            && $notification->title === 'Application Cancelled';
    });
});

it('NotifyTutorsOfHireOutcome notifies selected tutor', function () {
    Notification::fake();

    $selectedTutor = User::factory()->tutor()->create();
    $guardian = User::factory()->guardian()->create();

    $job = TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
    ]);

    $application = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $selectedTutor->id,
        'status' => ApplicationStatus::Confirmed,
    ]);

    $event = new HireConfirmed($job, $application, $selectedTutor->id, []);
    $listener = new NotifyTutorsOfHireOutcome;
    $listener->handle($event);

    Notification::assertSentTo($selectedTutor, JobLifecycleNotification::class, function (JobLifecycleNotification $notification) {
        return $notification->event === 'job-engagement-confirmed'
            && $notification->title === 'Job Hire Confirmed';
    });
});

it('NotifyTutorsOfHireOutcome notifies rejected tutors', function () {
    Notification::fake();

    $selectedTutor = User::factory()->tutor()->create();
    $rejectedTutor1 = User::factory()->tutor()->create();
    $rejectedTutor2 = User::factory()->tutor()->create();
    $guardian = User::factory()->guardian()->create();

    $job = TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
    ]);

    $application = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $selectedTutor->id,
        'status' => ApplicationStatus::Confirmed,
    ]);

    $event = new HireConfirmed(
        $job,
        $application,
        $selectedTutor->id,
        [$rejectedTutor1->id, $rejectedTutor2->id],
    );
    $listener = new NotifyTutorsOfHireOutcome;
    $listener->handle($event);

    Notification::assertSentTo($selectedTutor, JobLifecycleNotification::class);

    Notification::assertSentTo($rejectedTutor1, JobLifecycleNotification::class, function (JobLifecycleNotification $notification) {
        return $notification->event === 'job-application-status-updated'
            && $notification->title === 'Application Cancelled';
    });

    Notification::assertSentTo($rejectedTutor2, JobLifecycleNotification::class, function (JobLifecycleNotification $notification) {
        return $notification->event === 'job-application-status-updated';
    });
});

it('all listeners implement ShouldQueue', function () {
    expect(NotifyGuardianOfApplication::class)->toImplement(ShouldQueue::class);
    expect(NotifyGuardianOfWithdrawal::class)->toImplement(ShouldQueue::class);
    expect(NotifyTutorOfStatusChange::class)->toImplement(ShouldQueue::class);
    expect(NotifyTutorsOfHireOutcome::class)->toImplement(ShouldQueue::class);
});
