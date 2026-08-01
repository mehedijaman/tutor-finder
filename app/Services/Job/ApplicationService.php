<?php

namespace App\Services\Job;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    /**
     * Submit a tutor application for a live job.
     *
     * @param  array{cover_letter: string|null, expected_salary_amount: float|string|null, salary_currency: string|null}  $data
     * @return array{application: TuitionJobApplication, resubmitted: bool}
     *
     * @throws ValidationException
     */
    public function submit(TuitionJob $tuitionJob, User $tutor, array $data): array
    {
        $application = null;
        $resubmitted = false;

        DB::transaction(function () use ($tuitionJob, $tutor, $data, &$application, &$resubmitted): void {
            $lockedJob = TuitionJob::query()
                ->whereKey($tuitionJob->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureJobCanBeApplied($lockedJob);

            if ((int) $lockedJob->guardian_id === (int) $tutor->getAuthIdentifier()) {
                throw ValidationException::withMessages([
                    'job' => 'You cannot apply to your own job posting.',
                ]);
            }

            $tutorProfile = $tutor->tutorProfile;
            $tutorGender = strtolower(trim((string) ($tutorProfile?->gender ?? '')));
            $requiredGender = strtolower(trim((string) ($lockedJob->tutor_gender->value ?? $lockedJob->tutor_gender ?? 'any')));

            if ($requiredGender !== 'any' && $requiredGender !== '') {
                if ($tutorGender !== '' && $tutorGender !== $requiredGender) {
                    $genderLabel = ucfirst($requiredGender);
                    throw ValidationException::withMessages([
                        'job' => "This tuition job requires a {$genderLabel} tutor.",
                    ]);
                }
            }

            $application = TuitionJobApplication::query()
                ->where('job_id', $lockedJob->id)
                ->where('tutor_user_id', $tutor->getAuthIdentifier())
                ->lockForUpdate()
                ->first();

            if ($application !== null) {
                if ($application->status !== ApplicationStatus::Cancelled) {
                    throw ValidationException::withMessages([
                        'job' => 'You have already applied to this job.',
                    ]);
                }

                $application->markApplied(
                    $data['cover_letter'] ?? null,
                    $data['expected_salary_amount'] ?? null,
                );
                $application->forceFill([
                    'salary_currency' => $data['salary_currency'] ?? 'BDT',
                ])->save();
                $resubmitted = true;

                return;
            }

            $application = TuitionJobApplication::query()->create([
                'job_id' => $lockedJob->id,
                'tutor_user_id' => $tutor->getAuthIdentifier(),
                'cover_letter' => $data['cover_letter'] ?? null,
                'expected_salary_amount' => $data['expected_salary_amount'] ?? null,
                'salary_currency' => $data['salary_currency'] ?? 'BDT',
                'status' => ApplicationStatus::Applied,
                'cancel_reason' => null,
                'metadata' => null,
            ]);
        });

        return [
            'application' => $application,
            'resubmitted' => $resubmitted,
        ];
    }

    /**
     * Withdraw a tutor application.
     *
     * @throws DomainException
     */
    public function withdraw(TuitionJobApplication $application): void
    {
        if (! in_array($application->status, [
            ApplicationStatus::Applied,
            ApplicationStatus::Shortlisted,
        ], true)) {
            throw new DomainException('Only applied or shortlisted applications can be cancelled.');
        }

        $application->markCancelled('Cancelled by tutor.');
    }

    /**
     * Update guardian review status for an application.
     *
     * @throws DomainException
     */
    public function updateStatus(
        TuitionJob $tuitionJob,
        TuitionJobApplication $application,
        string $status,
        int $guardianId,
        ?string $reason = null,
    ): void {
        DB::transaction(function () use ($tuitionJob, $application, $status, $guardianId, $reason): void {
            $lockedJob = TuitionJob::query()
                ->whereKey($tuitionJob->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertGuardianCanManageOpenApplications($lockedJob, $guardianId);

            $lockedApplication = TuitionJobApplication::query()
                ->whereKey($application->getKey())
                ->where('job_id', $lockedJob->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($status === ApplicationStatus::Shortlisted->value) {
                $lockedApplication->markShortlisted();
            } else {
                if (! in_array($lockedApplication->status, [
                    ApplicationStatus::Applied,
                    ApplicationStatus::Shortlisted,
                ], true)) {
                    throw new DomainException('Only applied or shortlisted applications can be cancelled.');
                }

                $lockedApplication->markCancelled($reason ?: 'Cancelled by guardian.');
            }
        });
    }

    /**
     * Ensure a public job can be applied to.
     *
     * @throws ValidationException
     */
    public function ensureJobCanBeApplied(TuitionJob $tuitionJob): void
    {
        if ($tuitionJob->status !== JobStatus::Live) {
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
     * Assert guardian can manage open applications for this job.
     *
     * @throws DomainException
     */
    public function assertGuardianCanManageOpenApplications(TuitionJob $tuitionJob, int $guardianId): void
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
