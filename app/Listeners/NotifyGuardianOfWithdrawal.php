<?php

namespace App\Listeners;

use App\Events\ApplicationWithdrawn;
use App\Notifications\JobLifecycleNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyGuardianOfWithdrawal implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ApplicationWithdrawn $event): void
    {
        $tuitionJob = $event->tuitionJob;
        $tutor = $event->tutor;

        $tuitionJob->guardian?->notify(new JobLifecycleNotification(
            event: 'job-application-cancelled',
            title: 'Application Cancelled',
            message: "{$tutor->name} cancelled application for {$tuitionJob->title}.",
            url: "/guardian/jobs/{$tuitionJob->id}/applications",
            meta: [
                'job_id' => $tuitionJob->id,
                'application_id' => $event->application->id,
                'tutor_user_id' => $tutor->getAuthIdentifier(),
            ],
        ));
    }
}
