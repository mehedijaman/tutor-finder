<?php

use App\Models\Notice;
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
