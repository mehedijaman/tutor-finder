<?php

namespace App\Jobs;

use App\Enums\NoticeAudience;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Notice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class SendNoticePushNotificationsJob implements ShouldQueue
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

        $payload = json_encode([
            'title' => $this->notice->title,
            'body' => strip_tags(mb_substr($this->notice->body, 0, 100)),
            'icon' => '/favicon.ico',
            'tag' => 'notice-'.$this->notice->id,
            'url' => '/',
            'notification_id' => $this->notice->id,
        ]);

        $auth = [
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ];

        $webPush = new WebPush($auth);

        DB::table('push_subscriptions')
            ->join('users', 'push_subscriptions.subscribable_id', '=', 'users.id')
            ->where('push_subscriptions.subscribable_type', 'App\\Models\\User')
            ->whereIn('users.role', array_map(fn ($r) => $r->value, $roles))
            ->where('users.status', UserStatus::Active->value)
            ->select([
                'push_subscriptions.endpoint',
                'push_subscriptions.public_key',
                'push_subscriptions.auth_token',
            ])
            ->orderBy('push_subscriptions.id')
            ->chunk(100, function ($subscriptions) use ($webPush, $payload): void {
                foreach ($subscriptions as $sub) {
                    $subscription = Subscription::create([
                        'endpoint' => $sub->endpoint,
                        'keys' => [
                            'p256dh' => $sub->public_key,
                            'auth' => $sub->auth_token,
                        ],
                    ]);

                    $webPush->queueNotification($subscription, $payload);
                }

                foreach ($webPush->flush() as $report) {
                    if ($report->isSubscriptionExpired()) {
                        DB::table('push_subscriptions')
                            ->where('endpoint', $report->getEndpoint())
                            ->delete();
                    }
                }
            });
    }
}
