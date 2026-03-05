<?php

namespace App\Http\Controllers\Public;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\TutorReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TutorController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->where('role', UserRole::Tutor)
            ->where('status', UserStatus::Active)
            ->with('tutorProfile')
            ->with(['tutorEducations' => function ($q) {
                $q->orderBy('sort_order')->orderByDesc('is_current');
            }])
            ->withCount('tutorReviews')
            ->withAvg('tutorReviews', 'rating');

        if ($request->filled('gender')) {
            $query->whereHas('tutorProfile', function ($q) use ($request) {
                $q->where('gender', $request->gender);
            });
        }

        if ($request->filled('area')) {
            $query->whereHas('tutorProfile', function ($q) use ($request) {
                $q->where('present_address', 'like', '%'.$request->area.'%');
            });
        }

        if ($request->filled('min_budget')) {
            $query->whereHas('tutorProfile', function ($q) use ($request) {
                $q->where('expected_salary_min', '>=', (int) $request->min_budget);
            });
        }

        if ($request->filled('max_budget')) {
            $query->whereHas('tutorProfile', function ($q) use ($request) {
                $q->where('expected_salary_max', '<=', (int) $request->max_budget);
            });
        }

        $tutors = $query->orderByDesc('verified_at')->paginate(20)->appends($request->query());

        return inertia('Tutors', [
            'tutors' => $tutors,
            'total' => $tutors->total(),
            'filters' => [
                'area' => $request->area,
                'gender' => $request->gender,
                'min_budget' => $request->min_budget,
                'max_budget' => $request->max_budget,
            ],
            'meta' => [
                'title' => 'Find Tutors - '.config('app.name'),
                'description' => 'Browse and connect with tutors for home tuition, online tutoring, and coaching.',
            ],
        ]);
    }

    public function show(int $id)
    {
        $tutor = User::query()
            ->where('role', UserRole::Tutor)
            ->where('status', UserStatus::Active)
            ->with('tutorProfile')
            ->with('tutorEducations')
            ->withCount('tutorReviews')
            ->withAvg('tutorReviews', 'rating')
            ->findOrFail($id);

        $reviews = TutorReview::query()
            ->where('tutor_user_id', $id)
            ->with('guardian:id,name,photo_url')
            ->orderByDesc('created_at')
            ->paginate(10);

        $ratingDistribution = TutorReview::query()
            ->where('tutor_user_id', $id)
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribution[$i] = $ratingDistribution[$i] ?? 0;
        }

        $canReview = false;
        $reviewableAssignments = [];

        if (auth()->check() && auth()->user()->role === UserRole::Guardian) {
            $reviewableAssignments = DB::table('tuition_job_assignments')
                ->join('tuition_jobs', 'tuition_job_assignments.job_id', '=', 'tuition_jobs.id')
                ->leftJoin('tutor_reviews', 'tuition_job_assignments.id', '=', 'tutor_reviews.job_assignment_id')
                ->where('tuition_job_assignments.tutor_user_id', $id)
                ->where('tuition_jobs.guardian_id', auth()->id())
                ->whereNotNull('tuition_job_assignments.confirmed_at')
                ->whereNull('tuition_job_assignments.deleted_at')
                ->whereNull('tutor_reviews.id')
                ->select(
                    'tuition_job_assignments.id as assignment_id',
                    'tuition_jobs.title as job_title',
                )
                ->get()
                ->toArray();

            $canReview = count($reviewableAssignments) > 0;
        }

        return inertia('TutorShow', [
            'tutor' => $tutor,
            'reviews' => $reviews,
            'ratingDistribution' => $distribution,
            'canReview' => $canReview,
            'reviewableAssignments' => $reviewableAssignments,
            'meta' => [
                'title' => $tutor->name.' - Tutor',
                'description' => $tutor->tutorProfile?->bio ?? 'View tutor profile on '.config('app.name'),
            ],
        ]);
    }
}
