<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\Tuition\JobStoreRequest;
use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionJob;
use App\Models\TuitionType;
use App\Support\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class TuitionJobController extends Controller
{
    /**
     * Display guardian jobs list.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $search = trim($request->string('q')->toString());
        $status = strtolower(trim($request->string('status')->toString()));

        if (! in_array($status, TuitionJob::statuses(), true)) {
            $status = '';
        }

        $items = TuitionJob::query()
            ->with(['city:id,name', 'area:id,name', 'category:id,name', 'schoolClass:id,name'])
            ->withCount('applications')
            ->where('guardian_id', $user?->getAuthIdentifier())
            ->when($search !== '', function ($builder) use ($search): void {
                $builder->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (TuitionJob $job): array => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'status' => $job->status,
                'category_name' => $job->category?->name,
                'class_name' => $job->schoolClass?->name,
                'city_name' => $job->city?->name,
                'area_name' => $job->area?->name,
                'applications_count' => $job->applications_count,
                'published_at' => $job->published_at?->toDateTimeString(),
                'expires_at' => $job->expires_at?->toDateTimeString(),
                'updated_at' => $job->updated_at?->toDateTimeString(),
            ]);

        return inertia('guardian/jobs/Index', [
            'items' => $items,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Show create job page for guardian.
     */
    public function create(): Response
    {
        return inertia('guardian/jobs/Create', [
            'tuitionTypes' => $this->activeTuitionTypes(),
            'categories' => $this->activeCategories(),
            'schoolClasses' => $this->activeSchoolClasses(),
            'countries' => $this->activeCountries(),
            'cities' => $this->activeCities(),
            'areas' => $this->activeAreas(),
            'subjects' => $this->activeSubjects(),
            'genderOptions' => $this->genderOptions(),
            'dayOptions' => $this->dayOptions(),
        ]);
    }

    /**
     * Store a new guardian job.
     */
    public function store(
        JobStoreRequest $request,
        SlugService $slugService,
    ): RedirectResponse {
        $validated = $request->validated();
        $user = $request->user();

        $title = trim((string) $validated['title']);
        $slugBase = trim((string) ($validated['slug'] ?: $title));
        $tuitionDays = $validated['tuition_days'] ?? [];
        $daysPerWeek = count($tuitionDays) > 0 ? count($tuitionDays) : null;

        DB::transaction(function () use ($validated, $title, $slugBase, $tuitionDays, $daysPerWeek, $slugService, $user): void {
            $job = TuitionJob::query()->create([
                'title' => $title,
                'slug' => $slugService->unique(TuitionJob::class, $slugBase),
                'description' => (string) $validated['description'],
                'tuition_type_id' => (int) $validated['tuition_type_id'],
                'category_id' => (int) $validated['category_id'],
                'class_id' => (int) $validated['class_id'],
                'country_id' => (int) $validated['country_id'],
                'city_id' => (int) $validated['city_id'],
                'area_id' => $validated['area_id'] ?? null,
                'guardian_id' => $user?->getAuthIdentifier(),
                'location' => $validated['location'] ?: null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'student_gender' => (string) $validated['student_gender'],
                'tutor_gender' => (string) $validated['tutor_gender'],
                'tuition_days' => $tuitionDays,
                'days_per_week' => $daysPerWeek,
                'tuition_time' => $validated['tuition_time'] ?: null,
                'tuition_duration' => $validated['tuition_duration'] ?: null,
                'no_of_students' => $validated['no_of_students'] ?? null,
                'salary_amount' => $validated['salary_amount'] ?? null,
                'salary_currency' => $validated['salary_currency'] ?: 'BDT',
                'salary_negotiable' => (bool) $validated['salary_negotiable'],
                'status' => TuitionJob::STATUS_PENDING,
                'cancellation_reason' => null,
                'published_at' => null,
                'expires_at' => $validated['expires_at'] ?? null,
                'created_by' => null,
                'updated_by' => null,
                'confirmed_by' => null,
                'confirmed_at' => null,
            ]);

            $job->subjects()->sync($validated['subject_ids'] ?? []);
        });

        return redirect()
            ->route('guardian.jobs.index')
            ->with('status', 'Job request submitted successfully.');
    }

    /**
     * Get active tuition types.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function activeTuitionTypes(): array
    {
        return TuitionType::query()
            ->where('status', TuitionType::STATUS_ACTIVE)
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (TuitionType $tuitionType): array => [
                'id' => $tuitionType->id,
                'name' => $tuitionType->name,
            ])
            ->all();
    }

    /**
     * Get active categories.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function activeCategories(): array
    {
        return Category::query()
            ->where('status', Category::STATUS_ACTIVE)
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->all();
    }

    /**
     * Get active school classes.
     *
     * @return array<int, array{id: int, name: string, category_id: int}>
     */
    private function activeSchoolClasses(): array
    {
        return SchoolClass::query()
            ->where('status', SchoolClass::STATUS_ACTIVE)
            ->ordered()
            ->get(['id', 'name', 'category_id'])
            ->map(fn (SchoolClass $schoolClass): array => [
                'id' => $schoolClass->id,
                'name' => $schoolClass->name,
                'category_id' => $schoolClass->category_id,
            ])
            ->all();
    }

    /**
     * Get active countries.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function activeCountries(): array
    {
        return Country::query()
            ->where('status', Country::STATUS_ACTIVE)
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (Country $country): array => [
                'id' => $country->id,
                'name' => $country->name,
            ])
            ->all();
    }

    /**
     * Get active cities.
     *
     * @return array<int, array{id: int, name: string, country_id: int}>
     */
    private function activeCities(): array
    {
        return City::query()
            ->where('status', City::STATUS_ACTIVE)
            ->ordered()
            ->get(['id', 'name', 'country_id'])
            ->map(fn (City $city): array => [
                'id' => $city->id,
                'name' => $city->name,
                'country_id' => $city->country_id,
            ])
            ->all();
    }

    /**
     * Get active areas.
     *
     * @return array<int, array{id: int, name: string, city_id: int}>
     */
    private function activeAreas(): array
    {
        return Area::query()
            ->where('status', Area::STATUS_ACTIVE)
            ->ordered()
            ->get(['id', 'name', 'city_id'])
            ->map(fn (Area $area): array => [
                'id' => $area->id,
                'name' => $area->name,
                'city_id' => $area->city_id,
            ])
            ->all();
    }

    /**
     * Get active subjects.
     *
     * @return array<int, array{id: int, name: string, class_id: int}>
     */
    private function activeSubjects(): array
    {
        return Subject::query()
            ->where('status', Subject::STATUS_ACTIVE)
            ->ordered()
            ->get(['id', 'name', 'class_id'])
            ->map(fn (Subject $subject): array => [
                'id' => $subject->id,
                'name' => $subject->name,
                'class_id' => $subject->class_id,
            ])
            ->all();
    }

    /**
     * Get status options for guardian list.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return [
            ['value' => TuitionJob::STATUS_PENDING, 'label' => 'Pending'],
            ['value' => TuitionJob::STATUS_LIVE, 'label' => 'Live'],
            ['value' => TuitionJob::STATUS_CONFIRMED, 'label' => 'Confirmed'],
            ['value' => TuitionJob::STATUS_CANCELLED, 'label' => 'Cancelled'],
            ['value' => TuitionJob::STATUS_CLOSED, 'label' => 'Closed'],
        ];
    }

    /**
     * Get gender options.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function genderOptions(): array
    {
        return [
            ['value' => TuitionJob::GENDER_ANY, 'label' => 'Any'],
            ['value' => TuitionJob::GENDER_MALE, 'label' => 'Male'],
            ['value' => TuitionJob::GENDER_FEMALE, 'label' => 'Female'],
        ];
    }

    /**
     * Get selectable tuition days.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function dayOptions(): array
    {
        return [
            ['value' => 'sun', 'label' => 'Sunday'],
            ['value' => 'mon', 'label' => 'Monday'],
            ['value' => 'tue', 'label' => 'Tuesday'],
            ['value' => 'wed', 'label' => 'Wednesday'],
            ['value' => 'thu', 'label' => 'Thursday'],
            ['value' => 'fri', 'label' => 'Friday'],
            ['value' => 'sat', 'label' => 'Saturday'],
        ];
    }
}
