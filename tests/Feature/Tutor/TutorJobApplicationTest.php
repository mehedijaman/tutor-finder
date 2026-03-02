<?php

use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\TuitionType;
use App\Models\User;
use App\Notifications\JobLifecycleNotification;
use Inertia\Testing\AssertableInertia as Assert;

it('tutor can apply to a live job and see it in own applications list', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $category = Category::factory()->create(['status' => Category::STATUS_ACTIVE]);
    $schoolClass = SchoolClass::factory()->create([
        'category_id' => $category->id,
        'status' => SchoolClass::STATUS_ACTIVE,
    ]);
    $subject = Subject::factory()->create([
        'class_id' => $schoolClass->id,
        'status' => Subject::STATUS_ACTIVE,
    ]);
    $country = Country::factory()->create(['status' => Country::STATUS_ACTIVE]);
    $city = City::factory()->create([
        'country_id' => $country->id,
        'status' => City::STATUS_ACTIVE,
    ]);
    $area = Area::factory()->create([
        'city_id' => $city->id,
        'status' => Area::STATUS_ACTIVE,
    ]);
    $tuitionType = TuitionType::factory()->create(['status' => TuitionType::STATUS_ACTIVE]);

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
            'expected_salary' => 15000,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tuition_job_applications', [
        'tuition_job_id' => $job->id,
        'tutor_id' => $tutor->id,
        'status' => TuitionJobApplication::STATUS_PENDING,
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

it('tutor cannot submit duplicate pending application', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'expires_at' => now()->addDays(5),
    ]);

    TuitionJobApplication::factory()->create([
        'tuition_job_id' => $job->id,
        'tutor_id' => $tutor->id,
        'status' => TuitionJobApplication::STATUS_PENDING,
    ]);

    $this->actingAs($tutor)
        ->from(route('jobs.show', $job->slug))
        ->post(route('tutor.jobs.apply', ['tuitionJob' => $job->slug]), [])
        ->assertRedirect(route('jobs.show', $job->slug, absolute: false))
        ->assertSessionHasErrors(['job']);
});

it('tutor can withdraw own application', function () {
    $tutor = User::factory()->tutor()->create();

    $application = TuitionJobApplication::factory()->create([
        'tutor_id' => $tutor->id,
        'status' => TuitionJobApplication::STATUS_PENDING,
    ]);

    $this->actingAs($tutor)
        ->patch(route('tutor.job-applications.withdraw', ['tuitionJobApplication' => $application->id]))
        ->assertRedirect();

    $this->assertDatabaseHas('tuition_job_applications', [
        'id' => $application->id,
        'status' => TuitionJobApplication::STATUS_WITHDRAWN,
    ]);

    $guardian = $application->tuitionJob->guardian->refresh();
    expect($guardian->unreadNotifications)->toHaveCount(1);
    expect($guardian->unreadNotifications->first()->data['event'])->toBe('job-application-withdrawn');
});

it('tutor cannot apply to a confirmed job', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => TuitionJob::STATUS_CONFIRMED,
        'published_at' => now()->subHour(),
        'expires_at' => now()->addDays(5),
    ]);

    $this->actingAs($tutor)
        ->from(route('jobs.show', $job->slug))
        ->post(route('tutor.jobs.apply', ['tuitionJob' => $job->slug]), [])
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
