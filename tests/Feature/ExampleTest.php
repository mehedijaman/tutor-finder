<?php

use App\Enums\JobStatus;
use App\Enums\TaxonomyStatus;
use App\Models\Category;
use App\Models\Testimonial;
use App\Models\TuitionJob;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        'status' => JobStatus::Confirmed,
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $firstGuardian->getKey(),
        'status' => JobStatus::Confirmed,
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $secondGuardian->getKey(),
        'status' => JobStatus::Confirmed,
    ]);
    TuitionJob::factory()->create([
        'guardian_id' => $thirdGuardian->getKey(),
        'status' => JobStatus::Live,
    ]);

    Testimonial::factory()->create([
        'status' => TaxonomyStatus::Active,
        'rating' => 5,
    ]);
    Testimonial::factory()->create([
        'status' => TaxonomyStatus::Active,
        'rating' => 4,
    ]);
    Testimonial::factory()->create([
        'status' => TaxonomyStatus::Inactive,
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

test('home page includes only active testimonials for carousel', function () {
    Testimonial::factory()->create([
        'name' => 'Primary Testimonial',
        'status' => TaxonomyStatus::Active,
        'sort_order' => 1,
        'rating' => 5,
    ]);

    Testimonial::factory()->create([
        'name' => 'Secondary Testimonial',
        'status' => TaxonomyStatus::Active,
        'sort_order' => 2,
        'rating' => 4,
    ]);

    Testimonial::factory()->create([
        'name' => 'Hidden Testimonial',
        'status' => TaxonomyStatus::Inactive,
        'sort_order' => 0,
    ]);

    $trashedTestimonial = Testimonial::factory()->create([
        'name' => 'Trashed Testimonial',
        'status' => TaxonomyStatus::Active,
    ]);
    $trashedTestimonial->delete();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->has('testimonials', 2)
            ->where('testimonials.0.name', 'Primary Testimonial')
            ->where('testimonials.1.name', 'Secondary Testimonial')
            ->where('testimonials.0.rating', 5)
            ->where('testimonials.1.rating', 4),
        );
});

test('home page testimonial payload prefers media-library avatar url', function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);

    $testimonial = Testimonial::factory()->create([
        'name' => 'Media Avatar Testimonial',
        'status' => TaxonomyStatus::Active,
        'avatar_url' => null,
    ]);

    $testimonial
        ->addMedia(UploadedFile::fake()->image('testimonial-avatar.jpg', 300, 300))
        ->toMediaCollection('avatar');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where(
                'testimonials.0.avatar_url',
                fn ($value) => is_string($value) && str_contains($value, 'testimonial-avatar'),
            ),
        );
});

test('home page tuition methods are sourced from active category taxonomy', function () {
    Category::factory()->create([
        'name' => 'First Method',
        'slug' => 'first-method',
        'description' => 'First method description',
        'status' => TaxonomyStatus::Active,
        'sort_order' => 1,
    ]);

    Category::factory()->create([
        'name' => 'Second Method',
        'slug' => 'second-method',
        'description' => 'Second method description',
        'status' => TaxonomyStatus::Active,
        'sort_order' => 2,
    ]);

    Category::factory()->create([
        'name' => 'Inactive Method',
        'slug' => 'inactive-method',
        'status' => TaxonomyStatus::Inactive,
        'sort_order' => 0,
    ]);

    $trashedMethod = Category::factory()->create([
        'name' => 'Trashed Method',
        'slug' => 'trashed-method',
        'status' => TaxonomyStatus::Active,
        'sort_order' => 3,
    ]);
    $trashedMethod->delete();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->has('tuitionMethods', 2)
            ->where('tuitionMethods.0.slug', 'first-method')
            ->where('tuitionMethods.1.slug', 'second-method')
            ->where('tuitionMethods.0.description', 'First method description'),
        );
});
