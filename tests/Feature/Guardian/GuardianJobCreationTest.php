<?php

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
            'student_gender' => TuitionJob::GENDER_ANY,
            'tutor_gender' => TuitionJob::GENDER_FEMALE,
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
    expect($job->status)->toBe(TuitionJob::STATUS_PENDING);
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
        'status' => TuitionJob::STATUS_PENDING,
    ]);
    TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => TuitionJob::STATUS_CONFIRMED,
        'published_at' => now()->subDay(),
        'confirmed_at' => now()->subHour(),
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => TuitionJob::STATUS_CANCELLED,
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => TuitionJob::STATUS_CLOSED,
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
            ->where('filters.preset_status', TuitionJob::STATUS_PENDING));

    $this->actingAs($guardian)
        ->get(route('guardian.jobs.live'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/jobs/Index')
            ->where('filters.preset_status', TuitionJob::STATUS_LIVE));

    $this->actingAs($guardian)
        ->get(route('guardian.jobs.confirmed'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/jobs/Index')
            ->where('filters.preset_status', TuitionJob::STATUS_CONFIRMED));

    $this->actingAs($guardian)
        ->get(route('guardian.jobs.cancelled'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/jobs/Index')
            ->where('filters.preset_status', TuitionJob::STATUS_CANCELLED));

    $this->actingAs($guardian)
        ->get(route('guardian.jobs.closed'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/jobs/Index')
            ->where('filters.preset_status', TuitionJob::STATUS_CLOSED));
});
