<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TutorController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->where('role', 'tutor')
            ->where('status', 'active')
            ->with('tutorProfile')
            ->with(['tutorEducations' => function ($q) {
                $q->orderBy('sort_order')->orderByDesc('is_current');
            }]);

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
            ->where('role', 'tutor')
            ->where('status', 'active')
            ->with('tutorProfile')
            ->with('tutorEducations')
            ->findOrFail($id);

        return inertia('TutorShow', [
            'tutor' => $tutor,
            'meta' => [
                'title' => $tutor->name.' - Tutor',
                'description' => $tutor->tutorProfile?->bio ?? 'View tutor profile on '.config('app.name'),
            ],
        ]);
    }
}
