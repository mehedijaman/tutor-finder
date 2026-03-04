<?php

namespace App\Services\Job;

use App\Enums\JobStatus;
use App\Models\TuitionJob;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class JobLifecycleService
{
    /**
     * Status transition matrix.
     *
     * @var array<string, list<string>>
     */
    private const TRANSITIONS = [
        JobStatus::Pending->value => [
            JobStatus::Live->value,
            JobStatus::Cancelled->value,
        ],
        JobStatus::Live->value => [
            JobStatus::Confirmed->value,
            JobStatus::Cancelled->value,
            JobStatus::Closed->value,
        ],
        JobStatus::Confirmed->value => [
            JobStatus::Closed->value,
        ],
        JobStatus::Cancelled->value => [],
        JobStatus::Closed->value => [],
    ];

    /**
     * Determine if a status can move to another status.
     */
    public function canTransition(string $currentStatus, string $targetStatus): bool
    {
        $allowedStatuses = self::TRANSITIONS[$currentStatus] ?? [];

        return in_array($targetStatus, $allowedStatuses, true);
    }

    /**
     * Transition a job to a new status using the transition matrix.
     */
    public function transitionStatus(TuitionJob $job, string $targetStatus, User $admin, ?string $reason = null): void
    {
        $currentStatus = $job->status instanceof JobStatus ? $job->status->value : (string) $job->status;

        if ($targetStatus === $currentStatus) {
            throw new DomainException('The job is already in the selected status.');
        }

        if (! $this->canTransition($currentStatus, $targetStatus)) {
            throw new DomainException("Invalid status transition from {$currentStatus} to {$targetStatus}.");
        }

        if ($targetStatus === JobStatus::Live->value) {
            $job->markLive($admin);
        }

        if ($targetStatus === JobStatus::Confirmed->value) {
            $this->confirmWithAssignment($job, $admin);
        }

        if ($targetStatus === JobStatus::Cancelled->value) {
            $job->markCancelled($reason ?: 'Cancelled by admin.', $admin);
        }

        if ($targetStatus === JobStatus::Closed->value) {
            $job->markClosed($admin);
        }
    }

    /**
     * Confirm job status by locking the assignment record.
     */
    private function confirmWithAssignment(TuitionJob $job, User $admin): void
    {
        DB::transaction(function () use ($job, $admin): void {
            $lockedJob = TuitionJob::query()
                ->whereKey($job->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $assignment = $lockedJob->assignment()
                ->lockForUpdate()
                ->first();

            if ($assignment === null) {
                throw new DomainException('Job cannot be confirmed without an assignment.');
            }

            $confirmedAt = $assignment->confirmed_at ?? now();

            if ($assignment->appointed_at === null) {
                $assignment->appointed_at = $confirmedAt;
            }

            if ($assignment->confirmed_at === null) {
                $assignment->confirmed_at = $confirmedAt;
            }

            $assignment->save();

            $lockedJob->markConfirmedAt($admin, $confirmedAt);
        });
    }

    /**
     * Permanently delete a trashed job.
     */
    public function forceDeleteJob(TuitionJob $job): void
    {
        if (! $job->trashed()) {
            throw new DomainException('Only trashed jobs can be permanently deleted.');
        }

        DB::transaction(function () use ($job): void {
            $job->subjects()->detach();
            $job->forceDelete();
        });
    }

    /**
     * Empty the recycle bin by permanently deleting all trashed jobs.
     */
    public function emptyRecycleBin(): int
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

        return $count;
    }
}
