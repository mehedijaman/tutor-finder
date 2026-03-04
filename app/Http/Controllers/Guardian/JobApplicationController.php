<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Events\ApplicationStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\JobApplicationConfirmRequest;
use App\Http\Requests\Guardian\JobApplicationStatusUpdateRequest;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Services\Job\ApplicationService;
use App\Services\Job\HiringWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class JobApplicationController extends Controller
{
    /**
     * Display applications received for a guardian job.
     */
    public function index(Request $request, TuitionJob $tuitionJob): Response
    {
        $this->authorize('manageApplications', $tuitionJob);
        $status = strtolower(trim($request->string('status')->toString()));

        if (! in_array($status, TuitionJobApplication::statuses(), true)) {
            $status = '';
        }

        $tuitionJob->loadMissing('assignment.tutor');
        $selectedTutorUserId = $tuitionJob->assignment?->tutor_user_id;
        $isExpired = $tuitionJob->status === JobStatus::Live && $tuitionJob->isExpired();
        $canManageApplications = $tuitionJob->status === JobStatus::Live
            && ! $isExpired
            && $tuitionJob->assignment === null;

        $items = TuitionJobApplication::query()
            ->with(['tutor:id,name,email,phone'])
            ->where('job_id', $tuitionJob->id)
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
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
                'is_selected' => (int) $selectedTutorUserId === (int) $application->tutor_user_id,
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
                'is_expired' => $isExpired,
                'has_assignment' => $tuitionJob->assignment !== null,
                'can_manage_applications' => $canManageApplications,
                'selected_tutor_user_id' => $selectedTutorUserId,
                'selected_tutor_name' => $tuitionJob->assignment?->tutor?->name,
                'assignment_confirmed_at' => $tuitionJob->assignment?->confirmed_at?->toDateTimeString(),
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
     * Update guardian review status for an application.
     *
     * @throws ValidationException
     */
    public function updateStatus(
        JobApplicationStatusUpdateRequest $request,
        TuitionJob $tuitionJob,
        TuitionJobApplication $tuitionJobApplication,
        ApplicationService $applicationService,
    ): RedirectResponse {
        $this->authorize('manageApplications', $tuitionJob);

        if ((int) $tuitionJobApplication->job_id !== (int) $tuitionJob->id) {
            abort(404);
        }

        $status = $request->validated('status');
        $reason = $request->validated('cancel_reason');

        try {
            $applicationService->updateStatus(
                $tuitionJob,
                $tuitionJobApplication,
                $status,
                (int) $request->user()?->getAuthIdentifier(),
                $reason,
            );
        } catch (DomainException $exception) {
            throw ValidationException::withMessages([
                'status' => $exception->getMessage(),
            ]);
        }

        $tuitionJobApplication->loadMissing('tutor');

        ApplicationStatusUpdated::dispatch($tuitionJob, $tuitionJobApplication, $status);

        return back()->with('status', 'Application status updated successfully.');
    }

    /**
     * Confirm tutor engagement and mark job as confirmed.
     *
     * @throws ValidationException
     */
    public function confirm(
        JobApplicationConfirmRequest $request,
        TuitionJob $tuitionJob,
        TuitionJobApplication $tuitionJobApplication,
        HiringWorkflowService $hiringWorkflowService,
    ): RedirectResponse {
        $this->authorize('manageApplications', $tuitionJob);

        if ((int) $tuitionJobApplication->job_id !== (int) $tuitionJob->id) {
            abort(404);
        }

        $hiringWorkflowService->confirmHire(
            $tuitionJob,
            $tuitionJobApplication,
            $request->user(),
            $request->validated(),
        );

        return back()->with('status', 'Tutor hire confirmed successfully.');
    }
}
