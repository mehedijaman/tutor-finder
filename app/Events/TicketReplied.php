<?php

namespace App\Events;

use App\Enums\UserRole;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketReplied implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public SupportTicket $ticket,
        public SupportTicketMessage $message,
        public User $replier,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * If admin replied, broadcast to the ticket owner's private channel.
     * If user replied, broadcast to admin channel.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        if ($this->replier->role === UserRole::Admin) {
            return [
                new PrivateChannel("App.Models.User.{$this->ticket->user_id}"),
            ];
        }

        return [
            new PrivateChannel('role.admin'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.replied';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'replier_name' => $this->replier->name,
            'replier_role' => $this->replier->role->value,
        ];
    }
}
