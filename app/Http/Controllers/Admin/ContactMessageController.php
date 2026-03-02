<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactMessageStatusUpdateRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ContactMessageController extends Controller
{
    /**
     * Display contact messages with filtering.
     */
    public function index(Request $request): Response
    {
        $query = trim($request->string('q')->toString());
        $status = strtolower(trim($request->string('status')->toString()));
        $sort = $request->string('sort')->toString();

        if (! in_array($sort, ['created_at', 'name', 'email', 'phone', 'status'], true)) {
            $sort = 'created_at';
        }

        $direction = strtolower($request->string('direction')->toString());

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        if (! in_array($status, [ContactMessage::STATUS_OPEN, ContactMessage::STATUS_CLOSED], true)) {
            $status = '';
        }

        $items = ContactMessage::query()
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($subQuery) use ($query): void {
                    $subQuery
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%")
                        ->orWhere('subject', 'like', "%{$query}%");
                });
            })
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString()
            ->through(fn (ContactMessage $message): array => [
                'id' => $message->id,
                'name' => $message->name,
                'email' => $message->email,
                'phone' => $message->phone,
                'subject' => $message->subject,
                'status' => $message->status,
                'created_at' => $message->created_at?->toDateTimeString(),
            ]);

        return inertia('admin/contact-messages/Index', [
            'items' => $items,
            'filters' => [
                'q' => $query,
                'status' => $status,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'counts' => [
                'open' => ContactMessage::query()->where('status', ContactMessage::STATUS_OPEN)->count(),
                'closed' => ContactMessage::query()->where('status', ContactMessage::STATUS_CLOSED)->count(),
                'all' => ContactMessage::query()->count(),
            ],
        ]);
    }

    /**
     * Display a contact message details page.
     */
    public function show(ContactMessage $contactMessage): Response
    {
        return inertia('admin/contact-messages/Show', [
            'message' => [
                'id' => $contactMessage->id,
                'name' => $contactMessage->name,
                'email' => $contactMessage->email,
                'phone' => $contactMessage->phone,
                'subject' => $contactMessage->subject,
                'message' => $contactMessage->message,
                'status' => $contactMessage->status,
                'ip' => $contactMessage->ip,
                'user_agent' => $contactMessage->user_agent,
                'created_at' => $contactMessage->created_at?->toDateTimeString(),
                'updated_at' => $contactMessage->updated_at?->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Update message status (open/closed).
     */
    public function updateStatus(ContactMessageStatusUpdateRequest $request, ContactMessage $contactMessage): RedirectResponse
    {
        $validated = $request->validated();

        $contactMessage->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->back()
            ->with('status', 'Message status updated successfully.');
    }
}
