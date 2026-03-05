<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TutorReviewUpdateRequest;
use App\Models\TutorReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class TutorReviewController extends Controller
{
    /**
     * Display all tutor reviews with filtering and pagination.
     */
    public function index(Request $request): Response
    {
        $query = trim($request->string('q')->toString());
        $ratingFilter = $request->integer('rating');

        $showTrashed = $request->boolean('trash');

        $reviews = TutorReview::query()
            ->when($showTrashed, fn ($q) => $q->onlyTrashed())
            ->with([
                'tutor:id,name,photo_url',
                'guardian:id,name',
                'jobAssignment:id,job_id',
                'jobAssignment.job:id,title',
            ])
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($sub) use ($query): void {
                    $sub->where('comment', 'like', "%{$query}%")
                        ->orWhereHas('tutor', fn ($u) => $u->where('name', 'like', "%{$query}%"))
                        ->orWhereHas('guardian', fn ($u) => $u->where('name', 'like', "%{$query}%"));
                });
            })
            ->when($ratingFilter >= 1 && $ratingFilter <= 5, fn ($b) => $b->where('rating', $ratingFilter))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $trashedCount = TutorReview::onlyTrashed()->count();

        return inertia('admin/reviews/Index', [
            'reviews' => $reviews,
            'filters' => [
                'q' => $query,
                'rating' => $ratingFilter ?: '',
                'trash' => $showTrashed,
            ],
            'trashedCount' => $trashedCount,
        ]);
    }

    /**
     * Update a tutor review.
     */
    public function update(TutorReviewUpdateRequest $request, TutorReview $tutorReview): RedirectResponse
    {
        $tutorReview->update($request->validated());

        return back()->with('success', 'Review has been updated successfully.');
    }

    /**
     * Delete a tutor review.
     */
    public function destroy(TutorReview $tutorReview): RedirectResponse
    {
        $tutorReview->delete();

        return back()->with('success', 'Review has been deleted.');
    }

    /**
     * Restore a soft-deleted tutor review.
     */
    public function restore(TutorReview $tutorReview): RedirectResponse
    {
        if (! $tutorReview->trashed()) {
            return back()->with('success', 'Review is already active.');
        }

        $tutorReview->restore();

        return back()->with('success', 'Review has been restored.');
    }

    /**
     * Permanently delete a soft-deleted tutor review.
     */
    public function forceDelete(TutorReview $tutorReview): RedirectResponse
    {
        if (! $tutorReview->trashed()) {
            abort(403);
        }

        $tutorReview->forceDelete();

        return back()->with('success', 'Review has been permanently deleted.');
    }

    /**
     * Restore all soft-deleted tutor reviews.
     */
    public function restoreAll(): RedirectResponse
    {
        $count = TutorReview::onlyTrashed()->count();

        TutorReview::onlyTrashed()->restore();

        return back()->with('success', "{$count} review(s) have been restored.");
    }

    /**
     * Permanently delete all soft-deleted tutor reviews.
     */
    public function emptyTrash(): RedirectResponse
    {
        $count = TutorReview::onlyTrashed()->count();

        TutorReview::onlyTrashed()->forceDelete();

        return back()->with('success', "{$count} review(s) have been permanently deleted.");
    }
}
