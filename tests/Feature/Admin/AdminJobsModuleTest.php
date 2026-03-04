<?php

use App\Enums\ApplicationStatus;
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
use App\Models\TuitionJobApplication;
use App\Models\TuitionJobAssignment;
use App\Models\TuitionType;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

function createAdminJobFixture(User $admin, JobStatus $status = JobStatus::Pending): TuitionJob
{
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

    $job = TuitionJob::query()->create([
        'title' => fake()->unique()->sentence(4),
        'slug' => fake()->unique()->slug(4),
        'description' => fake()->paragraph(),
        'tuition_type_id' => $tuitionType->id,
        'category_id' => $category->id,
        'class_id' => $schoolClass->id,
        'country_id' => $country->id,
        'city_id' => $city->id,
        'area_id' => $area->id,
        'guardian_id' => $guardian->id,
        'location' => 'Test Location',
        'student_gender' => JobGender::Any,
        'tutor_gender' => JobGender::Any,
        'tuition_days' => ['sun', 'mon'],
        'days_per_week' => 2,
        'tuition_time' => '6 PM',
        'tuition_duration' => '3 months',
        'no_of_students' => 1,
        'salary_amount' => 10000,
        'salary_currency' => 'BDT',
        'salary_negotiable' => false,
        'status' => $status,
        'published_at' => $status === JobStatus::Live || $status === JobStatus::Confirmed || $status === JobStatus::Closed
            ? now()->subDay()
            : null,
        'expires_at' => now()->addDays(20),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
        'confirmed_by' => $status === JobStatus::Confirmed ? $admin->id : null,
        'confirmed_at' => $status === JobStatus::Confirmed ? now()->subHour() : null,
    ]);

    $job->subjects()->sync([$subject->id]);

    return $job;
}

it('admin can access new jobs routes with status presets', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    createAdminJobFixture($admin, JobStatus::Pending);
    createAdminJobFixture($admin, JobStatus::Live);
    createAdminJobFixture($admin, JobStatus::Confirmed);
    createAdminJobFixture($admin, JobStatus::Cancelled);
    createAdminJobFixture($admin, JobStatus::Live)->forceFill([
        'expires_at' => now()->subDay(),
    ])->save();

    $this->actingAs($admin)
        ->get(route('admin.jobs.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/jobs/Index')
            ->where('filters.preset_status', '')
            ->has('counts.total_count'));

    $this->actingAs($admin)
        ->get(route('admin.jobs.pending'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/jobs/Index')
            ->where('filters.preset_status', JobStatus::Pending->value));

    $this->actingAs($admin)
        ->get(route('admin.jobs.live'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/jobs/Index')
            ->where('filters.preset_status', JobStatus::Live->value));

    $this->actingAs($admin)
        ->get(route('admin.jobs.expired'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/jobs/Index')
            ->where('filters.preset_status', 'expired'));

    $this->actingAs($admin)
        ->get(route('admin.jobs.confirmed'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/jobs/Index')
            ->where('filters.preset_status', JobStatus::Confirmed->value));

    $this->actingAs($admin)
        ->get(route('admin.jobs.cancelled'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/jobs/Index')
            ->where('filters.preset_status', JobStatus::Cancelled->value));

    $jobWithApplications = createAdminJobFixture($admin, JobStatus::Live);
    $tutor = User::factory()->tutor()->create();

    TuitionJobApplication::factory()->create([
        'job_id' => $jobWithApplications->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Applied,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.jobs.applications', $jobWithApplications))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/jobs/Applications')
            ->where('job.id', $jobWithApplications->id)
            ->has('items.data', 1));

    $editJob = createAdminJobFixture($admin);

    $this->actingAs($admin)
        ->get(route('admin.jobs.create'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page->component('admin/jobs/Create'));

    $this->actingAs($admin)
        ->get(route('admin.jobs.edit', $editJob))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page->component('admin/jobs/Edit'));
});

it('enforces status transitions and recycle-bin actions on new admin jobs routes', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $job = createAdminJobFixture($admin, JobStatus::Pending);

    $this->actingAs($admin)
        ->patch(route('admin.jobs.approve', $job))
        ->assertRedirect();

    expect($job->fresh()->status)->toBe(JobStatus::Live);

    $this->actingAs($admin)
        ->patch(route('admin.jobs.status', $job), ['status' => JobStatus::Confirmed->value])
        ->assertSessionHasErrors('job');

    TuitionJobAssignment::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => User::factory()->tutor()->create()->id,
        'appointed_at' => null,
        'confirmed_at' => null,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.jobs.status', $job), ['status' => JobStatus::Confirmed->value])
        ->assertRedirect();

    expect($job->fresh()->status)->toBe(JobStatus::Confirmed);
    $assignment = TuitionJobAssignment::query()
        ->where('job_id', $job->id)
        ->firstOrFail();
    expect($assignment->appointed_at)->not->toBeNull();
    expect($assignment->confirmed_at)->not->toBeNull();
    expect($assignment->appointed_at?->equalTo($assignment->confirmed_at))->toBeTrue();

    $this->actingAs($admin)
        ->patch(route('admin.jobs.status', $job), ['status' => JobStatus::Cancelled->value])
        ->assertSessionHasErrors('job');

    $secondPending = createAdminJobFixture($admin, JobStatus::Pending);

    $this->actingAs($admin)
        ->patch(route('admin.jobs.status', $secondPending), ['status' => JobStatus::Confirmed->value])
        ->assertSessionHasErrors('job');

    $this->actingAs($admin)
        ->delete(route('admin.jobs.destroy', $job))
        ->assertRedirect();

    expect($job->fresh()->trashed())->toBeTrue();

    $this->actingAs($admin)
        ->patch(route('admin.jobs.restore', $job->id))
        ->assertRedirect();

    expect($job->fresh()->trashed())->toBeFalse();

    $this->actingAs($admin)
        ->delete(route('admin.jobs.destroy', $job))
        ->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('admin.jobs.force-delete', $job->id))
        ->assertRedirect();

    expect(TuitionJob::withTrashed()->find($job->id))->toBeNull();
});
