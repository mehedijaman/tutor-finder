<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NoticeAudience;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NoticeStoreRequest;
use App\Http\Requests\Admin\NoticeUpdateRequest;
use App\Jobs\SendNoticeNotificationsJob;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class NoticeController extends Controller
{
    /**
     * Display notice list or recycle bin.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $query = trim($request->string('q')->toString());
        $audience = strtolower(trim($request->string('audience')->toString()));
        $status = strtolower(trim($request->string('status')->toString()));

        if (! in_array($audience, [NoticeAudience::Tutor->value, NoticeAudience::Guardian->value, NoticeAudience::Both->value], true)) {
            $audience = '';
        }

        if (! in_array($status, ['active', 'inactive', 'expired'], true)) {
            $status = '';
        }

        $items = Notice::query()
            ->with('createdBy:id,name')
            ->when($showTrash, fn ($builder) => $builder->onlyTrashed())
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($subQuery) use ($query): void {
                    $subQuery
                        ->where('title', 'like', "%{$query}%")
                        ->orWhere('body', 'like', "%{$query}%");
                });
            })
            ->when($audience !== '', fn ($builder) => $builder->where('audience', $audience))
            ->when($status === 'active', fn ($builder) => $builder->active())
            ->when($status === 'inactive', fn ($builder) => $builder->where('is_active', false))
            ->when($status === 'expired', function ($builder): void {
                $builder->where('is_active', true)
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Notice $notice): array => [
                'id' => $notice->id,
                'title' => $notice->title,
                'audience' => $notice->audience,
                'is_active' => $notice->is_active,
                'is_expired' => $notice->isExpired(),
                'expires_at' => $notice->expires_at?->toDateTimeString(),
                'published_at' => $notice->published_at?->toDateTimeString(),
                'created_by' => $notice->createdBy?->name,
                'updated_at' => $notice->updated_at?->toDateTimeString(),
                'deleted_at' => $notice->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/notices/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'q' => $query,
                'audience' => $audience,
                'status' => $status,
            ],
            'counts' => [
                'active' => Notice::query()->active()->count(),
                'trash' => Notice::query()->onlyTrashed()->count(),
            ],
            'audienceOptions' => $this->audienceOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Show the create notice page.
     */
    public function create(): Response
    {
        return inertia('admin/notices/Create', [
            'audienceOptions' => $this->audienceOptions(),
        ]);
    }

    /**
     * Persist a new notice.
     */
    public function store(NoticeStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $notice = Notice::query()->create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'audience' => $validated['audience'],
            'expires_at' => $validated['expires_at'] ?? null,
            'published_at' => now(),
            'created_by_user_id' => $request->user()->id,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if ($notice->is_active) {
            SendNoticeNotificationsJob::dispatch($notice);
        }

        return redirect()
            ->route('admin.notices.index')
            ->with('success', 'Notice created successfully.');
    }

    /**
     * Show the edit notice page.
     */
    public function edit(Notice $notice): Response
    {
        return inertia('admin/notices/Edit', [
            'notice' => [
                'id' => $notice->id,
                'title' => $notice->title,
                'body' => $notice->body,
                'audience' => $notice->audience->value,
                'expires_at' => $notice->expires_at?->format('Y-m-d\TH:i'),
                'is_active' => $notice->is_active,
                'published_at' => $notice->published_at?->toDateTimeString(),
            ],
            'audienceOptions' => $this->audienceOptions(),
        ]);
    }

    /**
     * Update existing notice.
     */
    public function update(NoticeUpdateRequest $request, Notice $notice): RedirectResponse
    {
        $validated = $request->validated();

        $notice->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'audience' => $validated['audience'],
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()
            ->route('admin.notices.index')
            ->with('success', 'Notice updated successfully.');
    }

    /**
     * Soft delete a notice.
     */
    public function destroy(Notice $notice): RedirectResponse
    {
        $notice->delete();

        return redirect()
            ->back()
            ->with('success', 'Notice moved to recycle bin.');
    }

    /**
     * Restore a soft-deleted notice.
     */
    public function restore(Notice $notice): RedirectResponse
    {
        $notice->restore();

        return redirect()
            ->back()
            ->with('success', 'Notice restored successfully.');
    }

    /**
     * Permanently delete a notice.
     */
    public function forceDelete(Notice $notice): RedirectResponse
    {
        $notice->forceDelete();

        return redirect()
            ->back()
            ->with('success', 'Notice permanently deleted.');
    }

    /**
     * Empty the recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        Notice::query()->onlyTrashed()->forceDelete();

        return redirect()
            ->back()
            ->with('success', 'Recycle bin emptied.');
    }

    /**
     * Audience filter options.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function audienceOptions(): array
    {
        return array_map(
            fn (NoticeAudience $audience): array => [
                'value' => $audience->value,
                'label' => $audience->label(),
            ],
            NoticeAudience::cases()
        );
    }

    /**
     * Status filter options.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'inactive', 'label' => 'Inactive'],
            ['value' => 'expired', 'label' => 'Expired'],
        ];
    }
}
