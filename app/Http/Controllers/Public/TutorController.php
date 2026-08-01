<?php

namespace App\Http\Controllers\Public;

use App\Enums\JobStatus;
use App\Enums\TaxonomyStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionJob;
use App\Models\TuitionType;
use App\Models\TutorReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TutorController extends Controller
{
    public function index(Request $request)
    {
        $allowedDayValues = collect($this->dayOptions())->pluck('value')->all();

        $area = trim($request->string('area')->toString());
        $gender = trim($request->string('gender')->toString());
        $availableDay = strtolower(trim($request->string('available_day')->toString()));
        $verified = trim($request->string('verified')->toString());
        $tuitionTypeId = (int) $request->integer('tuition_type_id');
        $categoryId = (int) $request->integer('category_id');
        $classId = (int) $request->integer('class_id');
        $subjectId = (int) $request->integer('subject_id');
        $locationId = (int) $request->integer('location_id');
        $minBudget = $request->filled('min_budget') ? (int) $request->input('min_budget') : null;
        $maxBudget = $request->filled('max_budget') ? (int) $request->input('max_budget') : null;

        if (! in_array($gender, ['male', 'female', 'other', 'prefer_not_to_say'], true)) {
            $gender = '';
        }

        if (! in_array($availableDay, $allowedDayValues, true)) {
            $availableDay = '';
        }

        if (! in_array($verified, ['yes', 'no'], true)) {
            $verified = '';
        }

        if ($tuitionTypeId <= 0) {
            $tuitionTypeId = 0;
        }

        if ($categoryId <= 0) {
            $categoryId = 0;
        }

        if ($classId <= 0) {
            $classId = 0;
        }

        if ($subjectId <= 0) {
            $subjectId = 0;
        }

        if ($locationId <= 0) {
            $locationId = 0;
        }

        $search = trim($request->string('q')->toString());
        $institute = trim($request->string('institute')->toString());

        $query = User::query()
            ->where('role', UserRole::Tutor)
            ->where('status', UserStatus::Active)
            ->with('tutorProfile')
            ->with(['tutorEducations' => function ($q) {
                $q->orderBy('sort_order')->orderByDesc('is_current');
            }])
            ->withCount('tutorReviews')
            ->withAvg('tutorReviews', 'rating');

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                if (is_numeric($search)) {
                    $q->where('id', (int) $search);
                }
                $q->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhereHas('tutorEducations', function (Builder $edu) use ($search): void {
                        $edu->where('institute', 'like', '%'.$search.'%')
                            ->orWhere('department', 'like', '%'.$search.'%')
                            ->orWhere('degree', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($institute !== '') {
            $query->whereHas('tutorEducations', function (Builder $edu) use ($institute): void {
                $edu->where('institute', 'like', '%'.$institute.'%');
            });
        }

        if ($gender !== '') {
            $query->whereHas('tutorProfile', function (Builder $q) use ($gender): void {
                $q->where('gender', $gender);
            });
        }

        if ($area !== '') {
            $query->whereHas('tutorProfile', function (Builder $q) use ($area): void {
                $q->where('present_address', 'like', '%'.$area.'%');
            });
        }

        if ($minBudget !== null) {
            $query->whereHas('tutorProfile', function (Builder $q) use ($minBudget): void {
                $q->where('expected_salary_min', '>=', $minBudget);
            });
        }

        if ($maxBudget !== null) {
            $query->whereHas('tutorProfile', function (Builder $q) use ($maxBudget): void {
                $q->where('expected_salary_max', '<=', $maxBudget);
            });
        }

        if ($tuitionTypeId > 0) {
            $query->whereHas('tutorProfile', function (Builder $q) use ($tuitionTypeId): void {
                $q->whereJsonContains('preferred_tuition_types', $tuitionTypeId);
            });
        }

        if ($categoryId > 0) {
            $query->whereHas('tutorProfile', function (Builder $q) use ($categoryId): void {
                $q->whereJsonContains('preferred_categories', $categoryId);
            });
        }

        if ($classId > 0) {
            $query->whereHas('tutorProfile', function (Builder $q) use ($classId): void {
                $q->whereJsonContains('preferred_classes', $classId);
            });
        }

        if ($subjectId > 0) {
            $query->whereHas('tutorProfile', function (Builder $q) use ($subjectId): void {
                $q->whereJsonContains('preferred_subjects', $subjectId);
            });
        }

        if ($locationId > 0) {
            $query->whereHas('tutorProfile', function (Builder $q) use ($locationId): void {
                $q->whereJsonContains('preferred_locations', $locationId);
            });
        }

        if ($availableDay !== '') {
            $query->whereHas('tutorProfile', function (Builder $q) use ($availableDay): void {
                $q->whereJsonContains('available_days', $availableDay);
            });
        }

        if ($verified === 'yes') {
            $query->whereNotNull('verified_at');
        }

        if ($verified === 'no') {
            $query->whereNull('verified_at');
        }

        $tutors = $query->orderByDesc('verified_at')->paginate(20)->appends($request->query());
        $preferenceMaps = $this->preferenceNameMaps();

        $tutors->getCollection()->transform(function (User $tutor) use ($preferenceMaps): User {
            $this->mapTutorProfilePreferences($tutor, $preferenceMaps);

            return $tutor;
        });

        return inertia('Tutors', [
            'tutors' => $tutors,
            'total' => $tutors->total(),
            'filters' => [
                'q' => $search === '' ? null : $search,
                'institute' => $institute === '' ? null : $institute,
                'area' => $area === '' ? null : $area,
                'gender' => $gender === '' ? null : $gender,
                'min_budget' => $minBudget === null ? null : (string) $minBudget,
                'max_budget' => $maxBudget === null ? null : (string) $maxBudget,
                'tuition_type_id' => $tuitionTypeId > 0 ? $tuitionTypeId : null,
                'category_id' => $categoryId > 0 ? $categoryId : null,
                'class_id' => $classId > 0 ? $classId : null,
                'subject_id' => $subjectId > 0 ? $subjectId : null,
                'location_id' => $locationId > 0 ? $locationId : null,
                'available_day' => $availableDay === '' ? null : $availableDay,
                'verified' => $verified === '' ? null : $verified,
            ],
            'filterOptions' => [
                'tuitionTypes' => $this->activeTuitionTypes(),
                'categories' => $this->activeCategories(),
                'classes' => $this->activeSchoolClasses(),
                'subjects' => $this->activeSubjects(),
                'locations' => $this->activeLocations(),
                'days' => $this->dayOptions(),
                'genders' => [
                    ['value' => 'male', 'label' => 'Male'],
                    ['value' => 'female', 'label' => 'Female'],
                    ['value' => 'other', 'label' => 'Other'],
                    ['value' => 'prefer_not_to_say', 'label' => 'Prefer Not to Say'],
                ],
                'verified' => [
                    ['value' => 'yes', 'label' => 'Verified Only'],
                    ['value' => 'no', 'label' => 'Unverified Only'],
                ],
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
        $this->mapTutorProfilePreferences($tutor, $this->preferenceNameMaps());

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
        $guardianJobs = [];

        if (auth()->check() && auth()->user()->role === UserRole::Guardian) {
            $guardianJobs = TuitionJob::query()
                ->where('guardian_id', auth()->id())
                ->where('status', JobStatus::Live)
                ->latest()
                ->get(['id', 'title'])
                ->toArray();

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
            'guardianJobs' => $guardianJobs,
            'filterOptions' => [
                'tuitionTypes' => $this->activeTuitionTypes(),
                'categories' => $this->activeCategories(),
                'classes' => $this->activeSchoolClasses(),
                'subjects' => $this->activeSubjects(),
                'locations' => $this->activeLocations(),
                'countries' => Country::query()->where('status', TaxonomyStatus::Active)->ordered()->get(['id', 'name'])->toArray(),
                'cities' => City::query()->where('status', TaxonomyStatus::Active)->ordered()->get(['id', 'name', 'country_id'])->toArray(),
                'areas' => Area::query()->where('status', TaxonomyStatus::Active)->ordered()->get(['id', 'name', 'city_id'])->toArray(),
                'days' => $this->dayOptions(),
                'genderOptions' => [
                    ['value' => 'any', 'label' => 'Any'],
                    ['value' => 'male', 'label' => 'Male'],
                    ['value' => 'female', 'label' => 'Female'],
                ],
            ],
            'meta' => [
                'title' => $tutor->name.' - Tutor',
                'description' => $tutor->tutorProfile?->bio ?? 'View tutor profile on '.config('app.name'),
            ],
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function preferenceNameMaps(): array
    {
        return [
            'tuition_types' => TuitionType::query()
                ->where('status', TaxonomyStatus::Active)
                ->ordered()
                ->pluck('name', 'id')
                ->all(),
            'categories' => Category::query()
                ->where('status', TaxonomyStatus::Active)
                ->ordered()
                ->pluck('name', 'id')
                ->all(),
            'classes' => SchoolClass::query()
                ->where('status', TaxonomyStatus::Active)
                ->ordered()
                ->pluck('name', 'id')
                ->all(),
            'subjects' => Subject::query()
                ->where('status', TaxonomyStatus::Active)
                ->ordered()
                ->pluck('name', 'id')
                ->all(),
            'locations' => Area::query()
                ->where('status', TaxonomyStatus::Active)
                ->ordered()
                ->pluck('name', 'id')
                ->all(),
        ];
    }

    /**
     * @param  array<string, array<int, string>>  $preferenceMaps
     */
    private function mapTutorProfilePreferences(User $tutor, array $preferenceMaps): void
    {
        $profile = $tutor->tutorProfile;

        if ($profile === null) {
            return;
        }

        $profile->preferred_tuition_types = $this->mapIdsToNames(
            $profile->preferred_tuition_types,
            $preferenceMaps['tuition_types'] ?? [],
        );
        $profile->preferred_categories = $this->mapIdsToNames(
            $profile->preferred_categories,
            $preferenceMaps['categories'] ?? [],
        );
        $profile->preferred_classes = $this->mapIdsToNames(
            $profile->preferred_classes,
            $preferenceMaps['classes'] ?? [],
        );
        $profile->preferred_subjects = $this->mapIdsToNames(
            $profile->preferred_subjects,
            $preferenceMaps['subjects'] ?? [],
        );
        $profile->preferred_locations = $this->mapIdsToNames(
            $profile->preferred_locations,
            $preferenceMaps['locations'] ?? [],
        );
    }

    /**
     * @param  array<int, int|string>|null  $ids
     * @param  array<int, string>  $nameMap
     * @return array<int, string>
     */
    private function mapIdsToNames(?array $ids, array $nameMap): array
    {
        if ($ids === null || $ids === []) {
            return [];
        }

        return collect($ids)
            ->map(fn (mixed $item): int => (int) $item)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->map(fn (int $id): string => $nameMap[$id] ?? (string) $id)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function activeTuitionTypes(): array
    {
        return TuitionType::query()
            ->where('status', TaxonomyStatus::Active)
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (TuitionType $item): array => [
                'id' => $item->id,
                'name' => $item->name,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function activeCategories(): array
    {
        return Category::query()
            ->where('status', TaxonomyStatus::Active)
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (Category $item): array => [
                'id' => $item->id,
                'name' => $item->name,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, category_id: int}>
     */
    private function activeSchoolClasses(): array
    {
        return SchoolClass::query()
            ->where('status', TaxonomyStatus::Active)
            ->ordered()
            ->get(['id', 'name', 'category_id'])
            ->map(fn (SchoolClass $item): array => [
                'id' => $item->id,
                'name' => $item->name,
                'category_id' => $item->category_id,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, class_id: int}>
     */
    private function activeSubjects(): array
    {
        return Subject::query()
            ->where('status', TaxonomyStatus::Active)
            ->ordered()
            ->get(['id', 'name', 'class_id'])
            ->map(fn (Subject $item): array => [
                'id' => $item->id,
                'name' => $item->name,
                'class_id' => $item->class_id,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function activeLocations(): array
    {
        return Area::query()
            ->where('status', TaxonomyStatus::Active)
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (Area $item): array => [
                'id' => $item->id,
                'name' => $item->name,
            ])
            ->all();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function dayOptions(): array
    {
        return [
            ['value' => 'sat', 'label' => 'Saturday'],
            ['value' => 'sun', 'label' => 'Sunday'],
            ['value' => 'mon', 'label' => 'Monday'],
            ['value' => 'tue', 'label' => 'Tuesday'],
            ['value' => 'wed', 'label' => 'Wednesday'],
            ['value' => 'thu', 'label' => 'Thursday'],
            ['value' => 'fri', 'label' => 'Friday'],
        ];
    }
}
