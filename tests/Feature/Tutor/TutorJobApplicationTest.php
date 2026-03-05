<?php

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Enums\TaxonomyStatus;
use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\TuitionJobAssignment;
use App\Models\TuitionType;
use App\Models\User;
use App\Notifications\JobLifecycleNotification;
use Inertia\Testing\AssertableInertia as Assert;

it('tutor can apply to a live job and see it in own applications list', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $category = Category::factory()->create(['status' => TaxonomyStatus::Active]);
    $schoolClass = SchoolClass::factory()->create([
        'category_id' => $category->id,
        'status' => TaxonomyStatus::Active,
    ]);
    $subject = Subject::factory()->create([
        'class_id' => $schoolClass->id,
        'status' => TaxonomyStatus::Active,
    ]);
    $country = Country::factory()->create(['status' => TaxonomyStatus::Active]);
    $city = City::factory()->create([
        'country_id' => $country->id,
        'status' => TaxonomyStatus::Active,
    ]);
    $area = Area::factory()->create([
        'city_id' => $city->id,
        'status' => TaxonomyStatus::Active,
    ]);
    $tuitionType = TuitionType::factory()->create(['status' => TaxonomyStatus::Active]);

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'tuition_type_id' => $tuitionType->id,
        'category_id' => $category->id,
        'class_id' => $schoolClass->id,
        'country_id' => $country->id,
        'city_id' => $city->id,
        'area_id' => $area->id,
        'expires_at' => now()->addDays(5),
    ]);
    $job->subjects()->sync([$subject->id]);

    $this->actingAs($tutor)
        ->post(route('tutor.jobs.apply', ['tuitionJob' => $job->slug]), [
            'cover_letter' => 'I have 5 years of tutoring experience.',
            'expected_salary_amount' => 15000,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tuition_job_applications', [
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Applied->value,
    ]);

    $guardian->refresh();
    expect($guardian->unreadNotifications)->toHaveCount(1);
    expect($guardian->unreadNotifications->first()->data['event'])->toBe('job-application-submitted');

    $this->actingAs($tutor)
        ->get(route('tutor.job-applications.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/job-applications/Index')
            ->has('items.data', 1));
});

it('tutor can access application preset routes with status filters', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(7),
    ]);

    TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Applied,
    ]);
    TuitionJobApplication::factory()->create([
        'job_id' => TuitionJob::factory()->live()->create([
            'guardian_id' => $guardian->id,
            'expires_at' => now()->addDays(7),
        ])->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Shortlisted,
    ]);
    TuitionJobApplication::factory()->create([
        'job_id' => TuitionJob::factory()->live()->create([
            'guardian_id' => $guardian->id,
            'expires_at' => now()->addDays(7),
        ])->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Appointed,
    ]);
    TuitionJobApplication::factory()->create([
        'job_id' => TuitionJob::factory()->create([
            'guardian_id' => $guardian->id,
            'status' => JobStatus::Confirmed,
            'published_at' => now()->subDay(),
            'confirmed_at' => now()->subHour(),
        ])->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Confirmed,
    ]);
    TuitionJobApplication::factory()->create([
        'job_id' => TuitionJob::factory()->live()->create([
            'guardian_id' => $guardian->id,
            'expires_at' => now()->addDays(7),
        ])->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Cancelled,
    ]);

    $this->actingAs($tutor)
        ->get(route('tutor.job-applications.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/job-applications/Index')
            ->where('filters.preset_status', '')
            ->where('statusCounts.all', 5)
            ->where('statusCounts.applied', 1)
            ->where('statusCounts.shortlisted', 1)
            ->where('statusCounts.appointed', 1)
            ->where('statusCounts.confirmed', 1)
            ->where('statusCounts.cancelled', 1));

    $this->actingAs($tutor)
        ->get(route('tutor.job-applications.applied'))
        ->assertRedirect(route('tutor.job-applications.index', absolute: false));

    $this->actingAs($tutor)
        ->get(route('tutor.job-applications.shortlisted'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/job-applications/Index')
            ->where('filters.preset_status', ApplicationStatus::Shortlisted->value));

    $this->actingAs($tutor)
        ->get(route('tutor.job-applications.appointed'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/job-applications/Index')
            ->where('filters.preset_status', ApplicationStatus::Appointed->value));

    $this->actingAs($tutor)
        ->get(route('tutor.job-applications.confirmed'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/job-applications/Index')
            ->where('filters.preset_status', ApplicationStatus::Confirmed->value));

    $this->actingAs($tutor)
        ->get(route('tutor.job-applications.cancelled'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/job-applications/Index')
            ->where('filters.preset_status', ApplicationStatus::Cancelled->value));
});

it('tutor cannot submit duplicate applied application', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(5),
    ]);

    TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Applied,
    ]);

    $this->actingAs($tutor)
        ->from(route('jobs.show', $job->slug))
        ->post(route('tutor.jobs.apply', ['tuitionJob' => $job->slug]), [])
        ->assertRedirect(route('jobs.show', $job->slug, absolute: false))
        ->assertSessionHasErrors(['job']);
});

it('tutor can cancel own application', function () {
    $tutor = User::factory()->tutor()->create();

    $application = TuitionJobApplication::factory()->create([
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Applied,
    ]);

    $this->actingAs($tutor)
        ->patch(route('tutor.job-applications.withdraw', ['tuitionJobApplication' => $application->id]))
        ->assertRedirect();

    $this->assertDatabaseHas('tuition_job_applications', [
        'id' => $application->id,
        'status' => ApplicationStatus::Cancelled->value,
    ]);

    $guardian = $application->tuitionJob->guardian->refresh();
    expect($guardian->unreadNotifications)->toHaveCount(1);
    expect($guardian->unreadNotifications->first()->data['event'])->toBe('job-application-cancelled');
});

it('tutor cannot apply to a confirmed job', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => JobStatus::Confirmed,
        'published_at' => now()->subHour(),
        'expires_at' => now()->addDays(5),
    ]);

    $this->actingAs($tutor)
        ->from(route('jobs.show', $job->slug))
        ->post(route('tutor.jobs.apply', ['tuitionJob' => $job->slug]), [])
        ->assertSessionHasErrors(['job']);
});

it('tutor can reapply by updating existing cancelled row', function () {
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
        'cancel_reason' => 'Cancelled by tutor.',
    ]);

    $this->actingAs($tutor)
        ->post(route('tutor.jobs.apply', ['tuitionJob' => $job->slug]), [
            'cover_letter' => 'Re-applying after schedule change.',
            'expected_salary_amount' => 18000,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tuition_job_applications', [
        'id' => $application->id,
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Applied->value,
        'cancel_reason' => null,
    ]);
});

it('tutor cannot apply when assignment exists for the job', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $selectedTutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(5),
    ]);

    TuitionJobAssignment::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $selectedTutor->id,
    ]);

    $this->actingAs($tutor)
        ->from(route('jobs.show', $job->slug))
        ->post(route('tutor.jobs.apply', ['tuitionJob' => $job->slug]), [])
        ->assertRedirect(route('jobs.show', $job->slug, absolute: false))
        ->assertSessionHasErrors(['job']);
});

it('tutor can view notifications and mark one as read', function () {
    $tutor = User::factory()->tutor()->create();

    $tutor->notify(new JobLifecycleNotification(
        event: 'job-application-status-updated',
        title: 'Application Updated',
        message: 'Your application was shortlisted.',
        url: '/tutor/job-applications',
    ));

    $notificationId = $tutor->unreadNotifications()->firstOrFail()->id;

    $this->actingAs($tutor)
        ->get(route('tutor.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/Dashboard')
            ->where('notificationCounts.unread', 1));

    $this->actingAs($tutor)
        ->get(route('tutor.notifications.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/notifications/Index')
            ->has('items.data', 1));

    $this->actingAs($tutor)
        ->patch(route('tutor.notifications.read', ['notificationId' => $notificationId]))
        ->assertRedirect();

    expect($tutor->fresh()->unreadNotifications)->toHaveCount(0);

    $this->actingAs($tutor)
        ->get(route('tutor.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/Dashboard')
            ->where('notificationCounts.unread', 0));
});
