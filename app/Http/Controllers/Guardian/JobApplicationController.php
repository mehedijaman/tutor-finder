<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\JobApplicationConfirmRequest;
use App\Http\Requests\Guardian\JobApplicationStatusUpdateRequest;
use App\Models\Invoice;
use App\Models\SiteSetting;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\TuitionJobAssignment;
use App\Models\User;
use App\Notifications\JobLifecycleNotification;
use App\Services\Finance\InvoiceLifecycleService;
use DomainException;
use Illuminate\Database\QueryException;
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

        $tuitionJob->loadMissing('assignment.tutor');
        $selectedTutorUserId = $tuitionJob->assignment?->tutor_user_id;
        $isExpired = $tuitionJob->status === TuitionJob::STATUS_LIVE && $tuitionJob->isExpired();
        $canManageApplications = $tuitionJob->status === TuitionJob::STATUS_LIVE
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
                ['value' => TuitionJobApplication::STATUS_APPLIED, 'label' => 'Applied'],
                ['value' => TuitionJobApplication::STATUS_SHORTLISTED, 'label' => 'Shortlisted'],
                ['value' => TuitionJobApplication::STATUS_APPOINTED, 'label' => 'Appointed'],
                ['value' => TuitionJobApplication::STATUS_CONFIRMED, 'label' => 'Confirmed'],
                ['value' => TuitionJobApplication::STATUS_CANCELLED, 'label' => 'Cancelled'],
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

        if ((int) $tuitionJobApplication->job_id !== (int) $tuitionJob->id) {
            abort(404);
        }

        $status = $request->validated('status');
        $reason = $request->validated('cancel_reason');

        try {
            DB::transaction(function () use ($request, $tuitionJob, $tuitionJobApplication, $status, $reason): void {
                $guardianId = (int) $request->user()?->getAuthIdentifier();
                $lockedJob = TuitionJob::query()
                    ->whereKey($tuitionJob->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->assertGuardianCanManageOpenApplications($lockedJob, $guardianId);

                $lockedApplication = TuitionJobApplication::query()
                    ->whereKey($tuitionJobApplication->getKey())
                    ->where('job_id', $lockedJob->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($status === TuitionJobApplication::STATUS_SHORTLISTED) {
                    $lockedApplication->markShortlisted();
                } else {
                    if (! in_array($lockedApplication->status, [
                        TuitionJobApplication::STATUS_APPLIED,
                        TuitionJobApplication::STATUS_SHORTLISTED,
                    ], true)) {
                        throw new DomainException('Only applied or shortlisted applications can be cancelled.');
                    }

                    $lockedApplication->markCancelled($reason ?: 'Cancelled by guardian.');
                }
            });
        } catch (DomainException $exception) {
            throw ValidationException::withMessages([
                'status' => $exception->getMessage(),
            ]);
        }

        $tuitionJobApplication->loadMissing('tutor');
        $tutor = $tuitionJobApplication->tutor;

        $tutor?->notify(new JobLifecycleNotification(
            event: 'job-application-status-updated',
            title: $status === TuitionJobApplication::STATUS_SHORTLISTED ? 'Application Shortlisted' : 'Application Cancelled',
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
        JobApplicationConfirmRequest $request,
        TuitionJob $tuitionJob,
        TuitionJobApplication $tuitionJobApplication,
        InvoiceLifecycleService $invoiceLifecycleService,
    ): RedirectResponse {
        $this->ensureOwnership($request, $tuitionJob);

        if ((int) $tuitionJobApplication->job_id !== (int) $tuitionJob->id) {
            abort(404);
        }

        $guardian = $request->user();
        $selectedTutorId = null;
        $otherTutorIds = [];
        $confirmedAt = now();
        $validated = $request->validated();

        try {
            DB::transaction(function () use (
                $tuitionJob,
                $tuitionJobApplication,
                $guardian,
                $confirmedAt,
                $validated,
                $invoiceLifecycleService,
                &$selectedTutorId,
                &$otherTutorIds
            ): void {
                $guardianId = (int) $guardian?->getAuthIdentifier();
                $lockedJob = TuitionJob::query()
                    ->whereKey($tuitionJob->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->assertGuardianCanManageOpenApplications($lockedJob, $guardianId);

                $hasAssignment = TuitionJobAssignment::query()
                    ->where('job_id', $lockedJob->id)
                    ->lockForUpdate()
                    ->exists();

                if ($hasAssignment) {
                    throw new DomainException('A tutor has already been assigned for this job.');
                }

                $lockedApplication = TuitionJobApplication::query()
                    ->whereKey($tuitionJobApplication->getKey())
                    ->where('job_id', $lockedJob->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedApplication->status !== TuitionJobApplication::STATUS_SHORTLISTED) {
                    throw new DomainException('Only shortlisted applications can be confirmed.');
                }

                $siteSetting = SiteSetting::current();
                $platformOwnerUserId = $siteSetting->platformOwnerUserId();

                if ($platformOwnerUserId === null) {
                    throw new DomainException('Platform finance account is not configured.');
                }

                $salaryBaseAmount = (float) ($lockedJob->salary_amount ?? 0);
                $salaryBaseSource = 'job';

                if ($salaryBaseAmount <= 0 && $lockedApplication->expected_salary_amount !== null) {
                    $salaryBaseAmount = (float) $lockedApplication->expected_salary_amount;
                    $salaryBaseSource = 'application';
                }

                if ($salaryBaseAmount <= 0) {
                    throw new DomainException('Unable to resolve salary base for service fee calculation.');
                }

                $serviceFeeRate = (float) ($siteSetting->platform_service_fee_rate ?? 0.60000);
                $serviceFeeAmount = round($salaryBaseAmount * $serviceFeeRate, 2);
                $feeDueDays = (int) ($siteSetting->platform_service_fee_due_days ?? 10);
                $feeDueAt = $confirmedAt->copy()->addDays(max($feeDueDays, 0));
                $feeCurrency = strtoupper(trim((string) ($siteSetting->default_fee_currency ?? 'BDT')));
                $feePaymentMode = trim((string) ($siteSetting->default_fee_payment_mode ?? TuitionJobAssignment::PAYMENT_MODE_PAY_BEFORE));
                $escrowRequired = (bool) ($validated['month1_escrow_required'] ?? false);
                $escrowAmount = $escrowRequired ? (float) ($validated['month1_escrow_amount'] ?? 0) : 0.0;

                if ($escrowRequired && $escrowAmount <= 0) {
                    throw new DomainException('Escrow amount is required when escrow is enabled.');
                }

                $assignment = TuitionJobAssignment::query()->create([
                    'job_id' => $lockedJob->id,
                    'tutor_user_id' => $lockedApplication->tutor_user_id,
                    /**
                     * Phase 1 intentionally writes identical appointed/confirmed timestamps.
                     * This keeps history forward-compatible with a future split flow.
                     */
                    'appointed_at' => $confirmedAt,
                    'confirmed_at' => $confirmedAt,
                    'cancelled_at' => null,
                    'cancelled_by' => null,
                    'fault' => null,
                    'cancel_reason' => null,
                    'reported_within_24h' => false,
                    'duration_type' => TuitionJobAssignment::DURATION_LONG_TERM,
                    'short_term_months' => null,
                    'salary_base_amount' => $salaryBaseAmount,
                    'salary_base_source' => $salaryBaseSource,
                    'service_fee_rate' => $serviceFeeRate,
                    'service_fee_amount' => $serviceFeeAmount,
                    'fee_currency' => $feeCurrency,
                    'fee_due_at' => $feeDueAt,
                    'fee_payment_mode' => $feePaymentMode,
                    'month1_escrow_required' => $escrowRequired,
                    'month1_escrow_paid_at' => null,
                    'first_month_received_at' => null,
                    'month1_ended_at' => null,
                    'month1_settled_at' => null,
                    'notes' => $validated['notes'] ?? null,
                    'metadata' => [
                        'phase1_timestamps_equal' => true,
                        'month1_escrow_amount' => $escrowRequired ? $escrowAmount : null,
                    ],
                ]);

                $hasFinanceInvoices = Invoice::query()
                    ->where('job_assignment_id', $assignment->id)
                    ->whereIn('type', [
                        Invoice::TYPE_PLATFORM_SERVICE_FEE,
                        Invoice::TYPE_ONLINE_MONTH1_ESCROW,
                    ])
                    ->lockForUpdate()
                    ->exists();

                if ($hasFinanceInvoices) {
                    throw new DomainException('Finance invoices already exist for this assignment.');
                }

                $invoiceLifecycleService->issue([
                    'invoice_no' => null,
                    'invoiceable_type' => TuitionJobAssignment::class,
                    'invoiceable_id' => $assignment->id,
                    'user_id' => $assignment->tutor_user_id,
                    'payer_user_id' => $assignment->tutor_user_id,
                    'payee_user_id' => $platformOwnerUserId,
                    'type' => Invoice::TYPE_PLATFORM_SERVICE_FEE,
                    'job_assignment_id' => $assignment->id,
                    'amount' => $serviceFeeAmount,
                    'currency' => $feeCurrency,
                    'status' => Invoice::STATUS_UNPAID,
                    'due_at' => $feeDueAt,
                    'expires_at' => $feeDueAt,
                    'issued_by' => null,
                    'issued_at' => $confirmedAt,
                    'notes' => 'Platform service fee generated on hire confirmation.',
                ]);

                if ($escrowRequired) {
                    $invoiceLifecycleService->issue([
                        'invoice_no' => null,
                        'invoiceable_type' => TuitionJobAssignment::class,
                        'invoiceable_id' => $assignment->id,
                        'user_id' => $guardianId,
                        'payer_user_id' => $guardianId,
                        'payee_user_id' => $platformOwnerUserId,
                        'type' => Invoice::TYPE_ONLINE_MONTH1_ESCROW,
                        'job_assignment_id' => $assignment->id,
                        'amount' => $escrowAmount,
                        'currency' => $feeCurrency,
                        'status' => Invoice::STATUS_UNPAID,
                        'due_at' => $confirmedAt,
                        'expires_at' => $confirmedAt->copy()->addDays(10),
                        'issued_by' => null,
                        'issued_at' => $confirmedAt,
                        'notes' => 'Month-1 escrow invoice generated on hire confirmation.',
                    ]);
                }

                $lockedApplication->markConfirmed();

                $otherTutorIds = TuitionJobApplication::query()
                    ->where('job_id', $lockedJob->id)
                    ->whereKeyNot($lockedApplication->getKey())
                    ->whereIn('status', [
                        TuitionJobApplication::STATUS_APPLIED,
                        TuitionJobApplication::STATUS_SHORTLISTED,
                        TuitionJobApplication::STATUS_APPOINTED,
                    ])
                    ->pluck('tutor_user_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                TuitionJobApplication::query()
                    ->where('job_id', $lockedJob->id)
                    ->whereKeyNot($lockedApplication->getKey())
                    ->whereIn('status', [
                        TuitionJobApplication::STATUS_APPLIED,
                        TuitionJobApplication::STATUS_SHORTLISTED,
                        TuitionJobApplication::STATUS_APPOINTED,
                    ])
                    ->update([
                        'status' => TuitionJobApplication::STATUS_CANCELLED,
                        'cancel_reason' => 'Job confirmed with another tutor.',
                        'updated_at' => now(),
                    ]);

                $lockedJob->markConfirmedAt($guardian, $confirmedAt);
                $selectedTutorId = (int) $lockedApplication->tutor_user_id;
            });
        } catch (DomainException $exception) {
            throw ValidationException::withMessages([
                'status' => $exception->getMessage(),
            ]);
        } catch (QueryException) {
            throw ValidationException::withMessages([
                'status' => 'A tutor has already been assigned for this job.',
            ]);
        }

        $selectedTutor = User::query()->find($selectedTutorId);

        $selectedTutor?->notify(new JobLifecycleNotification(
            event: 'job-engagement-confirmed',
            title: 'Job Hire Confirmed',
            message: "Congratulations! You have been selected for {$tuitionJob->title}.",
            url: '/tutor/job-applications',
            meta: [
                'job_id' => $tuitionJob->id,
                'application_id' => $tuitionJobApplication->id,
            ],
        ));

        User::query()
            ->whereIn('id', $otherTutorIds)
            ->get()
            ->each(function (User $user) use ($tuitionJob): void {
                $user->notify(new JobLifecycleNotification(
                    event: 'job-application-status-updated',
                    title: 'Application Cancelled',
                    message: "Your application for {$tuitionJob->title} was not selected.",
                    url: '/tutor/job-applications',
                    meta: [
                        'job_id' => $tuitionJob->id,
                        'status' => TuitionJobApplication::STATUS_CANCELLED,
                    ],
                ));
            });

        return back()->with('status', 'Tutor hire confirmed successfully.');
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

    private function assertGuardianCanManageOpenApplications(TuitionJob $tuitionJob, int $guardianId): void
    {
        if ((int) $tuitionJob->guardian_id !== $guardianId) {
            throw new DomainException('You are not allowed to manage this job.');
        }

        if ($tuitionJob->status !== TuitionJob::STATUS_LIVE) {
            throw new DomainException('Only live jobs can manage applications.');
        }

        if ($tuitionJob->isExpired()) {
            throw new DomainException('Expired jobs cannot manage applications.');
        }

        if ($tuitionJob->assignment()->exists()) {
            throw new DomainException('A tutor has already been assigned for this job.');
        }
    }
}
