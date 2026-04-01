<?php

namespace App\Services\Job;

use App\Enums\ApplicationStatus;
use App\Enums\DurationType;
use App\Enums\FeePaymentMode;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\JobStatus;
use App\Events\HireConfirmed;
use App\Models\Invoice;
use App\Models\SiteSetting;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\TuitionJobAssignment;
use App\Models\User;
use App\Services\Finance\InvoiceLifecycleService;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HiringWorkflowService
{
    public function __construct(
        private readonly InvoiceLifecycleService $invoiceLifecycleService,
    ) {}

    /**
     * Confirm a tutor hire: create assignment, issue invoices, cancel other applications, and notify.
     *
     * @param  array{month1_escrow_required?: bool, month1_escrow_amount?: float|string|null, notes?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function confirmHire(
        TuitionJob $tuitionJob,
        TuitionJobApplication $application,
        User $guardian,
        array $data,
    ): void {
        $selectedTutorId = null;
        $otherTutorIds = [];
        $confirmedAt = now();

        try {
            DB::transaction(function () use (
                $tuitionJob,
                $application,
                $guardian,
                $confirmedAt,
                $data,
                &$selectedTutorId,
                &$otherTutorIds
            ): void {
                $guardianId = (int) $guardian->getAuthIdentifier();
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
                    ->whereKey($application->getKey())
                    ->where('job_id', $lockedJob->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedApplication->status !== ApplicationStatus::Shortlisted) {
                    throw new DomainException('Only shortlisted applications can be confirmed.');
                }

                $siteSetting = SiteSetting::current();
                $platformOwnerUserId = $siteSetting->platformOwnerUserId();

                if ($platformOwnerUserId === null) {
                    throw new DomainException('Platform finance account is not configured.');
                }

                $salaryBaseAmount = (float) ($data['salary_base_amount'] ?? 0);
                $salaryBaseSource = 'override';

                if ($salaryBaseAmount <= 0) {
                    $salaryBaseAmount = (float) ($lockedJob->salary_amount ?? 0);
                    $salaryBaseSource = 'job';
                }

                if ($salaryBaseAmount <= 0 && $lockedApplication->expected_salary_amount !== null) {
                    $salaryBaseAmount = (float) $lockedApplication->expected_salary_amount;
                    $salaryBaseSource = 'application';
                }

                if ($salaryBaseAmount <= 0) {
                    throw new DomainException('Unable to resolve salary base for service fee calculation.');
                }

                $feeContext = $this->calculateServiceFee($siteSetting, $salaryBaseAmount, $confirmedAt);

                $escrowRequired = (bool) ($data['month1_escrow_required'] ?? false);
                $escrowAmount = $escrowRequired ? (float) ($data['month1_escrow_amount'] ?? 0) : 0.0;

                if ($escrowRequired && $escrowAmount <= 0) {
                    throw new DomainException('Escrow amount is required when escrow is enabled.');
                }

                $assignment = TuitionJobAssignment::query()->create([
                    'job_id' => $lockedJob->id,
                    'tutor_user_id' => $lockedApplication->tutor_user_id,
                    'appointed_at' => $confirmedAt,
                    'confirmed_at' => $confirmedAt,
                    'cancelled_at' => null,
                    'cancelled_by' => null,
                    'fault' => null,
                    'cancel_reason' => null,
                    'reported_within_24h' => false,
                    'duration_type' => DurationType::LongTerm,
                    'short_term_months' => null,
                    'salary_base_amount' => $salaryBaseAmount,
                    'salary_base_source' => $salaryBaseSource,
                    'service_fee_rate' => $feeContext['serviceFeeRate'],
                    'service_fee_amount' => $feeContext['serviceFeeAmount'],
                    'fee_currency' => $feeContext['feeCurrency'],
                    'fee_due_at' => $feeContext['feeDueAt'],
                    'fee_payment_mode' => $feeContext['feePaymentMode'],
                    'month1_escrow_required' => $escrowRequired,
                    'month1_escrow_paid_at' => null,
                    'first_month_received_at' => null,
                    'month1_ended_at' => null,
                    'month1_settled_at' => null,
                    'notes' => $data['notes'] ?? null,
                    'metadata' => [
                        'phase1_timestamps_equal' => true,
                        'month1_escrow_amount' => $escrowRequired ? $escrowAmount : null,
                    ],
                ]);

                $this->issueFinanceInvoices(
                    $assignment,
                    $platformOwnerUserId,
                    $feeContext,
                    $guardianId,
                    $escrowRequired,
                    $escrowAmount,
                    $confirmedAt,
                );

                $lockedApplication->markConfirmed();

                $otherTutorIds = $this->cancelRemainingApplications($lockedJob->id, $lockedApplication->getKey());

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

        HireConfirmed::dispatch($tuitionJob, $application, $selectedTutorId, $otherTutorIds);
    }

    /**
     * Calculate platform service fee from site settings.
     *
     * @return array{serviceFeeRate: float, serviceFeeAmount: float, feeDueDays: int, feeDueAt: CarbonInterface, feeCurrency: string, feePaymentMode: string}
     */
    private function calculateServiceFee(SiteSetting $siteSetting, float $salaryBaseAmount, CarbonInterface $confirmedAt): array
    {
        $serviceFeeRate = (float) ($siteSetting->platform_service_fee_rate ?? 0.60000);
        $serviceFeeAmount = round($salaryBaseAmount * $serviceFeeRate, 2);
        $feeDueDays = (int) ($siteSetting->platform_service_fee_due_days ?? 10);
        $feeDueAt = $confirmedAt->copy()->addDays(max($feeDueDays, 0));
        $feeCurrency = strtoupper(trim((string) ($siteSetting->default_fee_currency ?? 'BDT')));
        $feePaymentMode = trim((string) ($siteSetting->default_fee_payment_mode ?? FeePaymentMode::PayBefore->value));

        return [
            'serviceFeeRate' => $serviceFeeRate,
            'serviceFeeAmount' => $serviceFeeAmount,
            'feeDueDays' => $feeDueDays,
            'feeDueAt' => $feeDueAt,
            'feeCurrency' => $feeCurrency,
            'feePaymentMode' => $feePaymentMode,
        ];
    }

    /**
     * Issue platform service fee and optional escrow invoices.
     *
     * @param  array{serviceFeeAmount: float, feeCurrency: string, feeDueAt: CarbonInterface, feePaymentMode: string}  $feeContext
     */
    private function issueFinanceInvoices(
        TuitionJobAssignment $assignment,
        int $platformOwnerUserId,
        array $feeContext,
        int $guardianId,
        bool $escrowRequired,
        float $escrowAmount,
        CarbonInterface $confirmedAt,
    ): void {
        $hasFinanceInvoices = Invoice::query()
            ->where('job_assignment_id', $assignment->id)
            ->whereIn('type', [
                InvoiceType::PlatformServiceFee,
                InvoiceType::OnlineMonth1Escrow,
            ])
            ->lockForUpdate()
            ->exists();

        if ($hasFinanceInvoices) {
            throw new DomainException('Finance invoices already exist for this assignment.');
        }

        $this->invoiceLifecycleService->issue([
            'invoice_no' => null,
            'invoiceable_type' => TuitionJobAssignment::class,
            'invoiceable_id' => $assignment->id,
            'user_id' => $assignment->tutor_user_id,
            'payer_user_id' => $assignment->tutor_user_id,
            'payee_user_id' => $platformOwnerUserId,
            'type' => InvoiceType::PlatformServiceFee,
            'job_assignment_id' => $assignment->id,
            'amount' => $feeContext['serviceFeeAmount'],
            'currency' => $feeContext['feeCurrency'],
            'status' => InvoiceStatus::Unpaid,
            'due_at' => $feeContext['feeDueAt'],
            'expires_at' => $feeContext['feeDueAt'],
            'issued_by' => null,
            'issued_at' => $confirmedAt,
            'notes' => 'Platform service fee generated on hire confirmation.',
        ]);

        if ($escrowRequired) {
            $this->invoiceLifecycleService->issue([
                'invoice_no' => null,
                'invoiceable_type' => TuitionJobAssignment::class,
                'invoiceable_id' => $assignment->id,
                'user_id' => $guardianId,
                'payer_user_id' => $guardianId,
                'payee_user_id' => $platformOwnerUserId,
                'type' => InvoiceType::OnlineMonth1Escrow,
                'job_assignment_id' => $assignment->id,
                'amount' => $escrowAmount,
                'currency' => $feeContext['feeCurrency'],
                'status' => InvoiceStatus::Unpaid,
                'due_at' => $confirmedAt,
                'expires_at' => $confirmedAt->copy()->addDays(10),
                'issued_by' => null,
                'issued_at' => $confirmedAt,
                'notes' => 'Month-1 escrow invoice generated on hire confirmation.',
            ]);
        }
    }

    /**
     * Cancel all remaining open applications for a confirmed job.
     *
     * @return list<int>
     */
    private function cancelRemainingApplications(int $jobId, int $confirmedApplicationId): array
    {
        $otherTutorIds = TuitionJobApplication::query()
            ->where('job_id', $jobId)
            ->whereKeyNot($confirmedApplicationId)
            ->whereIn('status', [
                ApplicationStatus::Applied,
                ApplicationStatus::Shortlisted,
                ApplicationStatus::Appointed,
            ])
            ->pluck('tutor_user_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        TuitionJobApplication::query()
            ->where('job_id', $jobId)
            ->whereKeyNot($confirmedApplicationId)
            ->whereIn('status', [
                ApplicationStatus::Applied,
                ApplicationStatus::Shortlisted,
                ApplicationStatus::Appointed,
            ])
            ->update([
                'status' => ApplicationStatus::Cancelled,
                'cancel_reason' => 'Job confirmed with another tutor.',
                'updated_at' => now(),
            ]);

        return $otherTutorIds;
    }

    /**
     * Assert guardian can manage open applications for this job.
     *
     * @throws DomainException
     */
    private function assertGuardianCanManageOpenApplications(TuitionJob $tuitionJob, int $guardianId): void
    {
        if ((int) $tuitionJob->guardian_id !== $guardianId) {
            throw new DomainException('You are not allowed to manage this job.');
        }

        if ($tuitionJob->status !== JobStatus::Live) {
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
