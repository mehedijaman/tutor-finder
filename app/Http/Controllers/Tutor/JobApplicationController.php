<?php

namespace App\Http\Controllers\Tutor;

use App\Enums\ApplicationStatus;
use App\Events\ApplicationSubmitted;
use App\Events\ApplicationWithdrawn;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\JobApplicationStoreRequest;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Services\Job\ApplicationService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class JobApplicationController extends Controller
{
    /**
     * Display tutor application list.
     */
    public function index(Request $request, ?string $status = null): Response
    {
        $user = $request->user();
        $presetStatus = $this->normalizeStatus((string) $status);
        $queryStatus = $this->normalizeStatus(trim($request->string('status')->toString()));
        $effectiveStatus = $presetStatus ?: $queryStatus;

        $items = TuitionJobApplication::query()
            ->with([
                'tuitionJob' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'title', 'slug', 'status', 'published_at', 'expires_at', 'city_id']),
                'tuitionJob.city:id,name',
            ])
            ->where('tutor_user_id', $user?->getAuthIdentifier())
            ->when($effectiveStatus !== '', fn ($builder) => $builder->where('status', $effectiveStatus))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (TuitionJobApplication $application): array => [
                'id' => $application->id,
                'status' => $application->status,
                'expected_salary_amount' => $application->expected_salary_amount,
                'salary_currency' => $application->salary_currency,
                'created_at' => $application->created_at?->toDateTimeString(),
                'cancel_reason' => $application->cancel_reason,
                'job' => [
                    'id' => $application->tuitionJob?->id,
                    'title' => $application->tuitionJob?->title ?? '[Deleted Job]',
                    'slug' => $application->tuitionJob?->slug,
                    'status' => $application->tuitionJob?->status ?? 'deleted',
                    'city_name' => $application->tuitionJob?->city?->name,
                ],
            ]);

        $statusSummary = TuitionJobApplication::query()
            ->where('tutor_user_id', $user?->getAuthIdentifier())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = [
            'all' => (int) $statusSummary->sum(),
            'applied' => (int) ($statusSummary[ApplicationStatus::Applied->value] ?? 0),
            'shortlisted' => (int) ($statusSummary[ApplicationStatus::Shortlisted->value] ?? 0),
            'appointed' => (int) ($statusSummary[ApplicationStatus::Appointed->value] ?? 0),
            'confirmed' => (int) ($statusSummary[ApplicationStatus::Confirmed->value] ?? 0),
            'cancelled' => (int) ($statusSummary[ApplicationStatus::Cancelled->value] ?? 0),
        ];

        return inertia('tutor/job-applications/Index', [
            'items' => $items,
            'filters' => [
                'status' => $queryStatus,
                'preset_status' => $presetStatus,
            ],
            'statusCounts' => $statusCounts,
        ]);
    }

    /**
     * Display applied applications.
     */
    public function applied(Request $request): Response
    {
        return $this->index($request, ApplicationStatus::Applied->value);
    }

    /**
     * Display shortlisted applications.
     */
    public function shortlisted(Request $request): Response
    {
        return $this->index($request, ApplicationStatus::Shortlisted->value);
    }

    /**
     * Display appointed applications.
     */
    public function appointed(Request $request): Response
    {
        return $this->index($request, ApplicationStatus::Appointed->value);
    }

    /**
     * Display confirmed applications.
     */
    public function confirmed(Request $request): Response
    {
        return $this->index($request, ApplicationStatus::Confirmed->value);
    }

    /**
     * Display cancelled applications.
     */
    public function cancelled(Request $request): Response
    {
        return $this->index($request, ApplicationStatus::Cancelled->value);
    }

    /**
     * Submit tutor application for a live job.
     *
     * @throws ValidationException
     */
    public function store(
        JobApplicationStoreRequest $request,
        TuitionJob $tuitionJob,
        ApplicationService $applicationService,
    ): RedirectResponse {
        $tutor = $request->user();

        $result = $applicationService->submit($tuitionJob, $tutor, [
            'cover_letter' => $request->validated('cover_letter'),
            'expected_salary_amount' => $request->validated('expected_salary_amount'),
            'salary_currency' => $request->validated('salary_currency'),
        ]);

        $application = $result['application'];
        $resubmitted = $result['resubmitted'];

        ApplicationSubmitted::dispatch($tuitionJob, $application, $tutor, $resubmitted);

        return back()->with('status', $resubmitted
            ? 'Application submitted again successfully.'
            : 'Application submitted successfully.');
    }

    /**
     * Withdraw tutor application.
     */
    public function withdraw(
        Request $request,
        TuitionJobApplication $tuitionJobApplication,
        ApplicationService $applicationService,
    ): RedirectResponse {
        $tutor = $request->user();

        $this->authorize('withdraw', $tuitionJobApplication);

        try {
            $applicationService->withdraw($tuitionJobApplication);
        } catch (DomainException $exception) {
            throw ValidationException::withMessages([
                'status' => $exception->getMessage(),
            ]);
        }

        $tuitionJobApplication->loadMissing('tuitionJob');
        $job = $tuitionJobApplication->tuitionJob;

        if ($job !== null) {
            ApplicationWithdrawn::dispatch($job, $tuitionJobApplication, $tutor);
        }

        return back()->with('status', 'Application cancelled successfully.');
    }

    /**
     * Normalize query or preset status.
     */
    private function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        if (ApplicationStatus::tryFrom($normalized) === null) {
            return '';
        }

        return $normalized;
    }
}
