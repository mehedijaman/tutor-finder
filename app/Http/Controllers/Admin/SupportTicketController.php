<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignTicketRequest;
use App\Http\Requests\Admin\UpdateTicketStatusRequest;
use App\Http\Requests\StoreTicketReplyRequest;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class SupportTicketController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly SupportTicketService $ticketService,
    ) {}

    /**
     * Display support tickets with filtering.
     */
    public function index(Request $request): Response
    {
        $query = trim($request->string('q')->toString());
        $status = trim($request->string('status')->toString());
        $priority = trim($request->string('priority')->toString());
        $category = trim($request->string('category')->toString());
        $assignedTo = $request->integer('assigned_to');

        $items = SupportTicket::query()
            ->with(['user:id,name,email,role', 'assignedAdmin:id,name', 'latestMessage'])
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($sub) use ($query): void {
                    $sub->where('subject', 'like', "%{$query}%")
                        ->orWhere('ticket_number', 'like', "%{$query}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$query}%"));
                });
            })
            ->when($status !== '' && TicketStatus::tryFrom($status), fn ($b) => $b->where('status', $status))
            ->when($priority !== '' && TicketPriority::tryFrom($priority), fn ($b) => $b->where('priority', $priority))
            ->when($category !== '' && TicketCategory::tryFrom($category), fn ($b) => $b->where('category', $category))
            ->when($assignedTo > 0, fn ($b) => $b->where('assigned_to', $assignedTo))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (SupportTicket $ticket): array => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'category' => $ticket->category,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'user' => [
                    'id' => $ticket->user->id,
                    'name' => $ticket->user->name,
                    'role' => $ticket->user->role,
                ],
                'assigned_admin' => $ticket->assignedAdmin ? [
                    'id' => $ticket->assignedAdmin->id,
                    'name' => $ticket->assignedAdmin->name,
                ] : null,
                'last_reply_at' => $ticket->latestMessage?->created_at?->toDateTimeString(),
                'created_at' => $ticket->created_at?->toDateTimeString(),
            ]);

        return inertia('admin/support-tickets/Index', [
            'items' => $items,
            'filters' => [
                'q' => $query,
                'status' => $status,
                'priority' => $priority,
                'category' => $category,
                'assigned_to' => $assignedTo,
            ],
            'counts' => $this->ticketService->getStatusCounts(),
            'priorityOptions' => array_map(
                fn (TicketPriority $p): array => ['value' => $p->value, 'label' => $p->label()],
                TicketPriority::cases(),
            ),
            'categoryOptions' => array_map(
                fn (TicketCategory $c): array => ['value' => $c->value, 'label' => $c->label()],
                TicketCategory::cases(),
            ),
            'statusOptions' => array_map(
                fn (TicketStatus $s): array => ['value' => $s->value, 'label' => $s->label()],
                TicketStatus::cases(),
            ),
            'adminUsers' => User::query()
                ->where('role', UserRole::Admin)
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn (User $u): array => ['value' => $u->id, 'label' => $u->name])
                ->all(),
        ]);
    }

    /**
     * Display a support ticket thread.
     */
    public function show(SupportTicket $supportTicket): Response
    {
        $supportTicket->load([
            'user:id,name,email,role,avatar',
            'assignedAdmin:id,name',
            'closedByAdmin:id,name',
            'messages' => fn ($q) => $q->with(['user:id,name,role,avatar', 'media'])->orderBy('created_at'),
        ]);

        return inertia('admin/support-tickets/Show', [
            'currentUserId' => auth()->id(),
            'ticket' => [
                'id' => $supportTicket->id,
                'ticket_number' => $supportTicket->ticket_number,
                'subject' => $supportTicket->subject,
                'category' => $supportTicket->category,
                'priority' => $supportTicket->priority,
                'status' => $supportTicket->status,
                'created_at' => $supportTicket->created_at?->toDateTimeString(),
                'closed_at' => $supportTicket->closed_at?->toDateTimeString(),
                'user' => [
                    'id' => $supportTicket->user->id,
                    'name' => $supportTicket->user->name,
                    'email' => $supportTicket->user->email,
                    'role' => $supportTicket->user->role,
                    'avatar' => $supportTicket->user->avatar,
                ],
                'assigned_admin' => $supportTicket->assignedAdmin ? [
                    'id' => $supportTicket->assignedAdmin->id,
                    'name' => $supportTicket->assignedAdmin->name,
                ] : null,
                'closed_by' => $supportTicket->closedByAdmin ? [
                    'id' => $supportTicket->closedByAdmin->id,
                    'name' => $supportTicket->closedByAdmin->name,
                ] : null,
                'messages' => $supportTicket->messages->map(fn ($msg): array => [
                    'id' => $msg->id,
                    'body' => $msg->body,
                    'user' => [
                        'id' => $msg->user->id,
                        'name' => $msg->user->name,
                        'role' => $msg->user->role,
                        'avatar' => $msg->user->avatar,
                    ],
                    'attachments' => $msg->getMedia('attachments')->map(fn ($media): array => [
                        'id' => $media->id,
                        'url' => $media->getUrl(),
                        'name' => $media->file_name,
                        'size' => $media->size,
                    ])->all(),
                    'created_at' => $msg->created_at?->toDateTimeString(),
                ])->all(),
            ],
            'statusOptions' => array_map(
                fn (TicketStatus $s): array => ['value' => $s->value, 'label' => $s->label()],
                TicketStatus::cases(),
            ),
            'adminUsers' => User::query()
                ->where('role', UserRole::Admin)
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn (User $u): array => ['value' => $u->id, 'label' => $u->name])
                ->all(),
        ]);
    }

    /**
     * Reply to a support ticket.
     */
    public function reply(StoreTicketReplyRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->ticketService->addReply(
            $supportTicket,
            $request->user(),
            $request->validated(),
        );

        return back()->with('success', 'Reply sent successfully.');
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(UpdateTicketStatusRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $status = TicketStatus::from($request->validated('status'));

        $this->ticketService->updateStatus($supportTicket, $status, $request->user());

        return back()->with('success', "Ticket status updated to {$status->label()}.");
    }

    /**
     * Assign ticket to an admin.
     */
    public function assign(AssignTicketRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->ticketService->assignTicket($supportTicket, $request->validated('assigned_to'));

        return back()->with('success', 'Ticket assigned successfully.');
    }
}
