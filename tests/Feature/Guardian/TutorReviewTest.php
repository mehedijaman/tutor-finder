<?php

use App\Models\TuitionJob;
use App\Models\TuitionJobAssignment;
use App\Models\TutorReview;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function createConfirmedAssignment(?User $guardian = null, ?User $tutor = null): TuitionJobAssignment
{
    $guardian ??= User::factory()->guardian()->create();
    $tutor ??= User::factory()->tutor()->create();

    $job = TuitionJob::factory()->live()->create(['guardian_id' => $guardian->id]);

    return TuitionJobAssignment::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'confirmed_at' => now(),
    ]);
}

// --- Submitting Reviews ---

it('allows a guardian to submit a review for a confirmed assignment', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $assignment = createConfirmedAssignment($guardian, $tutor);

    $this->actingAs($guardian)
        ->post('/guardian/reviews', [
            'job_assignment_id' => $assignment->id,
            'rating' => 5,
            'comment' => 'Excellent tutor, very patient and knowledgeable.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('tutor_reviews', [
        'tutor_user_id' => $tutor->id,
        'guardian_user_id' => $guardian->id,
        'job_assignment_id' => $assignment->id,
        'rating' => 5,
        'comment' => 'Excellent tutor, very patient and knowledgeable.',
    ]);
});

it('allows a review without a comment', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $assignment = createConfirmedAssignment($guardian, $tutor);

    $this->actingAs($guardian)
        ->post('/guardian/reviews', [
            'job_assignment_id' => $assignment->id,
            'rating' => 4,
            'comment' => null,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tutor_reviews', [
        'job_assignment_id' => $assignment->id,
        'rating' => 4,
        'comment' => null,
    ]);
});

it('prevents duplicate reviews for the same assignment', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $assignment = createConfirmedAssignment($guardian, $tutor);

    TutorReview::factory()->create([
        'tutor_user_id' => $tutor->id,
        'guardian_user_id' => $guardian->id,
        'job_assignment_id' => $assignment->id,
        'rating' => 5,
    ]);

    $this->actingAs($guardian)
        ->post('/guardian/reviews', [
            'job_assignment_id' => $assignment->id,
            'rating' => 3,
            'comment' => 'Another review',
        ])
        ->assertSessionHasErrors('job_assignment_id');
});

it('prevents a guardian from reviewing an assignment they do not own', function () {
    $guardian = User::factory()->guardian()->create();
    $otherGuardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $assignment = createConfirmedAssignment($otherGuardian, $tutor);

    $this->actingAs($guardian)
        ->post('/guardian/reviews', [
            'job_assignment_id' => $assignment->id,
            'rating' => 5,
        ])
        ->assertSessionHasErrors('job_assignment_id');
});

it('prevents reviewing an unconfirmed assignment', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $job = TuitionJob::factory()->live()->create(['guardian_id' => $guardian->id]);

    $assignment = TuitionJobAssignment::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'confirmed_at' => null,
    ]);

    $this->actingAs($guardian)
        ->post('/guardian/reviews', [
            'job_assignment_id' => $assignment->id,
            'rating' => 4,
        ])
        ->assertSessionHasErrors('job_assignment_id');
});

// --- Validation ---

it('validates rating is required and between 1 and 5', function () {
    $guardian = User::factory()->guardian()->create();
    $assignment = createConfirmedAssignment($guardian);

    $this->actingAs($guardian)
        ->post('/guardian/reviews', [
            'job_assignment_id' => $assignment->id,
            'rating' => 0,
        ])
        ->assertSessionHasErrors('rating');

    $this->actingAs($guardian)
        ->post('/guardian/reviews', [
            'job_assignment_id' => $assignment->id,
            'rating' => 6,
        ])
        ->assertSessionHasErrors('rating');
});

it('validates comment max length', function () {
    $guardian = User::factory()->guardian()->create();
    $assignment = createConfirmedAssignment($guardian);

    $this->actingAs($guardian)
        ->post('/guardian/reviews', [
            'job_assignment_id' => $assignment->id,
            'rating' => 5,
            'comment' => str_repeat('a', 2001),
        ])
        ->assertSessionHasErrors('comment');
});

// --- Access Control ---

it('requires authentication to submit a review', function () {
    $this->post('/guardian/reviews', [
        'job_assignment_id' => 1,
        'rating' => 5,
    ])->assertRedirect('/login');
});

it('prevents tutors from submitting reviews', function () {
    $tutor = User::factory()->tutor()->create();

    $this->actingAs($tutor)
        ->post('/guardian/reviews', [
            'job_assignment_id' => 1,
            'rating' => 5,
        ])
        ->assertForbidden();
});

// --- Delete Review ---

it('allows a guardian to delete their own review', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $assignment = createConfirmedAssignment($guardian, $tutor);

    $review = TutorReview::factory()->create([
        'tutor_user_id' => $tutor->id,
        'guardian_user_id' => $guardian->id,
        'job_assignment_id' => $assignment->id,
    ]);

    $this->actingAs($guardian)
        ->delete("/guardian/reviews/{$review->id}")
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertSoftDeleted('tutor_reviews', ['id' => $review->id]);
});

it('prevents a guardian from deleting another guardians review', function () {
    $guardian1 = User::factory()->guardian()->create();
    $guardian2 = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $assignment = createConfirmedAssignment($guardian1, $tutor);

    $review = TutorReview::factory()->create([
        'tutor_user_id' => $tutor->id,
        'guardian_user_id' => $guardian1->id,
        'job_assignment_id' => $assignment->id,
    ]);

    $this->actingAs($guardian2)
        ->delete("/guardian/reviews/{$review->id}")
        ->assertForbidden();
});

// --- Update Review ---

it('allows a guardian to update their own review', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $assignment = createConfirmedAssignment($guardian, $tutor);

    $review = TutorReview::factory()->create([
        'tutor_user_id' => $tutor->id,
        'guardian_user_id' => $guardian->id,
        'job_assignment_id' => $assignment->id,
        'rating' => 3,
        'comment' => 'Original comment',
    ]);

    $this->actingAs($guardian)
        ->put("/guardian/reviews/{$review->id}", [
            'rating' => 5,
            'comment' => 'Updated review comment',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('tutor_reviews', [
        'id' => $review->id,
        'rating' => 5,
        'comment' => 'Updated review comment',
    ]);
});

it('prevents a guardian from updating another guardians review', function () {
    $guardian1 = User::factory()->guardian()->create();
    $guardian2 = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $assignment = createConfirmedAssignment($guardian1, $tutor);

    $review = TutorReview::factory()->create([
        'tutor_user_id' => $tutor->id,
        'guardian_user_id' => $guardian1->id,
        'job_assignment_id' => $assignment->id,
    ]);

    $this->actingAs($guardian2)
        ->put("/guardian/reviews/{$review->id}", [
            'rating' => 1,
            'comment' => 'Hijacked review',
        ])
        ->assertForbidden();
});

it('validates rating when updating a review', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $assignment = createConfirmedAssignment($guardian, $tutor);

    $review = TutorReview::factory()->create([
        'tutor_user_id' => $tutor->id,
        'guardian_user_id' => $guardian->id,
        'job_assignment_id' => $assignment->id,
    ]);

    $this->actingAs($guardian)
        ->put("/guardian/reviews/{$review->id}", [
            'rating' => 0,
        ])
        ->assertSessionHasErrors('rating');

    $this->actingAs($guardian)
        ->put("/guardian/reviews/{$review->id}", [
            'rating' => 6,
        ])
        ->assertSessionHasErrors('rating');
});

// --- Public Tutor Profile Reviews ---

it('shows reviews on the tutor public profile', function () {
    $tutor = User::factory()->tutor()->create();
    $guardian = User::factory()->guardian()->create();
    $assignment = createConfirmedAssignment($guardian, $tutor);

    TutorReview::factory()->create([
        'tutor_user_id' => $tutor->id,
        'guardian_user_id' => $guardian->id,
        'job_assignment_id' => $assignment->id,
        'rating' => 4,
        'comment' => 'Great tutor!',
    ]);

    $this->get("/tutors/{$tutor->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TutorShow')
            ->has('reviews.data', 1)
            ->where('reviews.data.0.rating', 4)
            ->where('reviews.data.0.comment', 'Great tutor!')
            ->has('reviews.data.0.guardian')
            ->has('ratingDistribution')
        );
});

it('shows average rating and review count on tutor profile', function () {
    $tutor = User::factory()->tutor()->create();

    foreach ([5, 4, 3] as $rating) {
        $guardian = User::factory()->guardian()->create();
        $assignment = createConfirmedAssignment($guardian, $tutor);
        TutorReview::factory()->create([
            'tutor_user_id' => $tutor->id,
            'guardian_user_id' => $guardian->id,
            'job_assignment_id' => $assignment->id,
            'rating' => $rating,
        ]);
    }

    $this->get("/tutors/{$tutor->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TutorShow')
            ->where('tutor.tutor_reviews_count', 3)
            ->where('tutor.tutor_reviews_avg_rating', fn ($value) => round((float) $value, 1) === 4.0)
        );
});

it('shows rating distribution on tutor profile', function () {
    $tutor = User::factory()->tutor()->create();

    $ratings = [5, 5, 4, 3, 1];
    foreach ($ratings as $rating) {
        $guardian = User::factory()->guardian()->create();
        $assignment = createConfirmedAssignment($guardian, $tutor);
        TutorReview::factory()->create([
            'tutor_user_id' => $tutor->id,
            'guardian_user_id' => $guardian->id,
            'job_assignment_id' => $assignment->id,
            'rating' => $rating,
        ]);
    }

    $this->get("/tutors/{$tutor->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TutorShow')
            ->where('ratingDistribution.5', 2)
            ->where('ratingDistribution.4', 1)
            ->where('ratingDistribution.3', 1)
            ->where('ratingDistribution.2', 0)
            ->where('ratingDistribution.1', 1)
        );
});

it('shows canReview as true for guardians with reviewable assignments', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    createConfirmedAssignment($guardian, $tutor);

    $this->actingAs($guardian)
        ->get("/tutors/{$tutor->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('canReview', true)
            ->has('reviewableAssignments', 1)
        );
});

it('shows canReview as false when no reviewable assignments exist', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();

    $this->actingAs($guardian)
        ->get("/tutors/{$tutor->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('canReview', false)
        );
});

it('shows canReview as false for guests', function () {
    $tutor = User::factory()->tutor()->create();

    $this->get("/tutors/{$tutor->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('canReview', false)
        );
});

// --- Tutor Listing Reviews ---

it('includes review count and average rating on tutor listing', function () {
    $tutor = User::factory()->tutor()->create();
    $guardian = User::factory()->guardian()->create();
    $assignment = createConfirmedAssignment($guardian, $tutor);

    TutorReview::factory()->create([
        'tutor_user_id' => $tutor->id,
        'guardian_user_id' => $guardian->id,
        'job_assignment_id' => $assignment->id,
        'rating' => 5,
    ]);

    $this->get('/tutors')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tutors')
            ->has('tutors.data', fn (Assert $data) => $data
                ->each(fn (Assert $t) => $t
                    ->has('tutor_reviews_count')
                    ->has('tutor_reviews_avg_rating')
                    ->etc()
                )
            )
            ->etc()
        );
});

// --- Guardian Reviews Index Page ---

it('displays the guardian reviews index page with existing reviews', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $assignment = createConfirmedAssignment($guardian, $tutor);

    TutorReview::factory()->create([
        'tutor_user_id' => $tutor->id,
        'guardian_user_id' => $guardian->id,
        'job_assignment_id' => $assignment->id,
        'rating' => 4,
        'comment' => 'Great work!',
    ]);

    $this->actingAs($guardian)
        ->get('/guardian/reviews')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/reviews/Index')
            ->has('reviews.data', 1)
            ->where('reviews.data.0.rating', 4)
            ->where('reviews.data.0.comment', 'Great work!')
            ->has('reviews.data.0.tutor')
            ->has('reviewableAssignments')
        );
});

it('shows reviewable assignments for unreviewed confirmed jobs', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $assignment = createConfirmedAssignment($guardian, $tutor);

    $this->actingAs($guardian)
        ->get('/guardian/reviews')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/reviews/Index')
            ->has('reviews.data', 0)
            ->has('reviewableAssignments', 1)
        );
});

it('excludes already reviewed assignments from reviewable list', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $assignment = createConfirmedAssignment($guardian, $tutor);

    TutorReview::factory()->create([
        'tutor_user_id' => $tutor->id,
        'guardian_user_id' => $guardian->id,
        'job_assignment_id' => $assignment->id,
        'rating' => 5,
    ]);

    $this->actingAs($guardian)
        ->get('/guardian/reviews')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/reviews/Index')
            ->has('reviews.data', 1)
            ->has('reviewableAssignments', 0)
        );
});

it('requires authentication to access the reviews index', function () {
    $this->get('/guardian/reviews')
        ->assertRedirect('/login');
});
