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
use Inertia\Testing\AssertableInertia as Assert;

function createAdminJobFixture(User $admin, string $status = TuitionJob::STATUS_PENDING): TuitionJob
{
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
        'student_gender' => TuitionJob::GENDER_ANY,
        'tutor_gender' => TuitionJob::GENDER_ANY,
        'tuition_days' => ['sun', 'mon'],
        'days_per_week' => 2,
        'tuition_time' => '6 PM',
        'tuition_duration' => '3 months',
        'no_of_students' => 1,
        'salary_amount' => 10000,
        'salary_currency' => 'BDT',
        'salary_negotiable' => false,
        'status' => $status,
        'published_at' => $status === TuitionJob::STATUS_LIVE || $status === TuitionJob::STATUS_CONFIRMED || $status === TuitionJob::STATUS_CLOSED
            ? now()->subDay()
            : null,
        'expires_at' => now()->addDays(20),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
        'confirmed_by' => $status === TuitionJob::STATUS_CONFIRMED ? $admin->id : null,
        'confirmed_at' => $status === TuitionJob::STATUS_CONFIRMED ? now()->subHour() : null,
    ]);

    $job->subjects()->sync([$subject->id]);

    return $job;
}

it('admin can access new jobs routes with status presets', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    createAdminJobFixture($admin, TuitionJob::STATUS_PENDING);
    createAdminJobFixture($admin, TuitionJob::STATUS_LIVE);
    createAdminJobFixture($admin, TuitionJob::STATUS_CONFIRMED);
    createAdminJobFixture($admin, TuitionJob::STATUS_CANCELLED);

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
            ->where('filters.preset_status', TuitionJob::STATUS_PENDING));

    $this->actingAs($admin)
        ->get(route('admin.jobs.live'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/jobs/Index')
            ->where('filters.preset_status', TuitionJob::STATUS_LIVE));

    $this->actingAs($admin)
        ->get(route('admin.jobs.confirmed'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/jobs/Index')
            ->where('filters.preset_status', TuitionJob::STATUS_CONFIRMED));

    $this->actingAs($admin)
        ->get(route('admin.jobs.cancelled'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/jobs/Index')
            ->where('filters.preset_status', TuitionJob::STATUS_CANCELLED));

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

    $job = createAdminJobFixture($admin, TuitionJob::STATUS_PENDING);

    $this->actingAs($admin)
        ->patch(route('admin.jobs.approve', $job))
        ->assertRedirect();

    expect($job->fresh()->status)->toBe(TuitionJob::STATUS_LIVE);

    $this->actingAs($admin)
        ->patch(route('admin.jobs.status', $job), ['status' => TuitionJob::STATUS_CONFIRMED])
        ->assertRedirect();

    expect($job->fresh()->status)->toBe(TuitionJob::STATUS_CONFIRMED);

    $this->actingAs($admin)
        ->patch(route('admin.jobs.status', $job), ['status' => TuitionJob::STATUS_CANCELLED])
        ->assertSessionHasErrors('job');

    $secondPending = createAdminJobFixture($admin, TuitionJob::STATUS_PENDING);

    $this->actingAs($admin)
        ->patch(route('admin.jobs.status', $secondPending), ['status' => TuitionJob::STATUS_CONFIRMED])
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
