<?php

namespace App\Notifications;

use App\Models\Notice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewNoticeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Notice $notice,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'notice',
            'notice_id' => $this->notice->id,
            'title' => $this->notice->title,
            'audience' => $this->notice->audience->value,
            'expires_at' => $this->notice->expires_at?->toIso8601String(),
            'published_at' => $this->notice->published_at?->toIso8601String(),
        ];
    }
}
