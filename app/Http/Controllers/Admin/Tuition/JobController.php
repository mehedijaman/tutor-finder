<?php

namespace App\Http\Controllers\Admin\Tuition;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tuition\JobLifecycleRequest;
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class JobController extends Controller
{
    /**
     * Display jobs list for admin.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $search = trim($request->string('q')->toString());
        $status = strtolower(trim($request->string('status')->toString()));
        $guardianId = (int) $request->integer('guardian_id');

        if (! in_array($status, TuitionJob::statuses(), true)) {
            $status = '';
        }

        if ($guardianId <= 0) {
            $guardianId = 0;
        }

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
            ->when($showTrash, fn ($builder) => $builder->onlyTrashed())
            ->when($search !== '', function ($builder) use ($search): void {
                $builder->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhereHas('guardian', fn ($guardianQuery) => $guardianQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('city', fn ($cityQuery) => $cityQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->when($guardianId > 0, fn ($builder) => $builder->where('guardian_id', $guardianId))
            ->latest('id')
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

        return inertia('admin/tuition/jobs/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'q' => $search,
                'status' => $status,
                'guardian_id' => $guardianId > 0 ? $guardianId : null,
            ],
            'counts' => [
                'active' => TuitionJob::query()->count(),
                'trash' => TuitionJob::query()->onlyTrashed()->count(),
                'pending' => TuitionJob::query()->where('status', TuitionJob::STATUS_PENDING)->count(),
                'live' => TuitionJob::query()->where('status', TuitionJob::STATUS_LIVE)->count(),
            ],
            'statusOptions' => $this->statusOptions(),
            'guardianOptions' => $this->activeGuardians(),
        ]);
    }

    /**
     * Show create page.
     */
    public function create(): Response
    {
        return inertia('admin/tuition/jobs/Create', [
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
            ->route('admin.tuition.jobs.index')
            ->with('status', 'Job created successfully.');
    }

    /**
     * Show edit page.
     */
    public function edit(TuitionJob $tuitionJob): Response
    {
        $tuitionJob->load('subjects:id');

        return inertia('admin/tuition/jobs/Edit', [
            'job' => $this->toFormPayload($tuitionJob),
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
    public function update(JobUpdateRequest $request, TuitionJob $tuitionJob, SlugService $slugService): RedirectResponse
    {
        $validated = $request->validated();
        $adminId = $request->user()?->getAuthIdentifier();

        $title = trim((string) $validated['title']);
        $slugBase = trim((string) ($validated['slug'] ?: $title));
        $tuitionDays = $validated['tuition_days'] ?? [];
        $status = (string) $validated['status'];

        DB::transaction(function () use ($validated, $tuitionJob, $title, $slugBase, $tuitionDays, $status, $adminId, $slugService): void {
            $tuitionJob->forceFill([
                'title' => $title,
                'slug' => $slugService->unique(TuitionJob::class, $slugBase, $tuitionJob->id),
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
                'cancellation_reason' => $status === TuitionJob::STATUS_CANCELLED ? $tuitionJob->cancellation_reason : null,
                'published_at' => $this->normalizePublishedAt($status, $validated['published_at'] ?? $tuitionJob->published_at),
                'expires_at' => $validated['expires_at'] ?? null,
                'updated_by' => $adminId,
            ])->save();

            $tuitionJob->subjects()->sync($validated['subject_ids'] ?? []);
        });

        return redirect()
            ->route('admin.tuition.jobs.index')
            ->with('status', 'Job updated successfully.');
    }

    /**
     * Approve pending job.
     */
    public function approve(TuitionJob $tuitionJob, Request $request): RedirectResponse
    {
        try {
            $tuitionJob->markLive($request->user());
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors(['job' => $exception->getMessage()]);
        }

        return redirect()->back()->with('status', 'Job approved and marked as live.');
    }

    /**
     * Cancel pending or live job.
     */
    public function cancel(JobLifecycleRequest $request, TuitionJob $tuitionJob): RedirectResponse
    {
        $reason = $request->validated('reason') ?: 'Cancelled by admin.';

        try {
            $tuitionJob->markCancelled($reason, $request->user());
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors(['job' => $exception->getMessage()]);
        }

        return redirect()->back()->with('status', 'Job cancelled successfully.');
    }

    /**
     * Close live or confirmed job.
     */
    public function close(JobLifecycleRequest $request, TuitionJob $tuitionJob): RedirectResponse
    {
        try {
            $tuitionJob->markClosed($request->user());
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors(['job' => $exception->getMessage()]);
        }

        return redirect()->back()->with('status', 'Job closed successfully.');
    }

    /**
     * Soft delete job.
     */
    public function destroy(TuitionJob $tuitionJob): RedirectResponse
    {
        $tuitionJob->delete();

        return redirect()->back()->with('status', 'Job moved to recycle bin.');
    }

    /**
     * Restore trashed job.
     */
    public function restore(TuitionJob $tuitionJob): RedirectResponse
    {
        if (! $tuitionJob->trashed()) {
            return redirect()->back()->withErrors(['job' => 'Only trashed jobs can be restored.']);
        }

        $tuitionJob->restore();

        return redirect()->back()->with('status', 'Job restored successfully.');
    }

    /**
     * Permanently delete trashed job.
     */
    public function forceDelete(TuitionJob $tuitionJob): RedirectResponse
    {
        if (! $tuitionJob->trashed()) {
            return redirect()->back()->withErrors(['job' => 'Only trashed jobs can be permanently deleted.']);
        }

        DB::transaction(function () use ($tuitionJob): void {
            $tuitionJob->subjects()->detach();
            $tuitionJob->forceDelete();
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
    private function toFormPayload(TuitionJob $tuitionJob): array
    {
        return [
            'id' => $tuitionJob->id,
            'title' => $tuitionJob->title,
            'slug' => $tuitionJob->slug,
            'description' => $tuitionJob->description,
            'tuition_type_id' => $tuitionJob->tuition_type_id,
            'category_id' => $tuitionJob->category_id,
            'class_id' => $tuitionJob->class_id,
            'country_id' => $tuitionJob->country_id,
            'city_id' => $tuitionJob->city_id,
            'area_id' => $tuitionJob->area_id,
            'guardian_id' => $tuitionJob->guardian_id,
            'location' => $tuitionJob->location,
            'latitude' => $tuitionJob->latitude,
            'longitude' => $tuitionJob->longitude,
            'student_gender' => $tuitionJob->student_gender,
            'tutor_gender' => $tuitionJob->tutor_gender,
            'tuition_days' => $tuitionJob->tuition_days ?? [],
            'tuition_time' => $tuitionJob->tuition_time,
            'tuition_duration' => $tuitionJob->tuition_duration,
            'no_of_students' => $tuitionJob->no_of_students,
            'salary_amount' => $tuitionJob->salary_amount,
            'salary_currency' => $tuitionJob->salary_currency,
            'salary_negotiable' => $tuitionJob->salary_negotiable,
            'status' => $tuitionJob->status,
            'expires_at' => $tuitionJob->expires_at?->format('Y-m-d\\TH:i'),
            'published_at' => $tuitionJob->published_at?->format('Y-m-d\\TH:i'),
            'subject_ids' => $tuitionJob->subjects->pluck('id')->all(),
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
