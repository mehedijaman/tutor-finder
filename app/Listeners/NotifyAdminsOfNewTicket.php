<?php

namespace App\Listeners;

use App\Enums\UserRole;
use App\Events\TicketCreated;
use App\Models\User;
use App\Notifications\TicketNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminsOfNewTicket implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(TicketCreated $event): void
    {
        $ticket = $event->ticket;
        $ticket->loadMissing('user');

        $admins = $this->getAdminsToNotify($ticket);

        foreach ($admins as $admin) {
            $admin->notify(new TicketNotification(
                event: 'ticket-created',
                title: 'New Support Ticket',
                message: "{$ticket->user->name} opened ticket {$ticket->ticket_number}: {$ticket->subject}",
                url: "/admin/support-tickets/{$ticket->id}",
                meta: [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'category' => $ticket->category->value,
                    'priority' => $ticket->priority->value,
                    'user_id' => $ticket->user_id,
                ],
            ));
        }
    }

    /**
     * Get admin users to notify about a new ticket.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function getAdminsToNotify(\App\Models\SupportTicket $ticket): \Illuminate\Database\Eloquent\Collection
    {
        if ($ticket->assigned_to) {
            return User::query()
                ->where('id', $ticket->assigned_to)
                ->get();
        }

        return User::query()
            ->where('role', UserRole::Admin)
            ->permission('ticket-view')
            ->get();
    }
}
