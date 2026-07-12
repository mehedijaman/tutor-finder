<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Display admin notifications.
     */
    public function index(Request $request): Response
    {
        $status = strtolower(trim($request->string('status')->toString()));

        if (! in_array($status, ['unread', 'read'], true)) {
            $status = '';
        }

        $items = $request->user()
            ?->notifications()
            ->when($status === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($status === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($notification): array => [
                'id' => $notification->id,
                'title' => (string) ($notification->data['title'] ?? 'Notification'),
                'message' => (string) ($notification->data['message'] ?? ''),
                'event' => (string) ($notification->data['event'] ?? 'general'),
                'url' => (string) ($notification->data['url'] ?? ''),
                'read_at' => $notification->read_at?->toDateTimeString(),
                'created_at' => $notification->created_at?->toDateTimeString(),
            ]);

        return inertia('admin/notifications/Index', [
            'items' => $items,
            'filters' => [
                'status' => $status,
            ],
            'counts' => [
                'all' => $request->user()?->notifications()->count() ?? 0,
                'unread' => $request->user()?->unreadNotifications()->count() ?? 0,
            ],
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $notificationId): RedirectResponse
    {
        $notification = $request->user()
            ?->notifications()
            ->whereKey($notificationId)
            ->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return back();
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()?->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'All notifications marked as read.');
    }
}
