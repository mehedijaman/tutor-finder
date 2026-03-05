<?php

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Models\Notice;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\User;

it('tutor dashboard shows active notices for tutors', function () {
    $tutor = User::factory()->tutor()->create();

    Notice::factory()->forTutors()->create([
        'title' => 'Tutor Notice',
        'is_active' => true,
        'expires_at' => now()->addDay(),
        'published_at' => now()->subHour(),
    ]);
    Notice::factory()->forGuardians()->create([
        'title' => 'Guardian Notice',
        'is_active' => true,
        'expires_at' => now()->addDay(),
    ]);
    Notice::factory()->forAll()->create([
        'title' => 'Both Notice',
        'is_active' => true,
        'expires_at' => now()->addDay(),
        'published_at' => now(),
    ]);

    $response = $this->actingAs($tutor)->get('/tutor/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('notices', 2)
        ->where('notices.0.title', 'Both Notice')
        ->where('notices.1.title', 'Tutor Notice')
    );
});

it('guardian dashboard shows active notices for guardians', function () {
    $guardian = User::factory()->guardian()->create();

    Notice::factory()->forTutors()->create([
        'title' => 'Tutor Notice',
        'is_active' => true,
        'expires_at' => now()->addDay(),
    ]);
    Notice::factory()->forGuardians()->create([
        'title' => 'Guardian Notice',
        'is_active' => true,
        'expires_at' => now()->addDay(),
        'published_at' => now()->subHour(),
    ]);
    Notice::factory()->forAll()->create([
        'title' => 'Both Notice',
        'is_active' => true,
        'expires_at' => now()->addDay(),
        'published_at' => now(),
    ]);

    $response = $this->actingAs($guardian)->get('/guardian/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('notices', 2)
        ->where('notices.0.title', 'Both Notice')
        ->where('notices.1.title', 'Guardian Notice')
    );
});

it('dashboard does not show expired notices', function () {
    $tutor = User::factory()->tutor()->create();

    Notice::factory()->forTutors()->expired()->create([
        'title' => 'Expired Notice',
    ]);
    Notice::factory()->forTutors()->create([
        'title' => 'Active Notice',
        'is_active' => true,
        'expires_at' => now()->addDay(),
    ]);

    $response = $this->actingAs($tutor)->get('/tutor/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('notices', 1)
        ->where('notices.0.title', 'Active Notice')
    );
});

it('dashboard does not show inactive notices', function () {
    $tutor = User::factory()->tutor()->create();

    Notice::factory()->forTutors()->inactive()->create([
        'title' => 'Inactive Notice',
    ]);
    Notice::factory()->forTutors()->create([
        'title' => 'Active Notice',
        'is_active' => true,
        'expires_at' => now()->addDay(),
    ]);

    $response = $this->actingAs($tutor)->get('/tutor/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('notices', 1)
        ->where('notices.0.title', 'Active Notice')
    );
});

it('dashboard limits notices to 10', function () {
    $tutor = User::factory()->tutor()->create();

    Notice::factory()->forTutors()->count(15)->create([
        'is_active' => true,
        'expires_at' => now()->addDay(),
    ]);

    $response = $this->actingAs($tutor)->get('/tutor/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('notices', 10));
});

it('tutor dashboard shows application stats counts', function () {
    $tutor = User::factory()->tutor()->create();
    $otherTutor = User::factory()->tutor()->create();

    TuitionJobApplication::factory()->count(2)->create([
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Applied,
    ]);
    TuitionJobApplication::factory()->create([
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Shortlisted,
    ]);
    TuitionJobApplication::factory()->create([
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Appointed,
    ]);
    TuitionJobApplication::factory()->create([
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Confirmed,
    ]);
    TuitionJobApplication::factory()->create([
        'tutor_user_id' => $tutor->id,
        'status' => ApplicationStatus::Cancelled,
    ]);

    TuitionJobApplication::factory()->create([
        'tutor_user_id' => $otherTutor->id,
        'status' => ApplicationStatus::Applied,
    ]);

    $response = $this->actingAs($tutor)->get('/tutor/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('applicationStats.applied', 2)
        ->where('applicationStats.shortlisted', 1)
        ->where('applicationStats.appointed', 1)
        ->where('applicationStats.confirmed', 1)
        ->where('applicationStats.cancelled', 1)
    );
});

it('guardian dashboard shows job stats counts', function () {
    $guardian = User::factory()->guardian()->create();
    $otherGuardian = User::factory()->guardian()->create();

    TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => JobStatus::Pending,
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => JobStatus::Live,
        'published_at' => now()->subHour(),
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => JobStatus::Confirmed,
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => JobStatus::Cancelled,
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => JobStatus::Closed,
    ]);

    TuitionJob::factory()->create([
        'guardian_id' => $otherGuardian->id,
        'status' => JobStatus::Live,
        'published_at' => now()->subHour(),
    ]);

    $response = $this->actingAs($guardian)->get('/guardian/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('jobStats.pending', 1)
        ->where('jobStats.live', 1)
        ->where('jobStats.confirmed', 1)
        ->where('jobStats.cancelled', 1)
        ->where('jobStats.closed', 1)
    );
});
