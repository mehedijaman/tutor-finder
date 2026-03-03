<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\JobApplicationStoreRequest;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Notifications\JobLifecycleNotification;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        return inertia('tutor/job-applications/Index', [
            'items' => $items,
            'filters' => [
                'status' => $queryStatus,
                'preset_status' => $presetStatus,
            ],
            'statusOptions' => [
                ['value' => TuitionJobApplication::STATUS_APPLIED, 'label' => 'Applied'],
                ['value' => TuitionJobApplication::STATUS_SHORTLISTED, 'label' => 'Shortlisted'],
                ['value' => TuitionJobApplication::STATUS_APPOINTED, 'label' => 'Appointed'],
                ['value' => TuitionJobApplication::STATUS_CONFIRMED, 'label' => 'Confirmed'],
                ['value' => TuitionJobApplication::STATUS_CANCELLED, 'label' => 'Cancelled'],
            ],
        ]);
    }

    /**
     * Display applied applications.
     */
    public function applied(Request $request): Response
    {
        return $this->index($request, TuitionJobApplication::STATUS_APPLIED);
    }

    /**
     * Display shortlisted applications.
     */
    public function shortlisted(Request $request): Response
    {
        return $this->index($request, TuitionJobApplication::STATUS_SHORTLISTED);
    }

    /**
     * Display appointed applications.
     */
    public function appointed(Request $request): Response
    {
        return $this->index($request, TuitionJobApplication::STATUS_APPOINTED);
    }

    /**
     * Display confirmed applications.
     */
    public function confirmed(Request $request): Response
    {
        return $this->index($request, TuitionJobApplication::STATUS_CONFIRMED);
    }

    /**
     * Display cancelled applications.
     */
    public function cancelled(Request $request): Response
    {
        return $this->index($request, TuitionJobApplication::STATUS_CANCELLED);
    }

    /**
     * Submit tutor application for a live job.
     *
     * @throws ValidationException
     */
    public function store(JobApplicationStoreRequest $request, TuitionJob $tuitionJob): RedirectResponse
    {
        $tutor = $request->user();
        $application = null;
        $resubmitted = false;

        DB::transaction(function () use ($request, $tuitionJob, $tutor, &$application, &$resubmitted): void {
            $lockedJob = TuitionJob::query()
                ->whereKey($tuitionJob->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureJobCanBeApplied($lockedJob);

            if ((int) $lockedJob->guardian_id === (int) $tutor?->getAuthIdentifier()) {
                throw ValidationException::withMessages([
                    'job' => 'You cannot apply to your own job posting.',
                ]);
            }

            $application = TuitionJobApplication::query()
                ->where('job_id', $lockedJob->id)
                ->where('tutor_user_id', $tutor?->getAuthIdentifier())
                ->lockForUpdate()
                ->first();

            if ($application !== null) {
                if ($application->status !== TuitionJobApplication::STATUS_CANCELLED) {
                    throw ValidationException::withMessages([
                        'job' => 'You have already applied to this job.',
                    ]);
                }

                $application->markApplied(
                    $request->validated('cover_letter'),
                    $request->validated('expected_salary_amount'),
                );
                $application->forceFill([
                    'salary_currency' => $request->validated('salary_currency') ?? 'BDT',
                ])->save();
                $resubmitted = true;

                return;
            }

            $application = TuitionJobApplication::query()->create([
                'job_id' => $lockedJob->id,
                'tutor_user_id' => $tutor?->getAuthIdentifier(),
                'cover_letter' => $request->validated('cover_letter'),
                'expected_salary_amount' => $request->validated('expected_salary_amount'),
                'salary_currency' => $request->validated('salary_currency') ?? 'BDT',
                'status' => TuitionJobApplication::STATUS_APPLIED,
                'cancel_reason' => null,
                'metadata' => null,
            ]);
        });

        $event = $resubmitted ? 'job-application-resubmitted' : 'job-application-submitted';
        $title = $resubmitted ? 'Application Resubmitted' : 'New Job Application';
        $message = $resubmitted
            ? "{$tutor?->name} reapplied for {$tuitionJob->title}."
            : "{$tutor?->name} applied for {$tuitionJob->title}.";

        $tuitionJob->guardian?->notify(new JobLifecycleNotification(
            event: $event,
            title: $title,
            message: $message,
            url: "/guardian/jobs/{$tuitionJob->id}/applications",
            meta: [
                'job_id' => $tuitionJob->id,
                'application_id' => $application?->id,
                'tutor_user_id' => $tutor?->getAuthIdentifier(),
            ],
        ));

        return back()->with('status', $resubmitted
            ? 'Application submitted again successfully.'
            : 'Application submitted successfully.');
    }

    /**
     * Withdraw tutor application.
     */
    public function withdraw(Request $request, TuitionJobApplication $tuitionJobApplication): RedirectResponse
    {
        $tutor = $request->user();

        if ((int) $tuitionJobApplication->tutor_user_id !== (int) $tutor?->getAuthIdentifier()) {
            abort(403);
        }

        try {
            if (! in_array($tuitionJobApplication->status, [
                TuitionJobApplication::STATUS_APPLIED,
                TuitionJobApplication::STATUS_SHORTLISTED,
            ], true)) {
                throw new DomainException('Only applied or shortlisted applications can be cancelled.');
            }

            $tuitionJobApplication->markCancelled('Cancelled by tutor.');
        } catch (DomainException $exception) {
            throw ValidationException::withMessages([
                'status' => $exception->getMessage(),
            ]);
        }

        $tuitionJobApplication->loadMissing('tuitionJob.guardian');
        $job = $tuitionJobApplication->tuitionJob;

        $job?->guardian?->notify(new JobLifecycleNotification(
            event: 'job-application-cancelled',
            title: 'Application Cancelled',
            message: "{$tutor?->name} cancelled application for {$job?->title}.",
            url: "/guardian/jobs/{$job?->id}/applications",
            meta: [
                'job_id' => $job?->id,
                'application_id' => $tuitionJobApplication->id,
                'tutor_user_id' => $tutor?->getAuthIdentifier(),
            ],
        ));

        return back()->with('status', 'Application cancelled successfully.');
    }

    /**
     * Ensure a public job can be applied to.
     *
     * @throws ValidationException
     */
    private function ensureJobCanBeApplied(TuitionJob $tuitionJob): void
    {
        if ($tuitionJob->status !== TuitionJob::STATUS_LIVE) {
            throw ValidationException::withMessages([
                'job' => 'This job is not open for applications.',
            ]);
        }

        if ($tuitionJob->published_at === null || $tuitionJob->published_at->isFuture()) {
            throw ValidationException::withMessages([
                'job' => 'This job is not published yet.',
            ]);
        }

        if ($tuitionJob->isExpired()) {
            throw ValidationException::withMessages([
                'job' => 'This job has expired.',
            ]);
        }

        if ($tuitionJob->assignment()->exists()) {
            throw ValidationException::withMessages([
                'job' => 'This job has already been finalized.',
            ]);
        }
    }

    /**
     * Normalize query or preset status.
     */
    private function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        if (! in_array($normalized, TuitionJobApplication::statuses(), true)) {
            return '';
        }

        return $normalized;
    }
}
