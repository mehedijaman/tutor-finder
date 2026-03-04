<?php

namespace App\Listeners;

use App\Enums\ApplicationStatus;
use App\Events\ApplicationStatusUpdated;
use App\Notifications\JobLifecycleNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyTutorOfStatusChange implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ApplicationStatusUpdated $event): void
    {
        $application = $event->application;
        $tuitionJob = $event->tuitionJob;
        $status = $event->status;

        $application->loadMissing('tutor');
        $tutor = $application->tutor;

        $tutor?->notify(new JobLifecycleNotification(
            event: 'job-application-status-updated',
            title: $status === ApplicationStatus::Shortlisted->value ? 'Application Shortlisted' : 'Application Cancelled',
            message: "Your application for {$tuitionJob->title} is now {$status}.",
            url: '/tutor/job-applications',
            meta: [
                'job_id' => $tuitionJob->id,
                'application_id' => $application->id,
                'status' => $status,
            ],
        ));
    }
}
