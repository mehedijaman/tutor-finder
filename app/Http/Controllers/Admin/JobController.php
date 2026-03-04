<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\JobStatusUpdateRequest;
use App\Http\Requests\Admin\Tuition\JobStoreRequest;
use App\Http\Requests\Admin\Tuition\JobUpdateRequest;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Services\Job\JobFormOptionService;
use App\Services\Job\JobLifecycleService;
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
    private const FILTER_EXPIRED = 'expired';

    public function __construct(
        private readonly JobFormOptionService $formOptionService,
        private readonly JobLifecycleService $lifecycleService,
    ) {}

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
        $filterExpired = $effectiveStatus === self::FILTER_EXPIRED;

        $items = TuitionJob::query()
            ->with([
                'guardian:id,name',
                'country:id,name',
                'city:id,name',
                'area:id,name',
                'category:id,name',
                'schoolClass:id,name',
                'subjects:id,name',
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
            ->when($showTrash, fn (Builder $builder): Builder => $builder->onlyTrashed())
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $subQuery) use ($search): void {
                    $subQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhereHas('guardian', fn (Builder $guardianQuery): Builder => $guardianQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($effectiveStatus !== '' && ! $filterExpired, fn (Builder $builder): Builder => $builder->where('status', $effectiveStatus))
            ->when($filterExpired, fn (Builder $builder): Builder => $builder
                ->where('status', JobStatus::Live)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()))
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
                'applications_count' => $job->applications_count,
                'open_applications_count' => $job->open_applications_count,
                'has_assignment' => $job->assignment !== null,
                'selected_tutor_user_id' => $job->assignment?->tutor_user_id,
                'selected_tutor_name' => $job->assignment?->tutor?->name,
                'assignment_appointed_at' => $job->assignment?->appointed_at?->toDateTimeString(),
                'assignment_confirmed_at' => $job->assignment?->confirmed_at?->toDateTimeString(),
                'is_expired' => $job->status === JobStatus::Live && $job->isExpired(),
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
                'pending_count' => TuitionJob::query()->where('status', JobStatus::Pending)->count(),
                'live_count' => TuitionJob::query()->where('status', JobStatus::Live)->count(),
                'expired_count' => TuitionJob::query()
                    ->where('status', JobStatus::Live)
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now())
                    ->count(),
                'confirmed_count' => TuitionJob::query()->where('status', JobStatus::Confirmed)->count(),
                'cancelled_count' => TuitionJob::query()->where('status', JobStatus::Cancelled)->count(),
                'total_count' => TuitionJob::query()->count(),
                'trash_count' => TuitionJob::query()->onlyTrashed()->count(),
            ],
            'pageTitle' => $this->pageTitle($presetStatus, $showTrash),
            'statusOptions' => $this->statusOptions(),
            'guardianOptions' => $this->formOptionService->activeGuardians(),
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
     * Display expired jobs (computed filter, not a stored status).
     */
    public function expired(Request $request): Response
    {
        return $this->index($request, self::FILTER_EXPIRED);
    }

    /**
     * Display cancelled jobs.
     */
    public function cancelled(Request $request): Response
    {
        return $this->index($request, JobStatus::Cancelled->value);
    }

    /**
     * Display applications and hiring outcome for a specific job.
     */
    public function applications(Request $request, TuitionJob $job): Response
    {
        $status = strtolower(trim($request->string('status')->toString()));

        if (! in_array($status, TuitionJobApplication::statuses(), true)) {
            $status = '';
        }

        $job->loadMissing([
            'guardian:id,name',
            'assignment:id,job_id,tutor_user_id,appointed_at,confirmed_at',
            'assignment.tutor:id,name',
        ]);

        $items = TuitionJobApplication::query()
            ->with(['tutor:id,name,email,phone'])
            ->where('job_id', $job->id)
            ->when($status !== '', fn (Builder $builder): Builder => $builder->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (TuitionJobApplication $application): array => [
                'id' => $application->id,
                'status' => $application->status,
                'cover_letter' => $application->cover_letter,
                'expected_salary_amount' => $application->expected_salary_amount,
                'salary_currency' => $application->salary_currency,
                'cancel_reason' => $application->cancel_reason,
                'created_at' => $application->created_at?->toDateTimeString(),
                'is_selected' => (int) $job->assignment?->tutor_user_id === (int) $application->tutor_user_id,
                'tutor' => [
                    'id' => $application->tutor?->id,
                    'name' => $application->tutor?->name,
                    'email' => $application->tutor?->email,
                    'phone' => $application->tutor?->phone,
                ],
            ]);

        return inertia('admin/jobs/Applications', [
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'status' => $job->status,
                'guardian_name' => $job->guardian?->name,
                'is_expired' => $job->status === JobStatus::Live && $job->isExpired(),
                'has_assignment' => $job->assignment !== null,
                'selected_tutor_user_id' => $job->assignment?->tutor_user_id,
                'selected_tutor_name' => $job->assignment?->tutor?->name,
                'assignment_appointed_at' => $job->assignment?->appointed_at?->toDateTimeString(),
                'assignment_confirmed_at' => $job->assignment?->confirmed_at?->toDateTimeString(),
            ],
            'items' => $items,
            'filters' => [
                'status' => $status,
            ],
            'statusOptions' => [
                ['value' => ApplicationStatus::Applied, 'label' => 'Applied'],
                ['value' => ApplicationStatus::Shortlisted, 'label' => 'Shortlisted'],
                ['value' => ApplicationStatus::Appointed, 'label' => 'Appointed'],
                ['value' => ApplicationStatus::Confirmed, 'label' => 'Confirmed'],
                ['value' => ApplicationStatus::Cancelled, 'label' => 'Cancelled'],
            ],
        ]);
    }

    /**
     * Show create page.
     */
    public function create(): Response
    {
        return inertia('admin/jobs/Create', [
            'tuitionTypes' => $this->formOptionService->activeTuitionTypes(),
            'categories' => $this->formOptionService->activeCategories(),
            'schoolClasses' => $this->formOptionService->activeSchoolClasses(),
            'countries' => $this->formOptionService->activeCountries(),
            'cities' => $this->formOptionService->activeCities(),
            'areas' => $this->formOptionService->activeAreas(),
            'subjects' => $this->formOptionService->activeSubjects(),
            'guardians' => $this->formOptionService->activeGuardians(),
            'statusOptions' => $this->createStatusOptions(),
            'genderOptions' => $this->formOptionService->genderOptions(),
            'dayOptions' => $this->formOptionService->dayOptions(),
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
            'tuitionTypes' => $this->formOptionService->activeTuitionTypes(),
            'categories' => $this->formOptionService->activeCategories(),
            'schoolClasses' => $this->formOptionService->activeSchoolClasses(),
            'countries' => $this->formOptionService->activeCountries(),
            'cities' => $this->formOptionService->activeCities(),
            'areas' => $this->formOptionService->activeAreas(),
            'subjects' => $this->formOptionService->activeSubjects(),
            'guardians' => $this->formOptionService->activeGuardians(),
            'statusOptions' => $this->createStatusOptions(),
            'genderOptions' => $this->formOptionService->genderOptions(),
            'dayOptions' => $this->formOptionService->dayOptions(),
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
                'cancellation_reason' => $status === JobStatus::Cancelled->value ? $job->cancellation_reason : null,
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
        $reason = $request->validated('reason');

        try {
            $this->lifecycleService->transitionStatus($job, $targetStatus, $request->user(), $reason);
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
        try {
            $this->lifecycleService->forceDeleteJob($job);
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors(['job' => $exception->getMessage()]);
        }

        return redirect()->back()->with('status', 'Job permanently deleted.');
    }

    /**
     * Empty recycle bin jobs.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $count = $this->lifecycleService->emptyRecycleBin();

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
        if ($status !== JobStatus::Live->value) {
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
     * Normalize status string.
     */
    private function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        if ($normalized === self::FILTER_EXPIRED) {
            return $normalized;
        }

        if (JobStatus::tryFrom($normalized) === null) {
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
            JobStatus::Pending->value => 'Pending Jobs',
            JobStatus::Live->value => 'Live Jobs',
            self::FILTER_EXPIRED => 'Expired Jobs',
            JobStatus::Confirmed->value => 'Confirmed Jobs',
            JobStatus::Cancelled->value => 'Cancelled Jobs',
            default => 'All Jobs',
        };
    }

    /**
     * Get status options for index filter.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return [
            ['value' => JobStatus::Pending, 'label' => 'Pending'],
            ['value' => JobStatus::Live, 'label' => 'Live'],
            ['value' => self::FILTER_EXPIRED, 'label' => 'Expired (Live + Past Expiry)'],
            ['value' => JobStatus::Confirmed, 'label' => 'Confirmed'],
            ['value' => JobStatus::Cancelled, 'label' => 'Cancelled'],
            ['value' => JobStatus::Closed, 'label' => 'Closed'],
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
            ['value' => JobStatus::Pending, 'label' => 'Pending'],
            ['value' => JobStatus::Live, 'label' => 'Live'],
        ];
    }
}
