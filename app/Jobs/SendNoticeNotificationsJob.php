<?php

namespace App\Jobs;

use App\Enums\NoticeAudience;
use App\Enums\UserRole;
use App\Models\Notice;
use App\Models\User;
use App\Notifications\NewNoticeNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNoticeNotificationsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Notice $notice,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->notice->isExpired()) {
            return;
        }

        $roles = match ($this->notice->audience) {
            NoticeAudience::Tutor => [UserRole::Tutor],
            NoticeAudience::Guardian => [UserRole::Guardian],
            NoticeAudience::Both => [UserRole::Tutor, UserRole::Guardian],
        };

        User::query()
            ->whereIn('role', $roles)
            ->where('is_active', true)
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    $user->notify(new NewNoticeNotification($this->notice));
                }
            });
    }
}
