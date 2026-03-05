<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    /**
     * Determine whether the user can view any tickets.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Tutor, UserRole::Guardian], true);
    }

    /**
     * Determine whether the user can view the ticket.
     */
    public function view(User $user, SupportTicket $ticket): bool
    {
        return $this->isOwner($user, $ticket);
    }

    /**
     * Determine whether the user can create tickets.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Tutor, UserRole::Guardian], true);
    }

    /**
     * Determine whether the user can reply to the ticket.
     */
    public function reply(User $user, SupportTicket $ticket): bool
    {
        return $this->isOwner($user, $ticket);
    }

    /**
     * Check if the user owns the ticket.
     */
    private function isOwner(User $user, SupportTicket $ticket): bool
    {
        return (int) $ticket->user_id === (int) $user->getAuthIdentifier();
    }
}
