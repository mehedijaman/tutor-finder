<?php

namespace App\Events;

use App\Enums\NoticeAudience;
use App\Models\Notice;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoticeCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Notice $notice,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        if (in_array($this->notice->audience, [NoticeAudience::Tutor, NoticeAudience::Both], true)) {
            $channels[] = new PrivateChannel('role.tutor');
        }

        if (in_array($this->notice->audience, [NoticeAudience::Guardian, NoticeAudience::Both], true)) {
            $channels[] = new PrivateChannel('role.guardian');
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'notice.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'notice_id' => $this->notice->id,
            'title' => $this->notice->title,
            'audience' => $this->notice->audience->value,
            'created_at' => $this->notice->created_at->toIso8601String(),
        ];
    }
}
