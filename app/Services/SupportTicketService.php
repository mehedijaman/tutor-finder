<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Events\TicketCreated;
use App\Events\TicketReplied;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class SupportTicketService
{
    /**
     * Create a new support ticket with its first message.
     *
     * @param  array{subject: string, category: string, priority: string, message: string, attachments?: array<int, UploadedFile>|null}  $data
     */
    public function createTicket(User $user, array $data): SupportTicket
    {
        $ticket = SupportTicket::query()->create([
            'user_id' => $user->getAuthIdentifier(),
            'subject' => $data['subject'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => TicketStatus::Open,
        ]);

        $message = $ticket->messages()->create([
            'user_id' => $user->getAuthIdentifier(),
            'body' => $data['message'],
        ]);

        $this->attachMedia($message, $data['attachments'] ?? []);

        TicketCreated::dispatch($ticket);

        return $ticket;
    }

    /**
     * Add a reply to an existing ticket.
     *
     * @param  array{message: string, attachments?: array<int, UploadedFile>|null}  $data
     */
    public function addReply(SupportTicket $ticket, User $user, array $data): SupportTicketMessage
    {
        $message = $ticket->messages()->create([
            'user_id' => $user->getAuthIdentifier(),
            'body' => $data['message'],
        ]);

        $this->attachMedia($message, $data['attachments'] ?? []);

        if ($ticket->status === TicketStatus::Closed && $user->getAuthIdentifier() !== $ticket->assigned_to) {
            $ticket->update([
                'status' => TicketStatus::Open,
                'closed_at' => null,
                'closed_by' => null,
            ]);
        }

        TicketReplied::dispatch($ticket, $message, $user);

        return $message;
    }

    /**
     * Close a ticket.
     */
    public function closeTicket(SupportTicket $ticket, User $admin): void
    {
        $ticket->update([
            'status' => TicketStatus::Closed,
            'closed_at' => now(),
            'closed_by' => $admin->getAuthIdentifier(),
        ]);
    }

    /**
     * Assign a ticket to an admin.
     */
    public function assignTicket(SupportTicket $ticket, int $adminId): void
    {
        $ticket->update([
            'assigned_to' => $adminId,
        ]);
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(SupportTicket $ticket, TicketStatus $status, ?User $admin = null): void
    {
        $updateData = ['status' => $status];

        if ($status === TicketStatus::Closed && $admin) {
            $updateData['closed_at'] = now();
            $updateData['closed_by'] = $admin->getAuthIdentifier();
        }

        if ($status !== TicketStatus::Closed) {
            $updateData['closed_at'] = null;
            $updateData['closed_by'] = null;
        }

        $ticket->update($updateData);
    }

    /**
     * Get status counts for tickets.
     *
     * @return array{all: int, open: int, in_progress: int, closed: int}
     */
    public function getStatusCounts(?User $user = null): array
    {
        $query = SupportTicket::query();

        if ($user !== null) {
            $query->where('user_id', $user->getAuthIdentifier());
        }

        $counts = (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'all' => (int) $counts->sum(),
            'open' => (int) ($counts[TicketStatus::Open->value] ?? 0),
            'in_progress' => (int) ($counts[TicketStatus::InProgress->value] ?? 0),
            'closed' => (int) ($counts[TicketStatus::Closed->value] ?? 0),
        ];
    }

    /**
     * Attach uploaded images to a message.
     *
     * @param  array<int, UploadedFile>|null  $attachments
     */
    private function attachMedia(SupportTicketMessage $message, ?array $attachments): void
    {
        if (empty($attachments)) {
            return;
        }

        foreach ($attachments as $file) {
            $message->addMedia($file)->toMediaCollection('attachments');
        }
    }
}
