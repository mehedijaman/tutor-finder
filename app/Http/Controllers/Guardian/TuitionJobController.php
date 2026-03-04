<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\Tuition\JobStoreRequest;
use App\Models\TuitionJob;
use App\Services\Job\JobFormOptionService;
use App\Support\SlugService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class TuitionJobController extends Controller
{
    public function __construct(
        private readonly JobFormOptionService $formOptionService,
    ) {}

    /**
     * Display guardian jobs list.
     */
    public function index(Request $request, ?string $status = null): Response
    {
        $user = $request->user();
        $search = trim($request->string('q')->toString());
        $presetStatus = $this->normalizeStatus((string) $status);
        $queryStatus = $this->normalizeStatus(trim($request->string('status')->toString()));
        $effectiveStatus = $presetStatus ?: $queryStatus;

        $items = TuitionJob::query()
            ->with([
                'city:id,name',
                'area:id,name',
                'category:id,name',
                'schoolClass:id,name',
                'assignment:id,job_id,tutor_user_id,appointed_at,confirmed_at',
                'assignment.tutor:id,name',
            ])
            ->withCount('applications')
            ->withCount([
                'applications as open_applications_count' => fn (Builder $builder): Builder => $builder->whereIn('status', [
                    ApplicationStatus::Applied,
                    ApplicationStatus::Shortlisted,
                ]),
            ])
            ->where('guardian_id', $user?->getAuthIdentifier())
            ->when($search !== '', function ($builder) use ($search): void {
                $builder->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($effectiveStatus !== '', fn ($builder) => $builder->where('status', $effectiveStatus))
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
                'open_applications_count' => $job->open_applications_count,
                'has_assignment' => $job->assignment !== null,
                'selected_tutor_name' => $job->assignment?->tutor?->name,
                'hiring_confirmed_at' => $job->assignment?->confirmed_at?->toDateTimeString(),
                'is_expired' => $job->status === JobStatus::Live && $job->isExpired(),
                'published_at' => $job->published_at?->toDateTimeString(),
                'expires_at' => $job->expires_at?->toDateTimeString(),
                'updated_at' => $job->updated_at?->toDateTimeString(),
            ]);

        return inertia('guardian/jobs/Index', [
            'items' => $items,
            'filters' => [
                'q' => $search,
                'status' => $queryStatus,
                'preset_status' => $presetStatus,
            ],
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Display pending jobs.
     */
    public function pending(Request $request): Response
    {
        return $this->index($request, JobStatus::Pending->value);
    }

    /**
     * Display live jobs.
     */
    public function live(Request $request): Response
    {
        return $this->index($request, JobStatus::Live->value);
    }

    /**
     * Display confirmed jobs.
     */
    public function confirmed(Request $request): Response
    {
        return $this->index($request, JobStatus::Confirmed->value);
    }

    /**
     * Display cancelled jobs.
     */
    public function cancelled(Request $request): Response
    {
        return $this->index($request, JobStatus::Cancelled->value);
    }

    /**
     * Display closed jobs.
     */
    public function closed(Request $request): Response
    {
        return $this->index($request, JobStatus::Closed->value);
    }

    /**
     * Show create job page for guardian.
     */
    public function create(): Response
    {
        return inertia('guardian/jobs/Create', [
            'tuitionTypes' => $this->formOptionService->activeTuitionTypes(),
            'categories' => $this->formOptionService->activeCategories(),
            'schoolClasses' => $this->formOptionService->activeSchoolClasses(),
            'countries' => $this->formOptionService->activeCountries(),
            'cities' => $this->formOptionService->activeCities(),
            'areas' => $this->formOptionService->activeAreas(),
            'subjects' => $this->formOptionService->activeSubjects(),
            'genderOptions' => $this->formOptionService->genderOptions(),
            'dayOptions' => $this->formOptionService->dayOptions(),
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
                'status' => JobStatus::Pending,
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
     * Get status options for guardian list.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return [
            ['value' => JobStatus::Pending, 'label' => 'Pending'],
            ['value' => JobStatus::Live, 'label' => 'Live'],
            ['value' => JobStatus::Confirmed, 'label' => 'Confirmed'],
            ['value' => JobStatus::Cancelled, 'label' => 'Cancelled'],
            ['value' => JobStatus::Closed, 'label' => 'Closed'],
        ];
    }

    /**
     * Normalize query or preset status.
     */
    private function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        if (JobStatus::tryFrom($normalized) === null) {
            return '';
        }

        return $normalized;
    }
}
