<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\City;
use App\Models\Subject;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\TuitionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Response;

class JobController extends Controller
{
    /**
     * Display public job board with filters.
     */
    public function index(Request $request): Response
    {
        $query = trim($request->string('q')->toString());
        $categorySlug = trim($request->string('category')->toString());
        $tuitionTypeSlug = trim($request->string('tuition_type')->toString());
        $subjectId = (int) $request->integer('subject_id');
        $cityId = (int) $request->integer('city_id');
        $tutorGender = trim($request->string('tutor_gender')->toString());
        $minSalary = $request->filled('min_salary') ? (float) $request->input('min_salary') : null;
        $maxSalary = $request->filled('max_salary') ? (float) $request->input('max_salary') : null;
        $daysPerWeek = $request->filled('days_per_week') ? (int) $request->input('days_per_week') : null;
        $sort = trim($request->string('sort')->toString());

        if ($subjectId <= 0) {
            $subjectId = 0;
        }

        if ($cityId <= 0) {
            $cityId = 0;
        }

        if (! in_array($sort, ['newest', 'salary_high', 'salary_low'], true)) {
            $sort = 'newest';
        }

        $jobsQuery = $this->publicJobsQuery()
            ->with([
                'category:id,name,slug',
                'schoolClass:id,name',
                'subjects:id,name',
                'tuitionType:id,name,slug',
                'country:id,name',
                'city:id,name',
                'area:id,name',
            ])
            ->when($query !== '', function (Builder $builder) use ($query): void {
                $builder->where(function (Builder $nested) use ($query): void {
                    $nested
                        ->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('location', 'like', "%{$query}%")
                        ->orWhereHas('city', fn (Builder $cityQuery) => $cityQuery->where('name', 'like', "%{$query}%"));
                });
            })
            ->when($categorySlug !== '', function (Builder $builder) use ($categorySlug): void {
                $builder->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $categorySlug));
            })
            ->when($tuitionTypeSlug !== '', function (Builder $builder) use ($tuitionTypeSlug): void {
                $builder->whereHas('tuitionType', fn (Builder $typeQuery) => $typeQuery->where('slug', $tuitionTypeSlug));
            })
            ->when($subjectId > 0, function (Builder $builder) use ($subjectId): void {
                $builder->whereHas('subjects', fn (Builder $subjectQuery) => $subjectQuery->whereKey($subjectId));
            })
            ->when($cityId > 0, fn (Builder $builder) => $builder->where('city_id', $cityId))
            ->when($tutorGender !== '' && $tutorGender !== 'any', fn (Builder $builder) => $builder->where('tutor_gender', $tutorGender))
            ->when($daysPerWeek !== null, fn (Builder $builder) => $builder->where('days_per_week', $daysPerWeek))
            ->when($minSalary !== null, fn (Builder $builder) => $builder->where('salary_amount', '>=', $minSalary))
            ->when($maxSalary !== null, fn (Builder $builder) => $builder->where('salary_amount', '<=', $maxSalary));

        $this->applySort($jobsQuery, $sort);

        $jobs = $jobsQuery
            ->paginate(12)
            ->withQueryString()
            ->through(fn (TuitionJob $job): array => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'description' => Str::limit((string) $job->description, 180),
                'salary_amount' => $job->salary_amount,
                'salary_currency' => $job->salary_currency,
                'salary_negotiable' => $job->salary_negotiable,
                'tuition_type_name' => $job->tuitionType?->name,
                'category_name' => $job->category?->name,
                'class_name' => $job->schoolClass?->name,
                'country_name' => $job->country?->name,
                'city_name' => $job->city?->name,
                'area_name' => $job->area?->name,
                'subject_names' => $job->subjects->pluck('name')->values()->all(),
                'student_gender' => $job->student_gender,
                'tutor_gender' => $job->tutor_gender,
                'days_per_week' => $job->days_per_week,
                'tuition_time' => $job->tuition_time,
                'published_at' => $job->published_at?->toDateTimeString(),
                'expires_at' => $job->expires_at?->toDateTimeString(),
            ]);

        return inertia('JobBoard', [
            'jobs' => $jobs,
            'total' => $jobs->total(),
            'filters' => [
                'q' => $query,
                'category' => $categorySlug,
                'tuition_type' => $tuitionTypeSlug,
                'subject_id' => $subjectId > 0 ? $subjectId : null,
                'city_id' => $cityId > 0 ? $cityId : null,
                'tutor_gender' => $tutorGender,
                'days_per_week' => $daysPerWeek,
                'min_salary' => $minSalary,
                'max_salary' => $maxSalary,
                'sort' => $sort,
            ],
            'categoryOptions' => $this->categoryOptions(),
            'tuitionTypeOptions' => $this->tuitionTypeOptions(),
            'subjectOptions' => $this->subjectOptions(),
            'cityOptions' => $this->cityOptions(),
            'sortOptions' => [
                ['value' => 'newest', 'label' => 'Newest'],
                ['value' => 'salary_high', 'label' => 'Highest Salary'],
                ['value' => 'salary_low', 'label' => 'Lowest Salary'],
            ],
            'genderOptions' => [
                ['value' => 'any', 'label' => 'Any'],
                ['value' => 'male', 'label' => 'Male'],
                ['value' => 'female', 'label' => 'Female'],
            ],
            'daysOptions' => [
                ['value' => '1', 'label' => '1 Day/Week'],
                ['value' => '2', 'label' => '2 Days/Week'],
                ['value' => '3', 'label' => '3 Days/Week'],
                ['value' => '4', 'label' => '4 Days/Week'],
                ['value' => '5', 'label' => '5 Days/Week'],
                ['value' => '6', 'label' => '6 Days/Week'],
                ['value' => '7', 'label' => '7 Days/Week'],
            ],
        ]);
    }

    /**
     * Display a single public job by slug.
     */
    public function show(Request $request, string $slug): Response
    {
        $job = $this->publicJobsQuery()
            ->with([
                'category:id,name,slug',
                'schoolClass:id,name',
                'subjects:id,name',
                'tuitionType:id,name,slug',
                'country:id,name',
                'city:id,name',
                'area:id,name',
                'assignment:id,job_id,tutor_user_id',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $user = $request->user();
        $application = null;
        $canApply = false;

        if ($user !== null && $user->role === UserRole::Tutor) {
            $application = TuitionJobApplication::query()
                ->where('job_id', $job->id)
                ->where('tutor_user_id', $user->getAuthIdentifier())
                ->first();

            $canApply = $job->status === JobStatus::Live
                && ! $job->isExpired()
                && $job->assignment === null
                && ($application === null || $application->status === ApplicationStatus::Cancelled)
                && (int) $job->guardian_id !== (int) $user->getAuthIdentifier();
        }

        return inertia('JobShow', [
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'description' => $job->description,
                'salary_amount' => $job->salary_amount,
                'salary_currency' => $job->salary_currency,
                'salary_negotiable' => $job->salary_negotiable,
                'tuition_type_name' => $job->tuitionType?->name,
                'category_name' => $job->category?->name,
                'class_name' => $job->schoolClass?->name,
                'country_name' => $job->country?->name,
                'city_name' => $job->city?->name,
                'area_name' => $job->area?->name,
                'location' => $job->location,
                'subject_names' => $job->subjects->pluck('name')->values()->all(),
                'student_gender' => $job->student_gender,
                'tutor_gender' => $job->tutor_gender,
                'tuition_days' => $job->tuition_days ?? [],
                'days_per_week' => $job->days_per_week,
                'tuition_time' => $job->tuition_time,
                'tuition_duration' => $job->tuition_duration,
                'no_of_students' => $job->no_of_students,
                'published_at' => $job->published_at?->toDateTimeString(),
                'expires_at' => $job->expires_at?->toDateTimeString(),
            ],
            'meta' => [
                'title' => $job->title,
                'description' => Str::limit((string) $job->description, 155),
            ],
            'canApply' => $canApply,
            'application' => $application === null
                ? null
                : [
                    'id' => $application->id,
                    'status' => $application->status,
                    'expected_salary_amount' => $application->expected_salary_amount,
                    'salary_currency' => $application->salary_currency,
                    'cancel_reason' => $application->cancel_reason,
                    'created_at' => $application->created_at?->toDateTimeString(),
                ],
        ]);
    }

    /**
     * Get base public-visible job query.
     */
    private function publicJobsQuery(): Builder
    {
        return TuitionJob::query()
            ->where('status', JobStatus::Live)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Apply public sort strategy.
     */
    private function applySort(Builder $query, string $sort): void
    {
        if ($sort === 'salary_high') {
            $query->orderByDesc('salary_amount')->orderByDesc('published_at')->orderByDesc('id');

            return;
        }

        if ($sort === 'salary_low') {
            $query->orderBy('salary_amount')->orderByDesc('published_at')->orderByDesc('id');

            return;
        }

        $query->orderByDesc('published_at')->orderByDesc('id');
    }

    /**
     * Get category filter options.
     *
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    private function categoryOptions(): array
    {
        return Category::query()
            ->where('status', Category::STATUS_ACTIVE)
            ->whereHas('tuitionJobs', fn (Builder $builder) => $this->applyPublicScope($builder))
            ->ordered()
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->all();
    }

    /**
     * Get tuition type filter options.
     *
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    private function tuitionTypeOptions(): array
    {
        return TuitionType::query()
            ->where('status', TuitionType::STATUS_ACTIVE)
            ->whereHas('tuitionJobs', fn (Builder $builder) => $this->applyPublicScope($builder))
            ->ordered()
            ->get(['id', 'name', 'slug'])
            ->map(fn (TuitionType $type): array => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
            ])
            ->all();
    }

    /**
     * Get subject filter options.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function subjectOptions(): array
    {
        return Subject::query()
            ->where('status', Subject::STATUS_ACTIVE)
            ->whereHas('tuitionJobs', fn (Builder $builder) => $this->applyPublicScope($builder))
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (Subject $subject): array => [
                'id' => $subject->id,
                'name' => $subject->name,
            ])
            ->all();
    }

    /**
     * Get city filter options.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function cityOptions(): array
    {
        return City::query()
            ->where('status', City::STATUS_ACTIVE)
            ->whereHas('tuitionJobs', fn (Builder $builder) => $this->applyPublicScope($builder))
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (City $city): array => [
                'id' => $city->id,
                'name' => $city->name,
            ])
            ->all();
    }

    /**
     * Apply public visibility scope to relation query.
     */
    private function applyPublicScope(Builder $query): Builder
    {
        return $query
            ->where('status', JobStatus::Live)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}
