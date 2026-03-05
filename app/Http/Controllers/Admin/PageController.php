<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PageStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageStoreRequest;
use App\Http\Requests\Admin\PageUpdateRequest;
use App\Models\Page;
use App\Support\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class PageController extends Controller
{
    public function __construct(
        private SlugService $slugService,
    ) {}

    /**
     * Display page list or recycle bin.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $query = trim($request->string('q')->toString());
        $status = strtolower(trim($request->string('status')->toString()));

        if (! in_array($status, [PageStatus::Active->value, PageStatus::Inactive->value], true)) {
            $status = '';
        }

        $items = Page::query()
            ->when($showTrash, fn ($builder) => $builder->onlyTrashed())
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($subQuery) use ($query): void {
                    $subQuery
                        ->where('title', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%");
                });
            })
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Page $page): array => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'status' => $page->status,
                'is_system' => $page->is_system,
                'updated_at' => $page->updated_at?->toDateTimeString(),
                'created_at' => $page->created_at?->toDateTimeString(),
                'deleted_at' => $page->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/pages/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'q' => $query,
                'status' => $status,
            ],
            'counts' => [
                'active' => Page::query()->count(),
                'trash' => Page::query()->onlyTrashed()->count(),
            ],
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Show the create page form.
     */
    public function create(): Response
    {
        return inertia('admin/pages/Create', [
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Persist a new page.
     */
    public function store(PageStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $slug = $this->slugService->unique(Page::class, $validated['slug'] ?: $validated['title']);

        $page = Page::query()->create([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'] ?? '',
            'status' => $validated['status'],
            'is_system' => false,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ]);

        if ($request->hasFile('featured_image')) {
            $page->addMediaFromRequest('featured_image')
                ->toMediaCollection('featured_image');
        }

        return redirect()
            ->route('admin.pages.index')
            ->with('status', 'Page created successfully.');
    }

    /**
     * Show the edit page form.
     */
    public function edit(Page $page): Response
    {
        return inertia('admin/pages/Edit', [
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'content' => $page->content,
                'status' => $page->status,
                'is_system' => $page->is_system,
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
                'featured_image_url' => $page->getFirstMediaUrl('featured_image') ?: null,
            ],
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Update an existing page.
     */
    public function update(PageUpdateRequest $request, Page $page): RedirectResponse
    {
        $validated = $request->validated();

        $slug = $this->slugService->unique(
            Page::class,
            $validated['slug'] ?: $validated['title'],
            $page->id,
        );

        $page->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'] ?? '',
            'status' => $validated['status'],
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ]);

        if ($request->boolean('remove_featured_image')) {
            $page->clearMediaCollection('featured_image');
        }

        if ($request->hasFile('featured_image')) {
            $page->addMediaFromRequest('featured_image')
                ->toMediaCollection('featured_image');
        }

        return redirect()
            ->route('admin.pages.index')
            ->with('status', 'Page updated successfully.');
    }

    /**
     * Move page to recycle bin.
     */
    public function destroy(Page $page): RedirectResponse
    {
        if ($page->isSystem()) {
            return redirect()
                ->back()
                ->withErrors(['page' => 'System pages cannot be deleted.']);
        }

        $page->delete();

        return redirect()
            ->back()
            ->with('status', 'Page moved to recycle bin.');
    }

    /**
     * Update page status from the list view.
     */
    public function updateStatus(Request $request, Page $page): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $page->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->back()
            ->with('status', 'Page status updated successfully.');
    }

    /**
     * Restore a trashed page.
     */
    public function restore(Page $page): RedirectResponse
    {
        if (! $page->trashed()) {
            return redirect()
                ->route('admin.pages.index', ['trash' => 1])
                ->with('status', 'Page is already active.');
        }

        $page->restore();

        return redirect()
            ->route('admin.pages.index', ['trash' => 1])
            ->with('status', 'Page restored successfully.');
    }

    /**
     * Permanently delete a trashed page.
     */
    public function forceDelete(Page $page): RedirectResponse
    {
        if ($page->isSystem()) {
            return redirect()
                ->back()
                ->withErrors(['page' => 'System pages cannot be permanently deleted.']);
        }

        if (! $page->trashed()) {
            return redirect()
                ->back()
                ->withErrors(['page' => 'Only trashed pages can be permanently deleted.']);
        }

        $page->forceDelete();

        return redirect()
            ->back()
            ->with('status', 'Page permanently deleted.');
    }

    /**
     * Empty page recycle bin (excludes system pages).
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $count = Page::query()->onlyTrashed()->where('is_system', false)->count();

        Page::query()->onlyTrashed()->where('is_system', false)->forceDelete();

        return redirect()
            ->back()
            ->with('status', "Deleted {$count} page(s) from recycle bin.");
    }

    /**
     * Get available status options for forms and filters.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return [
            ['value' => PageStatus::Active->value, 'label' => 'Active'],
            ['value' => PageStatus::Inactive->value, 'label' => 'Inactive'],
        ];
    }
}
