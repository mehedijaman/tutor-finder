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
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class JobApplicationController extends Controller
{
    /**
     * Display tutor application list.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $status = strtolower(trim($request->string('status')->toString()));

        if (! in_array($status, TuitionJobApplication::statuses(), true)) {
            $status = '';
        }

        $items = TuitionJobApplication::query()
            ->with([
                'tuitionJob' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'title', 'slug', 'status', 'published_at', 'expires_at', 'city_id']),
                'tuitionJob.city:id,name',
            ])
            ->where('tutor_id', $user?->getAuthIdentifier())
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (TuitionJobApplication $application): array => [
                'id' => $application->id,
                'status' => $application->status,
                'expected_salary' => $application->expected_salary,
                'created_at' => $application->created_at?->toDateTimeString(),
                'reviewed_at' => $application->reviewed_at?->toDateTimeString(),
                'guardian_note' => $application->guardian_note,
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
                'status' => $status,
            ],
            'statusOptions' => [
                ['value' => TuitionJobApplication::STATUS_PENDING, 'label' => 'Pending'],
                ['value' => TuitionJobApplication::STATUS_SHORTLISTED, 'label' => 'Shortlisted'],
                ['value' => TuitionJobApplication::STATUS_REJECTED, 'label' => 'Rejected'],
                ['value' => TuitionJobApplication::STATUS_WITHDRAWN, 'label' => 'Withdrawn'],
            ],
        ]);
    }

    /**
     * Submit tutor application for a live job.
     *
     * @throws ValidationException
     */
    public function store(JobApplicationStoreRequest $request, TuitionJob $tuitionJob): RedirectResponse
    {
        $tutor = $request->user();
        $this->ensureJobCanBeApplied($tuitionJob);

        if ((int) $tuitionJob->guardian_id === (int) $tutor?->getAuthIdentifier()) {
            throw ValidationException::withMessages([
                'job' => 'You cannot apply to your own job posting.',
            ]);
        }

        $application = TuitionJobApplication::query()
            ->where('tuition_job_id', $tuitionJob->id)
            ->where('tutor_id', $tutor?->getAuthIdentifier())
            ->first();

        if ($application !== null) {
            if (in_array($application->status, [
                TuitionJobApplication::STATUS_REJECTED,
                TuitionJobApplication::STATUS_WITHDRAWN,
            ], true)) {
                $application->forceFill([
                    'status' => TuitionJobApplication::STATUS_PENDING,
                    'cover_letter' => $request->validated('cover_letter'),
                    'expected_salary' => $request->validated('expected_salary'),
                    'guardian_note' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ])->save();

                $tuitionJob->guardian?->notify(new JobLifecycleNotification(
                    event: 'job-application-resubmitted',
                    title: 'Application Resubmitted',
                    message: "{$tutor?->name} reapplied for {$tuitionJob->title}.",
                    url: "/guardian/jobs/{$tuitionJob->id}/applications",
                    meta: [
                        'job_id' => $tuitionJob->id,
                        'application_id' => $application->id,
                        'tutor_id' => $tutor?->getAuthIdentifier(),
                    ],
                ));

                return back()->with('status', 'Application submitted again successfully.');
            }

            throw ValidationException::withMessages([
                'job' => 'You have already applied to this job.',
            ]);
        }

        $application = TuitionJobApplication::query()->create([
            'tuition_job_id' => $tuitionJob->id,
            'tutor_id' => $tutor?->getAuthIdentifier(),
            'cover_letter' => $request->validated('cover_letter'),
            'expected_salary' => $request->validated('expected_salary'),
            'status' => TuitionJobApplication::STATUS_PENDING,
            'guardian_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        $tuitionJob->guardian?->notify(new JobLifecycleNotification(
            event: 'job-application-submitted',
            title: 'New Job Application',
            message: "{$tutor?->name} applied for {$tuitionJob->title}.",
            url: "/guardian/jobs/{$tuitionJob->id}/applications",
            meta: [
                'job_id' => $tuitionJob->id,
                'application_id' => $application->id,
                'tutor_id' => $tutor?->getAuthIdentifier(),
            ],
        ));

        return back()->with('status', 'Application submitted successfully.');
    }

    /**
     * Withdraw tutor application.
     */
    public function withdraw(Request $request, TuitionJobApplication $tuitionJobApplication): RedirectResponse
    {
        $tutor = $request->user();

        if ((int) $tuitionJobApplication->tutor_id !== (int) $tutor?->getAuthIdentifier()) {
            abort(403);
        }

        try {
            $tuitionJobApplication->markWithdrawn();
        } catch (DomainException $exception) {
            throw ValidationException::withMessages([
                'status' => $exception->getMessage(),
            ]);
        }

        $tuitionJobApplication->loadMissing('tuitionJob.guardian');
        $job = $tuitionJobApplication->tuitionJob;

        $job?->guardian?->notify(new JobLifecycleNotification(
            event: 'job-application-withdrawn',
            title: 'Application Withdrawn',
            message: "{$tutor?->name} withdrew application from {$job?->title}.",
            url: "/guardian/jobs/{$job?->id}/applications",
            meta: [
                'job_id' => $job?->id,
                'application_id' => $tuitionJobApplication->id,
                'tutor_id' => $tutor?->getAuthIdentifier(),
            ],
        ));

        return back()->with('status', 'Application withdrawn successfully.');
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
    }
}
