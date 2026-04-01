<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\InvoiceType;
use App\Enums\JobGender;
use App\Enums\JobStatus;
use App\Enums\TaxonomyStatus;
use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\SchoolClass;
use App\Models\SiteSetting;
use App\Models\Subject;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\TuitionJobAssignment;
use App\Models\TuitionType;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guardian can submit a direct tutor request', function () {
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

    $this->actingAs($guardian)
        ->post(route('guardian.jobs.store'), [
            'title' => 'Requesting Special Tutor',
            'slug' => '',
            'description' => 'Direct request for my favorite tutor.',
            'tuition_type_id' => $tuitionType->id,
            'category_id' => $category->id,
            'class_id' => $schoolClass->id,
            'country_id' => $country->id,
            'city_id' => $city->id,
            'area_id' => $area->id,
            'subject_ids' => [$subject->id],
            'location' => 'Home address',
            'student_gender' => JobGender::Male->value,
            'tutor_gender' => JobGender::Any->value,
            'tuition_days' => ['mon', 'wed'],
            'tuition_time' => '4 PM',
            'tuition_duration' => '1 hour',
            'no_of_students' => 1,
            'salary_amount' => 12000,
            'salary_currency' => 'BDT',
            'salary_negotiable' => false,
            'requested_tutor_id' => $tutor->id,
        ])
        ->assertRedirect(route('guardian.jobs.index'));

    $job = TuitionJob::where('requested_tutor_id', $tutor->id)->firstOrFail();
    expect($job->title)->toBe('Requesting Special Tutor');
    expect($job->status)->toBe(JobStatus::Pending);

    // Verify auto-created application
    $application = TuitionJobApplication::where('job_id', $job->id)
        ->where('tutor_user_id', $tutor->id)
        ->firstOrFail();

    expect($application->status)->toBe(ApplicationStatus::Shortlisted);
    expect($application->cover_letter)->toContain('Direct request');
});

test('admin can settle a direct tutor request and platform fee is generated', function () {
    // Seed permissions
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    // Setup platform setting
    SiteSetting::factory()->create([
        'platform_service_fee_rate' => 0.5, // 50%
        'platform_owner_user_id' => User::factory()->admin()->create()->id,
    ]);

    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    // Create a job with direct request
    $job = TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'requested_tutor_id' => $tutor->id,
        'salary_amount' => 10000,
        'salary_currency' => 'BDT',
        'status' => JobStatus::Pending,
    ]);

    // Create the shortlisted application that should exist
    $application = TuitionJobApplication::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Shortlisted,
        'expected_salary_amount' => 10000,
        'salary_currency' => 'BDT',
    ]);

    // Admin settles the request
    $this->actingAs($admin)
        ->post(route('admin.jobs.confirm-settlement', $job->id))
        ->assertRedirect(route('admin.jobs.index'));

    // Verify job status changed to Confirmed
    $job->refresh();
    expect($job->status)->toBe(JobStatus::Confirmed);

    // Verify assignment created
    $assignment = TuitionJobAssignment::where('job_id', $job->id)
        ->where('tutor_user_id', $tutor->id)
        ->firstOrFail();

    expect((float) $assignment->service_fee_amount)->toBe(5000.0);
    expect((float) $assignment->service_fee_rate)->toBe(0.5);

    // Verify Invoice generation
    $invoice = Invoice::where('job_assignment_id', $assignment->id)
        ->where('type', InvoiceType::PlatformServiceFee)
        ->firstOrFail();

    expect((float) $invoice->amount)->toBe(5000.0);
    expect($invoice->status->value)->toBe('unpaid');
    expect($invoice->user_id)->toBe($tutor->id);
});

test('guardian can request a tutor for an existing live job', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    // Create an existing live job
    $job = TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => JobStatus::Live,
        'salary_amount' => 15000,
        'salary_currency' => 'BDT',
    ]);

    $this->actingAs($guardian)
        ->post(route('guardian.jobs.request-tutor', $job->id), [
            'tutor_id' => $tutor->id,
        ])
        ->assertSessionHas('status', 'Request sent to tutor successfully.');

    $job->refresh();
    expect($job->requested_tutor_id)->toBe($tutor->id);
    expect($job->requested_at)->not->toBeNull();

    // Verify application created
    $application = TuitionJobApplication::where('job_id', $job->id)
        ->where('tutor_user_id', $tutor->id)
        ->firstOrFail();

    expect($application->status)->toBe(ApplicationStatus::Shortlisted);
    expect((float) $application->expected_salary_amount)->toBe(15000.0);
});

test('guardian cannot request a tutor for a non-live job', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    // Create a pending job
    $job = TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => JobStatus::Pending,
    ]);

    $this->actingAs($guardian)
        ->post(route('guardian.jobs.request-tutor', $job->id), [
            'tutor_id' => $tutor->id,
        ])
        ->assertSessionHas('error', 'Only live jobs can accept tutor requests.');

    $job->refresh();
    expect($job->requested_tutor_id)->toBeNull();
});

test('guardian cannot request a tutor for someone else\'s job', function () {
    $guardian = User::factory()->guardian()->create();
    $otherGuardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $job = TuitionJob::factory()->create([
        'guardian_id' => $otherGuardian->id,
        'status' => JobStatus::Live,
    ]);

    $this->actingAs($guardian)
        ->post(route('guardian.jobs.request-tutor', $job->id), [
            'tutor_id' => $tutor->id,
        ])
        ->assertStatus(403);
});
