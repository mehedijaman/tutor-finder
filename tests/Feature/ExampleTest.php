<?php

use App\Models\Testimonial;
use App\Models\TuitionJob;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});

test('authenticated users receive auth props on home page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('auth.user.id', $user->id),
        );
});

test('home page hero stats are sourced from database', function () {
    User::factory()->count(3)->tutor()->create(['status' => 'active']);
    User::factory()->tutor()->create(['status' => 'suspended']);

    $firstGuardian = User::factory()->guardian()->create();
    $secondGuardian = User::factory()->guardian()->create();
    $thirdGuardian = User::factory()->guardian()->create();

    TuitionJob::factory()->create([
        'guardian_id' => $firstGuardian->getKey(),
        'status' => TuitionJob::STATUS_CONFIRMED,
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $firstGuardian->getKey(),
        'status' => TuitionJob::STATUS_CONFIRMED,
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $secondGuardian->getKey(),
        'status' => TuitionJob::STATUS_CONFIRMED,
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $thirdGuardian->getKey(),
        'status' => TuitionJob::STATUS_LIVE,
    ]);

    Testimonial::factory()->create([
        'status' => Testimonial::STATUS_ACTIVE,
        'rating' => 5,
    ]);
    Testimonial::factory()->create([
        'status' => Testimonial::STATUS_ACTIVE,
        'rating' => 4,
    ]);
    Testimonial::factory()->create([
        'status' => Testimonial::STATUS_INACTIVE,
        'rating' => 1,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('heroStats.active_tutors', 3)
            ->where('heroStats.families_served', 2)
            ->where('heroStats.average_rating', 4.5),
        );
});
