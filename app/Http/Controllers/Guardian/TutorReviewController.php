<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\TutorReviewStoreRequest;
use App\Http\Requests\Guardian\TutorReviewUpdateRequest;
use App\Models\TuitionJobAssignment;
use App\Models\TutorReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class TutorReviewController extends Controller
{
    /**
     * Display guardian's reviews and reviewable assignments.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $reviews = TutorReview::query()
            ->where('guardian_user_id', $user->id)
            ->with([
                'tutor:id,name,photo_url',
                'jobAssignment:id,job_id',
                'jobAssignment.job:id,title',
            ])
            ->orderByDesc('created_at')
            ->paginate(10);

        $reviewableAssignments = DB::table('tuition_job_assignments')
            ->join('tuition_jobs', 'tuition_job_assignments.job_id', '=', 'tuition_jobs.id')
            ->join('users', 'tuition_job_assignments.tutor_user_id', '=', 'users.id')
            ->leftJoin('tutor_reviews', 'tuition_job_assignments.id', '=', 'tutor_reviews.job_assignment_id')
            ->where('tuition_jobs.guardian_id', $user->id)
            ->whereNotNull('tuition_job_assignments.confirmed_at')
            ->whereNull('tuition_job_assignments.deleted_at')
            ->whereNull('tutor_reviews.id')
            ->select(
                'tuition_job_assignments.id as assignment_id',
                'tuition_job_assignments.tutor_user_id',
                'tuition_jobs.title as job_title',
                'users.name as tutor_name',
            )
            ->get()
            ->toArray();

        return inertia('guardian/reviews/Index', [
            'reviews' => $reviews,
            'reviewableAssignments' => $reviewableAssignments,
        ]);
    }

    /**
     * Store a new tutor review.
     */
    public function store(TutorReviewStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $assignment = TuitionJobAssignment::findOrFail($validated['job_assignment_id']);

        TutorReview::create([
            'tutor_user_id' => $assignment->tutor_user_id,
            'guardian_user_id' => $request->user()->id,
            'job_assignment_id' => $assignment->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Your review has been submitted successfully.');
    }

    /**
     * Update an existing tutor review.
     */
    public function update(TutorReviewUpdateRequest $request, TutorReview $tutorReview): RedirectResponse
    {
        $tutorReview->update($request->validated());

        return back()->with('success', 'Your review has been updated successfully.');
    }

    /**
     * Delete a tutor review.
     */
    public function destroy(TutorReview $tutorReview): RedirectResponse
    {
        if ($tutorReview->guardian_user_id !== auth()->id()) {
            abort(403);
        }

        $tutorReview->delete();

        return back()->with('success', 'Your review has been deleted.');
    }
}
