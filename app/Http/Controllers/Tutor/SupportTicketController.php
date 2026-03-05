<?php

namespace App\Http\Controllers\Tutor;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupportTicketRequest;
use App\Http\Requests\StoreTicketReplyRequest;
use App\Models\SupportTicket;
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
     * Display tutor's support tickets.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $status = trim($request->string('status')->toString());

        $items = SupportTicket::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->with(['latestMessage'])
            ->when($status !== '' && TicketStatus::tryFrom($status), fn ($b) => $b->where('status', $status))
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (SupportTicket $ticket): array => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'category' => $ticket->category,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'last_reply_at' => $ticket->latestMessage?->created_at?->toDateTimeString(),
                'created_at' => $ticket->created_at?->toDateTimeString(),
            ]);

        return inertia('tutor/support-tickets/Index', [
            'items' => $items,
            'filters' => [
                'status' => $status,
            ],
            'counts' => $this->ticketService->getStatusCounts($user),
        ]);
    }

    /**
     * Show ticket creation form.
     */
    public function create(): Response
    {
        return inertia('tutor/support-tickets/Create', [
            'categoryOptions' => array_map(
                fn (TicketCategory $c): array => ['value' => $c->value, 'label' => $c->label()],
                TicketCategory::cases(),
            ),
            'priorityOptions' => array_map(
                fn (TicketPriority $p): array => ['value' => $p->value, 'label' => $p->label()],
                TicketPriority::cases(),
            ),
        ]);
    }

    /**
     * Store a new support ticket.
     */
    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        $ticket = $this->ticketService->createTicket(
            $request->user(),
            $request->validated(),
        );

        return redirect()
            ->route('tutor.tickets.show', $ticket)
            ->with('success', 'Support ticket created successfully.');
    }

    /**
     * Display a support ticket thread.
     */
    public function show(Request $request, SupportTicket $supportTicket): Response
    {
        $this->authorize('view', $supportTicket);

        $supportTicket->load([
            'messages' => fn ($q) => $q->with(['user:id,name,role,avatar', 'media'])->orderBy('created_at'),
        ]);

        return inertia('tutor/support-tickets/Show', [
            'currentUserId' => $request->user()->id,
            'ticket' => [
                'id' => $supportTicket->id,
                'ticket_number' => $supportTicket->ticket_number,
                'subject' => $supportTicket->subject,
                'category' => $supportTicket->category,
                'priority' => $supportTicket->priority,
                'status' => $supportTicket->status,
                'created_at' => $supportTicket->created_at?->toDateTimeString(),
                'closed_at' => $supportTicket->closed_at?->toDateTimeString(),
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
        ]);
    }

    /**
     * Reply to a support ticket.
     */
    public function reply(StoreTicketReplyRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('reply', $supportTicket);

        $this->ticketService->addReply(
            $supportTicket,
            $request->user(),
            $request->validated(),
        );

        return back()->with('success', 'Reply sent successfully.');
    }
}
