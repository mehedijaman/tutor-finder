<?php

use App\Models\TuitionJob;
use App\Models\TuitionJobAssignment;
use App\Models\TutorReview;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->admin()->create();
    $this->admin->assignRole('super-admin');
});

function createReviewWithAssignment(?User $guardian = null, ?User $tutor = null): TutorReview
{
    $guardian ??= User::factory()->guardian()->create();
    $tutor ??= User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create(['guardian_id' => $guardian->id]);

    $assignment = TuitionJobAssignment::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'confirmed_at' => now(),
    ]);

    return TutorReview::factory()->create([
        'tutor_user_id' => $tutor->id,
        'guardian_user_id' => $guardian->id,
        'job_assignment_id' => $assignment->id,
    ]);
}

// --- Index ---

it('displays the admin reviews index page', function () {
    $review = createReviewWithAssignment();

    $this->actingAs($this->admin)
        ->get('/admin/reviews')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/reviews/Index')
            ->has('reviews.data', 1)
            ->has('reviews.data.0.tutor')
            ->has('reviews.data.0.guardian')
            ->has('filters')
        );
});

it('filters reviews by rating', function () {
    $review5 = createReviewWithAssignment();
    $review5->update(['rating' => 5]);

    $review3 = createReviewWithAssignment();
    $review3->update(['rating' => 3]);

    $this->actingAs($this->admin)
        ->get('/admin/reviews?rating=5')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/reviews/Index')
            ->has('reviews.data', 1)
            ->where('reviews.data.0.rating', 5)
        );
});

it('searches reviews by tutor name', function () {
    $tutor = User::factory()->tutor()->create(['name' => 'Unique Tutor Name']);
    $review = createReviewWithAssignment(tutor: $tutor);

    createReviewWithAssignment();

    $this->actingAs($this->admin)
        ->get('/admin/reviews?q=Unique+Tutor')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/reviews/Index')
            ->has('reviews.data', 1)
        );
});

// --- Update ---

it('allows admin to update a review', function () {
    $review = createReviewWithAssignment();

    $this->actingAs($this->admin)
        ->put("/admin/reviews/{$review->id}", [
            'rating' => 3,
            'comment' => 'Updated by admin.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('tutor_reviews', [
        'id' => $review->id,
        'rating' => 3,
        'comment' => 'Updated by admin.',
    ]);
});

it('validates rating when admin updates a review', function () {
    $review = createReviewWithAssignment();

    $this->actingAs($this->admin)
        ->put("/admin/reviews/{$review->id}", [
            'rating' => 6,
            'comment' => 'Test',
        ])
        ->assertSessionHasErrors('rating');
});

// --- Delete ---

it('allows admin to delete a review', function () {
    $review = createReviewWithAssignment();

    $this->actingAs($this->admin)
        ->delete("/admin/reviews/{$review->id}")
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertSoftDeleted('tutor_reviews', ['id' => $review->id]);
});

// --- Authorization ---

it('requires review-view permission to access reviews index', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/reviews')
        ->assertForbidden();
});

it('requires review-update permission to update a review', function () {
    $admin = User::factory()->admin()->create();
    $review = createReviewWithAssignment();

    $this->actingAs($admin)
        ->put("/admin/reviews/{$review->id}", ['rating' => 4, 'comment' => 'Test'])
        ->assertForbidden();
});

it('requires review-delete permission to delete a review', function () {
    $admin = User::factory()->admin()->create();
    $review = createReviewWithAssignment();

    $this->actingAs($admin)
        ->delete("/admin/reviews/{$review->id}")
        ->assertForbidden();
});

it('requires authentication to access admin reviews', function () {
    $this->get('/admin/reviews')
        ->assertRedirect('/admin/login');
});
