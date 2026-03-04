<?php

namespace App\Listeners;

use App\Enums\ApplicationStatus;
use App\Events\HireConfirmed;
use App\Models\User;
use App\Notifications\JobLifecycleNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyTutorsOfHireOutcome implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(HireConfirmed $event): void
    {
        $tuitionJob = $event->tuitionJob;
        $application = $event->application;

        $selectedTutor = User::query()->find($event->selectedTutorUserId);

        $selectedTutor?->notify(new JobLifecycleNotification(
            event: 'job-engagement-confirmed',
            title: 'Job Hire Confirmed',
            message: "Congratulations! You have been selected for {$tuitionJob->title}.",
            url: '/tutor/job-applications',
            meta: [
                'job_id' => $tuitionJob->id,
                'application_id' => $application->id,
            ],
        ));

        if (count($event->rejectedTutorUserIds) === 0) {
            return;
        }

        User::query()
            ->whereIn('id', $event->rejectedTutorUserIds)
            ->get()
            ->each(function (User $user) use ($tuitionJob): void {
                $user->notify(new JobLifecycleNotification(
                    event: 'job-application-status-updated',
                    title: 'Application Cancelled',
                    message: "Your application for {$tuitionJob->title} was not selected.",
                    url: '/tutor/job-applications',
                    meta: [
                        'job_id' => $tuitionJob->id,
                        'status' => ApplicationStatus::Cancelled,
                    ],
                ));
            });
    }
}
