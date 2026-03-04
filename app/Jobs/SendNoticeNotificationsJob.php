<?php

namespace App\Jobs;

use App\Enums\NoticeAudience;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Events\NoticeCreated;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        NoticeCreated::dispatch($this->notice);

        $this->insertDatabaseNotifications();

        SendNoticePushNotificationsJob::dispatch($this->notice);
    }

    /**
     * Insert database notifications in bulk for efficient storage.
     */
    private function insertDatabaseNotifications(): void
    {
        $roles = match ($this->notice->audience) {
            NoticeAudience::Tutor => [UserRole::Tutor],
            NoticeAudience::Guardian => [UserRole::Guardian],
            NoticeAudience::Both => [UserRole::Tutor, UserRole::Guardian],
        };

        $notificationData = [
            'type' => 'notice',
            'notice_id' => $this->notice->id,
            'title' => $this->notice->title,
            'audience' => $this->notice->audience->value,
            'expires_at' => $this->notice->expires_at?->toIso8601String(),
            'published_at' => $this->notice->published_at?->toIso8601String(),
        ];

        $now = now();

        User::query()
            ->whereIn('role', $roles)
            ->where('status', UserStatus::Active)
            ->select('id')
            ->chunkById(500, function ($users) use ($notificationData, $now): void {
                $notifications = $users->map(fn ($user) => [
                    'id' => Str::uuid()->toString(),
                    'type' => 'App\\Notifications\\NewNoticeNotification',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $user->id,
                    'data' => json_encode($notificationData),
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                DB::table('notifications')->insert($notifications);
            });
    }
}
