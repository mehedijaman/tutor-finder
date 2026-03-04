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
use Inertia\Testing\AssertableInertia as Assert;

it('shows only live published non-expired non-trashed jobs on public board', function () {
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

    $live = TuitionJob::factory()->create([
        'title' => 'Visible Public Job',
        'status' => JobStatus::Live,
        'published_at' => now()->subHour(),
        'expires_at' => now()->addDays(3),
        'category_id' => $category->id,
        'class_id' => $schoolClass->id,
        'country_id' => $country->id,
        'city_id' => $city->id,
        'area_id' => $area->id,
        'tuition_type_id' => $tuitionType->id,
    ]);
    $live->subjects()->sync([$subject->id]);

    TuitionJob::factory()->create([
        'status' => JobStatus::Pending,
        'published_at' => now()->subHour(),
        'category_id' => $category->id,
        'class_id' => $schoolClass->id,
        'country_id' => $country->id,
        'city_id' => $city->id,
        'area_id' => $area->id,
        'tuition_type_id' => $tuitionType->id,
    ]);

    TuitionJob::factory()->create([
        'status' => JobStatus::Live,
        'published_at' => now()->addHour(),
        'category_id' => $category->id,
        'class_id' => $schoolClass->id,
        'country_id' => $country->id,
        'city_id' => $city->id,
        'area_id' => $area->id,
        'tuition_type_id' => $tuitionType->id,
    ]);

    TuitionJob::factory()->create([
        'status' => JobStatus::Live,
        'published_at' => now()->subHour(),
        'expires_at' => now()->subMinute(),
        'category_id' => $category->id,
        'class_id' => $schoolClass->id,
        'country_id' => $country->id,
        'city_id' => $city->id,
        'area_id' => $area->id,
        'tuition_type_id' => $tuitionType->id,
    ]);

    $trashed = TuitionJob::factory()->create([
        'status' => JobStatus::Live,
        'published_at' => now()->subHour(),
        'category_id' => $category->id,
        'class_id' => $schoolClass->id,
        'country_id' => $country->id,
        'city_id' => $city->id,
        'area_id' => $area->id,
        'tuition_type_id' => $tuitionType->id,
    ]);
    $trashed->delete();

    $this->get(route('jobs'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('JobBoard')
            ->has('jobs.data', 1)
            ->where('jobs.data.0.slug', $live->slug));
});

it('applies public board filters', function () {
    $categoryA = Category::factory()->create(['status' => TaxonomyStatus::Active, 'name' => 'Category A', 'slug' => 'category-a']);
    $categoryB = Category::factory()->create(['status' => TaxonomyStatus::Active, 'name' => 'Category B', 'slug' => 'category-b']);

    $schoolClassA = SchoolClass::factory()->create([
        'category_id' => $categoryA->id,
        'status' => TaxonomyStatus::Active,
    ]);

    $schoolClassB = SchoolClass::factory()->create([
        'category_id' => $categoryB->id,
        'status' => TaxonomyStatus::Active,
    ]);

    $subjectA = Subject::factory()->create([
        'class_id' => $schoolClassA->id,
        'status' => TaxonomyStatus::Active,
    ]);

    $subjectB = Subject::factory()->create([
        'class_id' => $schoolClassB->id,
        'status' => TaxonomyStatus::Active,
    ]);

    $country = Country::factory()->create(['status' => TaxonomyStatus::Active]);
    $cityA = City::factory()->create([
        'country_id' => $country->id,
        'status' => TaxonomyStatus::Active,
    ]);
    $cityB = City::factory()->create([
        'country_id' => $country->id,
        'status' => TaxonomyStatus::Active,
    ]);

    $tuitionType = TuitionType::factory()->create(['status' => TaxonomyStatus::Active]);

    $match = TuitionJob::factory()->create([
        'title' => 'Physics Match Job',
        'status' => JobStatus::Live,
        'published_at' => now()->subHour(),
        'expires_at' => now()->addDays(5),
        'category_id' => $categoryA->id,
        'class_id' => $schoolClassA->id,
        'country_id' => $country->id,
        'city_id' => $cityA->id,
        'area_id' => null,
        'tuition_type_id' => $tuitionType->id,
    ]);
    $match->subjects()->sync([$subjectA->id]);

    $other = TuitionJob::factory()->create([
        'title' => 'Other Job',
        'status' => JobStatus::Live,
        'published_at' => now()->subHour(),
        'expires_at' => now()->addDays(5),
        'category_id' => $categoryB->id,
        'class_id' => $schoolClassB->id,
        'country_id' => $country->id,
        'city_id' => $cityB->id,
        'area_id' => null,
        'tuition_type_id' => $tuitionType->id,
    ]);
    $other->subjects()->sync([$subjectB->id]);

    $this->get(route('jobs', [
        'category' => 'category-a',
        'subject_id' => $subjectA->id,
        'city_id' => $cityA->id,
        'q' => 'Physics',
    ]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('JobBoard')
            ->has('jobs.data', 1)
            ->where('jobs.data.0.slug', $match->slug));
});

it('shows job details for live public jobs and hides non public jobs', function () {
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
    $tuitionType = TuitionType::factory()->create(['status' => TaxonomyStatus::Active]);

    $live = TuitionJob::factory()->create([
        'title' => 'Public Detail Job',
        'status' => JobStatus::Live,
        'published_at' => now()->subHour(),
        'expires_at' => now()->addDays(2),
        'category_id' => $category->id,
        'class_id' => $schoolClass->id,
        'country_id' => $country->id,
        'city_id' => $city->id,
        'tuition_type_id' => $tuitionType->id,
    ]);
    $live->subjects()->sync([$subject->id]);

    $pending = TuitionJob::factory()->create([
        'status' => JobStatus::Pending,
        'published_at' => now()->subHour(),
        'category_id' => $category->id,
        'class_id' => $schoolClass->id,
        'country_id' => $country->id,
        'city_id' => $city->id,
        'tuition_type_id' => $tuitionType->id,
    ]);

    $this->get(route('jobs.show', $live->slug))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('JobShow')
            ->where('job.slug', $live->slug));

    $this->get(route('jobs.show', $pending->slug))->assertNotFound();
});

it('allows tutor re-apply on job show when existing application is cancelled', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $live = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'published_at' => now()->subHour(),
        'expires_at' => now()->addDays(2),
    ]);

    TuitionJobApplication::factory()->create([
        'job_id' => $live->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Cancelled,
        'cancel_reason' => 'Previous conflict resolved.',
    ]);

    $this->actingAs($tutor)
        ->get(route('jobs.show', $live->slug))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('JobShow')
            ->where('canApply', true)
            ->where('application.status', ApplicationStatus::Cancelled->value));
});

it('blocks apply on job show when assignment already exists', function () {
    $guardian = User::factory()->guardian()->create();
    $selectedTutor = User::factory()->tutor()->create();
    $tutor = User::factory()->tutor()->create();
    $live = TuitionJob::factory()->live()->create([
        'guardian_id' => $guardian->id,
        'published_at' => now()->subHour(),
        'expires_at' => now()->addDays(2),
    ]);

    TuitionJobAssignment::factory()->create([
        'job_id' => $live->id,
        'tutor_user_id' => $selectedTutor->id,
    ]);

    $this->actingAs($tutor)
        ->get(route('jobs.show', $live->slug))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('JobShow')
            ->where('canApply', false)
            ->where('application', null));
});
