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
use Database\Seeders\AdminRolesAndPermissionsSeeder;

it('admin can create approve cancel close and recycle jobs', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

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

    $payload = [
        'title' => 'English Tutor Needed',
        'slug' => '',
        'description' => 'Looking for skilled English tutor.',
        'guardian_id' => $guardian->id,
        'tuition_type_id' => $tuitionType->id,
        'category_id' => $category->id,
        'class_id' => $schoolClass->id,
        'country_id' => $country->id,
        'city_id' => $city->id,
        'area_id' => $area->id,
        'subject_ids' => [$subject->id],
        'location' => 'Dhanmondi',
        'student_gender' => TuitionJob::GENDER_ANY,
        'tutor_gender' => TuitionJob::GENDER_ANY,
        'tuition_days' => ['sun', 'mon'],
        'tuition_time' => '6 PM',
        'tuition_duration' => '4 months',
        'no_of_students' => 1,
        'salary_amount' => 8000,
        'salary_currency' => 'BDT',
        'salary_negotiable' => true,
        'status' => TuitionJob::STATUS_PENDING,
        'published_at' => null,
        'expires_at' => now()->addDays(14)->toDateTimeString(),
    ];

    $this->actingAs($admin)
        ->post(route('admin.tuition.jobs.store'), $payload)
        ->assertRedirect(route('admin.tuition.jobs.index', absolute: false));

    $job = TuitionJob::query()->firstOrFail();

    expect($job->status)->toBe(TuitionJob::STATUS_PENDING);

    $this->actingAs($admin)
        ->patch(route('admin.tuition.jobs.approve', $job))
        ->assertRedirect();

    expect($job->fresh()->status)->toBe(TuitionJob::STATUS_LIVE);

    $this->actingAs($admin)
        ->patch(route('admin.tuition.jobs.approve', $job))
        ->assertSessionHasErrors('job');

    $this->actingAs($admin)
        ->patch(route('admin.tuition.jobs.close', $job))
        ->assertRedirect();

    expect($job->fresh()->status)->toBe(TuitionJob::STATUS_CLOSED);

    $this->actingAs($admin)
        ->patch(route('admin.tuition.jobs.cancel', $job), ['reason' => 'No longer needed'])
        ->assertSessionHasErrors('job');

    $this->actingAs($admin)
        ->delete(route('admin.tuition.jobs.destroy', $job))
        ->assertRedirect();

    expect($job->fresh()->trashed())->toBeTrue();

    $this->actingAs($admin)
        ->patch(route('admin.tuition.jobs.restore', $job->id))
        ->assertRedirect();

    expect($job->fresh()->trashed())->toBeFalse();

    $this->actingAs($admin)
        ->delete(route('admin.tuition.jobs.destroy', $job))
        ->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('admin.tuition.jobs.force-delete', $job->id))
        ->assertRedirect();

    expect(TuitionJob::withTrashed()->find($job->id))->toBeNull();

    $this->assertDatabaseMissing('tuition_job_subjects', [
        'job_id' => $job->id,
        'subject_id' => $subject->id,
    ]);
});

it('enforces slug uniqueness across soft deleted rows and blocks subject force delete when attached', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

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

    $payload = [
        'title' => 'Physics Tutor Wanted',
        'slug' => '',
        'description' => 'Physics support needed.',
        'guardian_id' => $guardian->id,
        'tuition_type_id' => $tuitionType->id,
        'category_id' => $category->id,
        'class_id' => $schoolClass->id,
        'country_id' => $country->id,
        'city_id' => $city->id,
        'area_id' => $area->id,
        'subject_ids' => [$subject->id],
        'location' => '',
        'student_gender' => TuitionJob::GENDER_ANY,
        'tutor_gender' => TuitionJob::GENDER_ANY,
        'tuition_days' => ['sun', 'wed'],
        'tuition_time' => '',
        'tuition_duration' => '',
        'no_of_students' => null,
        'salary_amount' => 7000,
        'salary_currency' => 'BDT',
        'salary_negotiable' => false,
        'status' => TuitionJob::STATUS_PENDING,
        'published_at' => null,
        'expires_at' => now()->addDays(20)->toDateTimeString(),
    ];

    $this->actingAs($admin)
        ->post(route('admin.tuition.jobs.store'), $payload)
        ->assertRedirect();

    $first = TuitionJob::query()->latest('id')->firstOrFail();

    $this->actingAs($admin)
        ->delete(route('admin.tuition.jobs.destroy', $first))
        ->assertRedirect();

    $this->actingAs($admin)
        ->post(route('admin.tuition.jobs.store'), $payload)
        ->assertRedirect();

    $slugs = TuitionJob::withTrashed()
        ->orderBy('id')
        ->pluck('slug')
        ->all();

    expect($slugs)->toContain('physics-tutor-wanted');
    expect($slugs)->toContain('physics-tutor-wanted-2');

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.subjects.destroy', $subject))
        ->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('admin.tuition.taxonomies.subjects.force-delete', $subject->id))
        ->assertSessionHasErrors('subject');
});
