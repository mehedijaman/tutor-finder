<?php

use App\Enums\JobGender;
use App\Enums\JobStatus;
use App\Enums\TaxonomyStatus;
use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionJob;
use App\Models\TuitionType;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('guardian can view jobs pages and create pending job', function () {
    $guardian = User::factory()->guardian()->create();

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

    $this->actingAs($guardian)
        ->get(route('guardian.jobs.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page->component('guardian/jobs/Index'));

    $this->actingAs($guardian)
        ->get(route('guardian.jobs.create'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page->component('guardian/jobs/Create'));

    $this->actingAs($guardian)
        ->post(route('guardian.jobs.store'), [
            'title' => 'Need Maths Tutor for Class 9',
            'slug' => '',
            'description' => 'Need experienced tutor for evening classes at home.',
            'tuition_type_id' => $tuitionType->id,
            'category_id' => $category->id,
            'class_id' => $schoolClass->id,
            'country_id' => $country->id,
            'city_id' => $city->id,
            'area_id' => $area->id,
            'subject_ids' => [$subject->id],
            'location' => 'Near Lake Road',
            'student_gender' => JobGender::Any->value,
            'tutor_gender' => JobGender::Female->value,
            'tuition_days' => ['sun', 'tue', 'thu'],
            'tuition_time' => '5 PM - 7 PM',
            'tuition_duration' => '3 months',
            'no_of_students' => 1,
            'salary_amount' => 10000,
            'salary_currency' => 'BDT',
            'salary_negotiable' => false,
            'expires_at' => now()->addDays(10)->toDateTimeString(),
        ])
        ->assertRedirect(route('guardian.jobs.index', absolute: false));

    $job = TuitionJob::query()->firstOrFail();

    expect($job->guardian_id)->toBe($guardian->id);
    expect($job->status)->toBe(JobStatus::Pending);
    expect($job->days_per_week)->toBe(3);
    expect($job->slug)->toBe('need-maths-tutor-for-class-9');

    $this->assertDatabaseHas('tuition_job_subjects', [
        'job_id' => $job->id,
        'subject_id' => $subject->id,
    ]);
});

it('guardian can access jobs preset routes with status filters', function () {
    $guardian = User::factory()->guardian()->create();

    TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => JobStatus::Pending,
    ]);
    TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => JobStatus::Confirmed,
        'published_at' => now()->subDay(),
        'confirmed_at' => now()->subHour(),
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => JobStatus::Cancelled,
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => JobStatus::Closed,
        'published_at' => now()->subDay(),
    ]);

    $this->actingAs($guardian)
        ->get(route('guardian.jobs.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/jobs/Index')
            ->where('filters.preset_status', ''));

    $this->actingAs($guardian)
        ->get(route('guardian.jobs.pending'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/jobs/Index')
            ->where('filters.preset_status', JobStatus::Pending->value));

    $this->actingAs($guardian)
        ->get(route('guardian.jobs.live'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/jobs/Index')
            ->where('filters.preset_status', JobStatus::Live->value));

    $this->actingAs($guardian)
        ->get(route('guardian.jobs.confirmed'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/jobs/Index')
            ->where('filters.preset_status', JobStatus::Confirmed->value));

    $this->actingAs($guardian)
        ->get(route('guardian.jobs.cancelled'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/jobs/Index')
            ->where('filters.preset_status', JobStatus::Cancelled->value));

    $this->actingAs($guardian)
        ->get(route('guardian.jobs.closed'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/jobs/Index')
            ->where('filters.preset_status', JobStatus::Closed->value));
});
