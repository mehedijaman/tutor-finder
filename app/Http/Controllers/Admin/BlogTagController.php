<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogTagStoreRequest;
use App\Http\Requests\Admin\BlogTagUpdateRequest;
use App\Models\BlogTag;
use App\Support\SlugService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class BlogTagController extends Controller
{
    /**
     * Display blog tags list or recycle bin.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $search = trim($request->string('search')->toString());
        $sort = $request->string('sort')->toString();
        $direction = strtolower($request->string('direction')->toString()) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, ['name', 'slug', 'status', 'updated_at', 'created_at'], true)) {
            $sort = 'updated_at';
        }

        $items = BlogTag::query()
            ->withCount('posts')
            ->when($showTrash, fn ($query) => $query->onlyTrashed())
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (BlogTag $blogTag): array => [
                'id' => $blogTag->id,
                'name' => $blogTag->name,
                'slug' => $blogTag->slug,
                'status' => $blogTag->status,
                'posts_count' => $blogTag->posts_count,
                'updated_at' => $blogTag->updated_at?->toDateTimeString(),
                'deleted_at' => $blogTag->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/blog/tags/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'counts' => [
                'active' => BlogTag::query()->count(),
                'trash' => BlogTag::query()->onlyTrashed()->count(),
            ],
        ]);
    }

    /**
     * Show create tag page.
     */
    public function create(): Response
    {
        return inertia('admin/blog/tags/Create');
    }

    /**
     * Store a new blog tag.
     */
    public function store(
        BlogTagStoreRequest $request,
        SlugService $slugService,
    ): RedirectResponse|JsonResponse {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));

        $blogTag = BlogTag::query()->create([
            'name' => $name,
            'slug' => $slugService->unique(BlogTag::class, $slugBase),
            'status' => (string) $validated['status'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $blogTag->id,
                'name' => $blogTag->name,
                'slug' => $blogTag->slug,
                'status' => $blogTag->status,
            ], 201);
        }

        return redirect()
            ->route('admin.blog.tags.index')
            ->with('status', 'Blog tag created successfully.');
    }

    /**
     * Show edit tag page.
     */
    public function edit(BlogTag $blogTag): Response
    {
        return inertia('admin/blog/tags/Edit', [
            'tag' => [
                'id' => $blogTag->id,
                'name' => $blogTag->name,
                'slug' => $blogTag->slug,
                'status' => $blogTag->status,
            ],
        ]);
    }

    /**
     * Update an existing blog tag.
     */
    public function update(
        BlogTagUpdateRequest $request,
        BlogTag $blogTag,
        SlugService $slugService,
    ): RedirectResponse {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));

        $blogTag->forceFill([
            'name' => $name,
            'slug' => $slugService->unique(BlogTag::class, $slugBase, $blogTag->id),
            'status' => (string) $validated['status'],
        ])->save();

        return redirect()
            ->route('admin.blog.tags.index')
            ->with('status', 'Blog tag updated successfully.');
    }

    /**
     * Move tag to recycle bin.
     */
    public function destroy(BlogTag $blogTag): RedirectResponse
    {
        $blogTag->delete();

        return redirect()
            ->back()
            ->with('status', 'Blog tag moved to recycle bin.');
    }

    /**
     * Restore tag from recycle bin.
     */
    public function restore(BlogTag $blogTag): RedirectResponse
    {
        if (! $blogTag->trashed()) {
            return redirect()
                ->route('admin.blog.tags.index', ['trash' => 1])
                ->with('status', 'Blog tag is already active.');
        }

        $blogTag->restore();

        return redirect()
            ->route('admin.blog.tags.index', ['trash' => 1])
            ->with('status', 'Blog tag restored successfully.');
    }

    /**
     * Permanently delete a tag from recycle bin.
     */
    public function forceDelete(BlogTag $blogTag): RedirectResponse
    {
        if (! $blogTag->trashed()) {
            return redirect()
                ->back()
                ->withErrors(['tag' => 'Only trashed tags can be permanently deleted.']);
        }

        $blogTag->posts()->detach();
        $blogTag->forceDelete();

        return redirect()
            ->back()
            ->with('status', 'Blog tag permanently deleted.');
    }

    /**
     * Empty tags recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $count = BlogTag::query()
            ->onlyTrashed()
            ->get()
            ->reduce(function (int $carry, BlogTag $blogTag): int {
                $blogTag->posts()->detach();
                $blogTag->forceDelete();

                return $carry + 1;
            }, 0);

        return redirect()
            ->back()
            ->with('status', "Deleted {$count} tag(s) from recycle bin.");
    }
}
