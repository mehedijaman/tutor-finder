<?php

namespace App\Listeners;

use App\Enums\UserRole;
use App\Events\TicketReplied;
use App\Models\User;
use App\Notifications\TicketNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyOfTicketReply implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(TicketReplied $event): void
    {
        $ticket = $event->ticket;
        $replier = $event->replier;

        $ticket->loadMissing('user');

        if ($replier->role === UserRole::Admin) {
            $this->notifyTicketOwner($ticket, $replier);
        } else {
            $this->notifyAdmins($ticket, $replier);
        }
    }

    /**
     * Notify the ticket owner of an admin reply.
     */
    private function notifyTicketOwner(\App\Models\SupportTicket $ticket, User $admin): void
    {
        $rolePath = $ticket->user->role === UserRole::Tutor ? 'tutor' : 'guardian';

        $ticket->user->notify(new TicketNotification(
            event: 'ticket-admin-reply',
            title: 'Ticket Reply',
            message: "Admin replied to your ticket {$ticket->ticket_number}: {$ticket->subject}",
            url: "/{$rolePath}/support-tickets/{$ticket->id}",
            meta: [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'admin_name' => $admin->name,
            ],
        ));
    }

    /**
     * Notify admins of a user reply.
     */
    private function notifyAdmins(\App\Models\SupportTicket $ticket, User $replier): void
    {
        $admins = $this->getAdminsToNotify($ticket);

        foreach ($admins as $admin) {
            $admin->notify(new TicketNotification(
                event: 'ticket-user-reply',
                title: 'Ticket Reply',
                message: "{$replier->name} replied to ticket {$ticket->ticket_number}: {$ticket->subject}",
                url: "/admin/support-tickets/{$ticket->id}",
                meta: [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'user_id' => $replier->getAuthIdentifier(),
                ],
            ));
        }
    }

    /**
     * Get admin users to notify about a reply.
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
