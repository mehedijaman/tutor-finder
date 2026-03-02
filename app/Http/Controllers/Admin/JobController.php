<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\JobStatusUpdateRequest;
use App\Http\Requests\Admin\Tuition\JobStoreRequest;
use App\Http\Requests\Admin\Tuition\JobUpdateRequest;
use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionJob;
use App\Models\TuitionType;
use App\Models\User;
use App\Support\SlugService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class JobController extends Controller
{
    /**
     * @var array<string, list<string>>
     */
    private const TRANSITIONS = [
        TuitionJob::STATUS_PENDING => [
            TuitionJob::STATUS_LIVE,
            TuitionJob::STATUS_CANCELLED,
        ],
        TuitionJob::STATUS_LIVE => [
            TuitionJob::STATUS_CONFIRMED,
            TuitionJob::STATUS_CANCELLED,
            TuitionJob::STATUS_CLOSED,
        ],
        TuitionJob::STATUS_CONFIRMED => [
            TuitionJob::STATUS_CLOSED,
        ],
        TuitionJob::STATUS_CANCELLED => [],
        TuitionJob::STATUS_CLOSED => [],
    ];

    /**
     * Display jobs list for admin.
     */
    public function index(Request $request, ?string $status = null): Response
    {
        $presetStatus = $this->normalizeStatus((string) $status);
        $showTrash = $request->boolean('trash');
        $search = trim($request->string('q')->toString());
        $queryStatus = $this->normalizeStatus(trim($request->string('status')->toString()));
        $guardianId = (int) $request->integer('guardian_id');
        $sort = trim($request->string('sort')->toString());
        $direction = strtolower(trim($request->string('direction')->toString()));

        if ($guardianId <= 0) {
            $guardianId = 0;
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $sortMap = [
            'title' => 'title',
            'slug' => 'slug',
            'status' => 'status',
            'published_at' => 'published_at',
            'expires_at' => 'expires_at',
            'updated_at' => 'updated_at',
            'created_at' => 'created_at',
        ];

        $sortColumn = $sortMap[$sort] ?? 'updated_at';
        $effectiveStatus = $presetStatus ?: $queryStatus;

        $items = TuitionJob::query()
            ->with([
                'guardian:id,name',
                'country:id,name',
                'city:id,name',
                'area:id,name',
                'category:id,name',
                'schoolClass:id,name',
                'subjects:id,name',
            ])
            ->when($showTrash, fn (Builder $builder): Builder => $builder->onlyTrashed())
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $subQuery) use ($search): void {
                    $subQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhereHas('guardian', fn (Builder $guardianQuery): Builder => $guardianQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($effectiveStatus !== '', fn (Builder $builder): Builder => $builder->where('status', $effectiveStatus))
            ->when($guardianId > 0, fn (Builder $builder): Builder => $builder->where('guardian_id', $guardianId))
            ->orderBy($sortColumn, $direction)
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (TuitionJob $job): array => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'status' => $job->status,
                'guardian_id' => $job->guardian_id,
                'guardian_name' => $job->guardian?->name,
                'category_name' => $job->category?->name,
                'class_name' => $job->schoolClass?->name,
                'country_name' => $job->country?->name,
                'city_name' => $job->city?->name,
                'area_name' => $job->area?->name,
                'subject_names' => $job->subjects->pluck('name')->values()->all(),
                'published_at' => $job->published_at?->toDateTimeString(),
                'expires_at' => $job->expires_at?->toDateTimeString(),
                'updated_at' => $job->updated_at?->toDateTimeString(),
                'deleted_at' => $job->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/jobs/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'q' => $search,
                'status' => $queryStatus,
                'preset_status' => $presetStatus,
                'guardian_id' => $guardianId > 0 ? $guardianId : null,
                'sort' => $sortColumn,
                'direction' => $direction,
            ],
            'counts' => [
                'pending_count' => TuitionJob::query()->where('status', TuitionJob::STATUS_PENDING)->count(),
                'live_count' => TuitionJob::query()->where('status', TuitionJob::STATUS_LIVE)->count(),
                'confirmed_count' => TuitionJob::query()->where('status', TuitionJob::STATUS_CONFIRMED)->count(),
                'cancelled_count' => TuitionJob::query()->where('status', TuitionJob::STATUS_CANCELLED)->count(),
                'total_count' => TuitionJob::query()->count(),
                'trash_count' => TuitionJob::query()->onlyTrashed()->count(),
            ],
            'pageTitle' => $this->pageTitle($presetStatus, $showTrash),
            'statusOptions' => $this->statusOptions(),
            'guardianOptions' => $this->activeGuardians(),
        ]);
    }

    /**
     * Display pending jobs.
     */
    public function pending(Request $request): Response
    {
        return $this->index($request, TuitionJob::STATUS_PENDING);
    }

    /**
     * Display live jobs.
     */
    public function live(Request $request): Response
    {
        return $this->index($request, TuitionJob::STATUS_LIVE);
    }

    /**
     * Display confirmed jobs.
     */
    public function confirmed(Request $request): Response
    {
        return $this->index($request, TuitionJob::STATUS_CONFIRMED);
    }

    /**
     * Display cancelled jobs.
     */
    public function cancelled(Request $request): Response
    {
        return $this->index($request, TuitionJob::STATUS_CANCELLED);
    }

    /**
     * Show create page.
     */
    public function create(): Response
    {
        return inertia('admin/jobs/Create', [
            'tuitionTypes' => $this->activeTuitionTypes(),
            'categories' => $this->activeCategories(),
            'schoolClasses' => $this->activeSchoolClasses(),
            'countries' => $this->activeCountries(),
            'cities' => $this->activeCities(),
            'areas' => $this->activeAreas(),
            'subjects' => $this->activeSubjects(),
            'guardians' => $this->activeGuardians(),
            'statusOptions' => $this->createStatusOptions(),
            'genderOptions' => $this->genderOptions(),
            'dayOptions' => $this->dayOptions(),
        ]);
    }

    /**
     * Store a new job by admin.
     */
    public function store(JobStoreRequest $request, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $adminId = $request->user()?->getAuthIdentifier();

        $title = trim((string) $validated['title']);
        $slugBase = trim((string) ($validated['slug'] ?: $title));
        $tuitionDays = $validated['tuition_days'] ?? [];
        $status = (string) $validated['status'];

        DB::transaction(function () use ($validated, $title, $slugBase, $tuitionDays, $status, $adminId, $slugService): void {
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
                'guardian_id' => (int) $validated['guardian_id'],
                'location' => $validated['location'] ?: null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'student_gender' => (string) $validated['student_gender'],
                'tutor_gender' => (string) $validated['tutor_gender'],
                'tuition_days' => $tuitionDays,
                'days_per_week' => count($tuitionDays) > 0 ? count($tuitionDays) : null,
                'tuition_time' => $validated['tuition_time'] ?: null,
                'tuition_duration' => $validated['tuition_duration'] ?: null,
                'no_of_students' => $validated['no_of_students'] ?? null,
                'salary_amount' => $validated['salary_amount'] ?? null,
                'salary_currency' => $validated['salary_currency'] ?: 'BDT',
                'salary_negotiable' => (bool) $validated['salary_negotiable'],
                'status' => $status,
                'cancellation_reason' => null,
                'published_at' => $this->normalizePublishedAt($status, $validated['published_at'] ?? null),
                'expires_at' => $validated['expires_at'] ?? null,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'confirmed_by' => null,
                'confirmed_at' => null,
            ]);

            $job->subjects()->sync($validated['subject_ids'] ?? []);
        });

        return redirect()
            ->route('admin.jobs.index')
            ->with('status', 'Job created successfully.');
    }

    /**
     * Show edit page.
     */
    public function edit(TuitionJob $job): Response
    {
        $job->load('subjects:id');

        return inertia('admin/jobs/Edit', [
            'job' => $this->toFormPayload($job),
            'tuitionTypes' => $this->activeTuitionTypes(),
            'categories' => $this->activeCategories(),
            'schoolClasses' => $this->activeSchoolClasses(),
            'countries' => $this->activeCountries(),
            'cities' => $this->activeCities(),
            'areas' => $this->activeAreas(),
            'subjects' => $this->activeSubjects(),
            'guardians' => $this->activeGuardians(),
            'statusOptions' => $this->createStatusOptions(),
            'genderOptions' => $this->genderOptions(),
            'dayOptions' => $this->dayOptions(),
        ]);
    }

    /**
     * Update a job.
     */
    public function update(JobUpdateRequest $request, TuitionJob $job, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $adminId = $request->user()?->getAuthIdentifier();

        $title = trim((string) $validated['title']);
        $slugBase = trim((string) ($validated['slug'] ?: $title));
        $tuitionDays = $validated['tuition_days'] ?? [];
        $status = (string) $validated['status'];

        DB::transaction(function () use ($validated, $job, $title, $slugBase, $tuitionDays, $status, $adminId, $slugService): void {
            $job->forceFill([
                'title' => $title,
                'slug' => $slugService->unique(TuitionJob::class, $slugBase, $job->id),
                'description' => (string) $validated['description'],
                'tuition_type_id' => (int) $validated['tuition_type_id'],
                'category_id' => (int) $validated['category_id'],
                'class_id' => (int) $validated['class_id'],
                'country_id' => (int) $validated['country_id'],
                'city_id' => (int) $validated['city_id'],
                'area_id' => $validated['area_id'] ?? null,
                'guardian_id' => (int) $validated['guardian_id'],
                'location' => $validated['location'] ?: null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'student_gender' => (string) $validated['student_gender'],
                'tutor_gender' => (string) $validated['tutor_gender'],
                'tuition_days' => $tuitionDays,
                'days_per_week' => count($tuitionDays) > 0 ? count($tuitionDays) : null,
                'tuition_time' => $validated['tuition_time'] ?: null,
                'tuition_duration' => $validated['tuition_duration'] ?: null,
                'no_of_students' => $validated['no_of_students'] ?? null,
                'salary_amount' => $validated['salary_amount'] ?? null,
                'salary_currency' => $validated['salary_currency'] ?: 'BDT',
                'salary_negotiable' => (bool) $validated['salary_negotiable'],
                'status' => $status,
                'cancellation_reason' => $status === TuitionJob::STATUS_CANCELLED ? $job->cancellation_reason : null,
                'published_at' => $this->normalizePublishedAt($status, $validated['published_at'] ?? $job->published_at),
                'expires_at' => $validated['expires_at'] ?? null,
                'updated_by' => $adminId,
            ])->save();

            $job->subjects()->sync($validated['subject_ids'] ?? []);
        });

        return redirect()
            ->route('admin.jobs.index')
            ->with('status', 'Job updated successfully.');
    }

    /**
     * Approve pending job.
     */
    public function approve(TuitionJob $job, Request $request): RedirectResponse
    {
        try {
            $job->markLive($request->user());
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors(['job' => $exception->getMessage()]);
        }

        return redirect()->back()->with('status', 'Job approved and marked as live.');
    }

    /**
     * Update job status by transition matrix.
     */
    public function status(JobStatusUpdateRequest $request, TuitionJob $job): RedirectResponse
    {
        $targetStatus = (string) $request->validated('status');

        if ($targetStatus === $job->status) {
            return redirect()->back()->withErrors(['job' => 'The job is already in the selected status.']);
        }

        if (! $this->canTransition($job->status, $targetStatus)) {
            return redirect()->back()->withErrors(['job' => "Invalid status transition from {$job->status} to {$targetStatus}."]);
        }

        try {
            if ($targetStatus === TuitionJob::STATUS_LIVE) {
                $job->markLive($request->user());
            }

            if ($targetStatus === TuitionJob::STATUS_CONFIRMED) {
                $job->markConfirmed($request->user());
            }

            if ($targetStatus === TuitionJob::STATUS_CANCELLED) {
                $reason = $request->validated('reason') ?: 'Cancelled by admin.';
                $job->markCancelled($reason, $request->user());
            }

            if ($targetStatus === TuitionJob::STATUS_CLOSED) {
                $job->markClosed($request->user());
            }
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors(['job' => $exception->getMessage()]);
        }

        return redirect()->back()->with('status', 'Job status updated successfully.');
    }

    /**
     * Soft delete job.
     */
    public function destroy(TuitionJob $job): RedirectResponse
    {
        $job->delete();

        return redirect()->back()->with('status', 'Job moved to recycle bin.');
    }

    /**
     * Restore trashed job.
     */
    public function restore(TuitionJob $job): RedirectResponse
    {
        if (! $job->trashed()) {
            return redirect()->back()->withErrors(['job' => 'Only trashed jobs can be restored.']);
        }

        $job->restore();

        return redirect()->back()->with('status', 'Job restored successfully.');
    }

    /**
     * Permanently delete trashed job.
     */
    public function forceDelete(TuitionJob $job): RedirectResponse
    {
        if (! $job->trashed()) {
            return redirect()->back()->withErrors(['job' => 'Only trashed jobs can be permanently deleted.']);
        }

        DB::transaction(function () use ($job): void {
            $job->subjects()->detach();
            $job->forceDelete();
        });

        return redirect()->back()->with('status', 'Job permanently deleted.');
    }

    /**
     * Empty recycle bin jobs.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $count = 0;

        DB::transaction(function () use (&$count): void {
            TuitionJob::query()
                ->onlyTrashed()
                ->get()
                ->each(function (TuitionJob $job) use (&$count): void {
                    $job->subjects()->detach();
                    $job->forceDelete();
                    $count++;
                });
        });

        return redirect()->back()->with('status', "Deleted {$count} job(s) from recycle bin.");
    }

    /**
     * Convert job model for form page.
     *
     * @return array<string, mixed>
     */
    private function toFormPayload(TuitionJob $job): array
    {
        return [
            'id' => $job->id,
            'title' => $job->title,
            'slug' => $job->slug,
            'description' => $job->description,
            'tuition_type_id' => $job->tuition_type_id,
            'category_id' => $job->category_id,
            'class_id' => $job->class_id,
            'country_id' => $job->country_id,
            'city_id' => $job->city_id,
            'area_id' => $job->area_id,
            'guardian_id' => $job->guardian_id,
            'location' => $job->location,
            'latitude' => $job->latitude,
            'longitude' => $job->longitude,
            'student_gender' => $job->student_gender,
            'tutor_gender' => $job->tutor_gender,
            'tuition_days' => $job->tuition_days ?? [],
            'tuition_time' => $job->tuition_time,
            'tuition_duration' => $job->tuition_duration,
            'no_of_students' => $job->no_of_students,
            'salary_amount' => $job->salary_amount,
            'salary_currency' => $job->salary_currency,
            'salary_negotiable' => $job->salary_negotiable,
            'status' => $job->status,
            'expires_at' => $job->expires_at?->format('Y-m-d\\TH:i'),
            'published_at' => $job->published_at?->format('Y-m-d\\TH:i'),
            'subject_ids' => $job->subjects->pluck('id')->all(),
        ];
    }

    /**
     * Normalize published at based on status.
     */
    private function normalizePublishedAt(string $status, mixed $publishedAt): ?Carbon
    {
        if ($status !== TuitionJob::STATUS_LIVE) {
            return null;
        }

        if ($publishedAt instanceof Carbon) {
            return $publishedAt;
        }

        if (is_string($publishedAt) && trim($publishedAt) !== '') {
            return Carbon::parse($publishedAt);
        }

        return now();
    }

    /**
     * Determine if a status can move to another status.
     */
    private function canTransition(string $currentStatus, string $targetStatus): bool
    {
        $allowedStatuses = self::TRANSITIONS[$currentStatus] ?? [];

        return in_array($targetStatus, $allowedStatuses, true);
    }

    /**
     * Normalize status string.
     */
    private function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        if (! in_array($normalized, TuitionJob::statuses(), true)) {
            return '';
        }

        return $normalized;
    }

    /**
     * Resolve page title.
     */
    private function pageTitle(string $presetStatus, bool $showTrash): string
    {
        if ($showTrash) {
            return 'Job Recycle Bin';
        }

        return match ($presetStatus) {
            TuitionJob::STATUS_PENDING => 'Pending Jobs',
            TuitionJob::STATUS_LIVE => 'Live Jobs',
            TuitionJob::STATUS_CONFIRMED => 'Confirmed Jobs',
            TuitionJob::STATUS_CANCELLED => 'Cancelled Jobs',
            default => 'All Jobs',
        };
    }

    /**
     * Get active guardians.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function activeGuardians(): array
    {
        return User::query()
            ->where('role', 'guardian')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $guardian): array => [
                'id' => $guardian->id,
                'name' => $guardian->name,
            ])
            ->all();
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
     * Get status options for index filter.
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
     * Get allowed status options for create/edit form.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function createStatusOptions(): array
    {
        return [
            ['value' => TuitionJob::STATUS_PENDING, 'label' => 'Pending'],
            ['value' => TuitionJob::STATUS_LIVE, 'label' => 'Live'],
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
