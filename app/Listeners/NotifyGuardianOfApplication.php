<?php

namespace App\Listeners;

use App\Events\ApplicationSubmitted;
use App\Notifications\JobLifecycleNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyGuardianOfApplication implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ApplicationSubmitted $event): void
    {
        $tuitionJob = $event->tuitionJob;
        $tutor = $event->tutor;

        $eventName = $event->resubmitted ? 'job-application-resubmitted' : 'job-application-submitted';
        $title = $event->resubmitted ? 'Application Resubmitted' : 'New Job Application';
        $message = $event->resubmitted
            ? "{$tutor->name} reapplied for {$tuitionJob->title}."
            : "{$tutor->name} applied for {$tuitionJob->title}.";

        $tuitionJob->guardian?->notify(new JobLifecycleNotification(
            event: $eventName,
            title: $title,
            message: $message,
            url: "/guardian/jobs/{$tuitionJob->id}/applications",
            meta: [
                'job_id' => $tuitionJob->id,
                'application_id' => $event->application->id,
                'tutor_user_id' => $tutor->getAuthIdentifier(),
            ],
        ));
    }
}
