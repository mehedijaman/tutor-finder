<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\JobApplicationStatusUpdateRequest;
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
     * Display applications received for a guardian job.
     */
    public function index(Request $request, TuitionJob $tuitionJob): Response
    {
        $this->ensureOwnership($request, $tuitionJob);
        $status = strtolower(trim($request->string('status')->toString()));

        if (! in_array($status, TuitionJobApplication::statuses(), true)) {
            $status = '';
        }

        $items = TuitionJobApplication::query()
            ->with(['tutor:id,name,email,phone'])
            ->where('tuition_job_id', $tuitionJob->id)
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (TuitionJobApplication $application): array => [
                'id' => $application->id,
                'status' => $application->status,
                'cover_letter' => $application->cover_letter,
                'expected_salary' => $application->expected_salary,
                'guardian_note' => $application->guardian_note,
                'created_at' => $application->created_at?->toDateTimeString(),
                'reviewed_at' => $application->reviewed_at?->toDateTimeString(),
                'is_selected' => (int) $tuitionJob->selected_application_id === (int) $application->id,
                'tutor' => [
                    'id' => $application->tutor?->id,
                    'name' => $application->tutor?->name,
                    'email' => $application->tutor?->email,
                    'phone' => $application->tutor?->phone,
                ],
            ]);

        return inertia('guardian/jobs/Applications', [
            'job' => [
                'id' => $tuitionJob->id,
                'title' => $tuitionJob->title,
                'slug' => $tuitionJob->slug,
                'status' => $tuitionJob->status,
                'selected_application_id' => $tuitionJob->selected_application_id,
            ],
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
     * Update guardian review status for an application.
     *
     * @throws ValidationException
     */
    public function updateStatus(
        JobApplicationStatusUpdateRequest $request,
        TuitionJob $tuitionJob,
        TuitionJobApplication $tuitionJobApplication,
    ): RedirectResponse {
        $this->ensureOwnership($request, $tuitionJob);

        if ((int) $tuitionJobApplication->tuition_job_id !== (int) $tuitionJob->id) {
            abort(404);
        }

        $status = $request->validated('status');
        $note = $request->validated('guardian_note');
        $guardian = $request->user();

        try {
            if ($status === TuitionJobApplication::STATUS_SHORTLISTED) {
                $tuitionJobApplication->markShortlisted($guardian, $note);
            } else {
                $tuitionJobApplication->markRejected($guardian, $note);
            }
        } catch (DomainException $exception) {
            throw ValidationException::withMessages([
                'status' => $exception->getMessage(),
            ]);
        }

        $tuitionJobApplication->loadMissing('tutor');
        $tutor = $tuitionJobApplication->tutor;

        $tutor?->notify(new JobLifecycleNotification(
            event: 'job-application-status-updated',
            title: $status === TuitionJobApplication::STATUS_SHORTLISTED ? 'Application Shortlisted' : 'Application Rejected',
            message: "Your application for {$tuitionJob->title} is now {$status}.",
            url: '/tutor/job-applications',
            meta: [
                'job_id' => $tuitionJob->id,
                'application_id' => $tuitionJobApplication->id,
                'status' => $status,
            ],
        ));

        return back()->with('status', 'Application status updated successfully.');
    }

    /**
     * Confirm tutor engagement and mark job as confirmed.
     *
     * @throws ValidationException
     */
    public function confirm(
        Request $request,
        TuitionJob $tuitionJob,
        TuitionJobApplication $tuitionJobApplication,
    ): RedirectResponse {
        $this->ensureOwnership($request, $tuitionJob);

        if ((int) $tuitionJobApplication->tuition_job_id !== (int) $tuitionJob->id) {
            abort(404);
        }

        if ($tuitionJob->status !== TuitionJob::STATUS_LIVE) {
            throw ValidationException::withMessages([
                'status' => 'Only live jobs can be confirmed.',
            ]);
        }

        if ($tuitionJobApplication->status !== TuitionJobApplication::STATUS_SHORTLISTED) {
            throw ValidationException::withMessages([
                'status' => 'Only shortlisted applications can be confirmed.',
            ]);
        }

        $guardian = $request->user();

        $tuitionJobApplication->loadMissing('tutor');

        $otherApplications = TuitionJobApplication::query()
            ->with('tutor')
            ->where('tuition_job_id', $tuitionJob->id)
            ->whereKeyNot($tuitionJobApplication->getKey())
            ->whereIn('status', [
                TuitionJobApplication::STATUS_PENDING,
                TuitionJobApplication::STATUS_SHORTLISTED,
            ])
            ->get();

        DB::transaction(function () use ($tuitionJob, $tuitionJobApplication, $guardian): void {
            $tuitionJob->confirmEngagement($guardian, $tuitionJobApplication);

            TuitionJobApplication::query()
                ->where('tuition_job_id', $tuitionJob->id)
                ->whereKeyNot($tuitionJobApplication->getKey())
                ->whereIn('status', [
                    TuitionJobApplication::STATUS_PENDING,
                    TuitionJobApplication::STATUS_SHORTLISTED,
                ])
                ->update([
                    'status' => TuitionJobApplication::STATUS_REJECTED,
                    'guardian_note' => 'Job confirmed with another tutor.',
                    'reviewed_by' => $guardian?->getAuthIdentifier(),
                    'reviewed_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        $tuitionJobApplication->tutor?->notify(new JobLifecycleNotification(
            event: 'job-engagement-confirmed',
            title: 'Job Engagement Confirmed',
            message: "Congratulations! You have been selected for {$tuitionJob->title}.",
            url: '/tutor/job-applications',
            meta: [
                'job_id' => $tuitionJob->id,
                'application_id' => $tuitionJobApplication->id,
            ],
        ));

        $otherApplications
            ->filter(fn (TuitionJobApplication $application): bool => $application->tutor !== null)
            ->each(function (TuitionJobApplication $application) use ($tuitionJob): void {
                $application->tutor?->notify(new JobLifecycleNotification(
                    event: 'job-application-status-updated',
                    title: 'Application Rejected',
                    message: "Your application for {$tuitionJob->title} was not selected.",
                    url: '/tutor/job-applications',
                    meta: [
                        'job_id' => $tuitionJob->id,
                        'application_id' => $application->id,
                        'status' => TuitionJobApplication::STATUS_REJECTED,
                    ],
                ));
            });

        return back()->with('status', 'Tutor engagement confirmed successfully.');
    }

    /**
     * Ensure the job belongs to current guardian.
     */
    private function ensureOwnership(Request $request, TuitionJob $tuitionJob): void
    {
        if ((int) $tuitionJob->guardian_id !== (int) $request->user()?->getAuthIdentifier()) {
            abort(403);
        }
    }
}
