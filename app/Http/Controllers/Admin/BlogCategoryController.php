<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCategoryStoreRequest;
use App\Http\Requests\Admin\BlogCategoryUpdateRequest;
use App\Models\BlogCategory;
use App\Support\SlugService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class BlogCategoryController extends Controller
{
    /**
     * Display blog category list or recycle bin.
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

        $items = BlogCategory::query()
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
            ->through(fn (BlogCategory $blogCategory): array => [
                'id' => $blogCategory->id,
                'name' => $blogCategory->name,
                'slug' => $blogCategory->slug,
                'description' => $blogCategory->description,
                'status' => $blogCategory->status,
                'posts_count' => $blogCategory->posts_count,
                'image_url' => $blogCategory->getFirstMediaUrl('image') ?: null,
                'updated_at' => $blogCategory->updated_at?->toDateTimeString(),
                'deleted_at' => $blogCategory->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/blog/categories/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'counts' => [
                'active' => BlogCategory::query()->count(),
                'trash' => BlogCategory::query()->onlyTrashed()->count(),
            ],
        ]);
    }

    /**
     * Show the create category page.
     */
    public function create(): Response
    {
        return inertia('admin/blog/categories/Create');
    }

    /**
     * Store a newly created blog category.
     */
    public function store(
        BlogCategoryStoreRequest $request,
        SlugService $slugService,
    ): RedirectResponse|JsonResponse {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));

        $blogCategory = BlogCategory::query()->create([
            'name' => $name,
            'slug' => $slugService->unique(BlogCategory::class, $slugBase),
            'description' => $validated['description'] ?? null,
            'status' => (string) $validated['status'],
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ]);

        if ($request->hasFile('image')) {
            $blogCategory
                ->addMediaFromRequest('image')
                ->toMediaCollection('image');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $blogCategory->id,
                'name' => $blogCategory->name,
                'slug' => $blogCategory->slug,
                'status' => $blogCategory->status,
                'description' => $blogCategory->description,
                'image_url' => $blogCategory->getFirstMediaUrl('image') ?: null,
            ], 201);
        }

        return redirect()
            ->route('admin.blog.categories.index')
            ->with('status', 'Blog category created successfully.');
    }

    /**
     * Show the edit category page.
     */
    public function edit(BlogCategory $blogCategory): Response
    {
        return inertia('admin/blog/categories/Edit', [
            'category' => $this->toFormPayload($blogCategory),
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(
        BlogCategoryUpdateRequest $request,
        BlogCategory $blogCategory,
        SlugService $slugService,
    ): RedirectResponse {
        $validated = $request->validated();
        $name = trim((string) $validated['name']);
        $slugBase = trim((string) ($validated['slug'] ?: $name));

        $blogCategory->forceFill([
            'name' => $name,
            'slug' => $slugService->unique(BlogCategory::class, $slugBase, $blogCategory->id),
            'description' => $validated['description'] ?? null,
            'status' => (string) $validated['status'],
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ])->save();

        if ($request->boolean('remove_image')) {
            $blogCategory->clearMediaCollection('image');
        }

        if ($request->hasFile('image')) {
            $blogCategory
                ->addMediaFromRequest('image')
                ->toMediaCollection('image');
        }

        return redirect()
            ->route('admin.blog.categories.index')
            ->with('status', 'Blog category updated successfully.');
    }

    /**
     * Move category to recycle bin.
     */
    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->delete();

        return redirect()
            ->back()
            ->with('status', 'Blog category moved to recycle bin.');
    }

    /**
     * Restore a category from recycle bin.
     */
    public function restore(BlogCategory $blogCategory): RedirectResponse
    {
        if (! $blogCategory->trashed()) {
            return redirect()
                ->route('admin.blog.categories.index', ['trash' => 1])
                ->with('status', 'Blog category is already active.');
        }

        $blogCategory->restore();

        return redirect()
            ->route('admin.blog.categories.index', ['trash' => 1])
            ->with('status', 'Blog category restored successfully.');
    }

    /**
     * Permanently delete a category from recycle bin.
     */
    public function forceDelete(BlogCategory $blogCategory): RedirectResponse
    {
        if (! $blogCategory->trashed()) {
            return redirect()
                ->back()
                ->withErrors(['category' => 'Only trashed categories can be permanently deleted.']);
        }

        $blogCategory->clearMediaCollection('image');
        $blogCategory->posts()->detach();
        $blogCategory->forceDelete();

        return redirect()
            ->back()
            ->with('status', 'Blog category permanently deleted.');
    }

    /**
     * Empty category recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $count = 0;

        DB::transaction(function () use (&$count): void {
            BlogCategory::query()
                ->onlyTrashed()
                ->get()
                ->each(function (BlogCategory $blogCategory) use (&$count): void {
                    $blogCategory->clearMediaCollection('image');
                    $blogCategory->posts()->detach();
                    $blogCategory->forceDelete();
                    $count++;
                });
        });

        return redirect()
            ->back()
            ->with('status', "Deleted {$count} category(s) from recycle bin.");
    }

    /**
     * Serialize category payload for form pages.
     *
     * @return array<string, mixed>
     */
    protected function toFormPayload(BlogCategory $blogCategory): array
    {
        return [
            'id' => $blogCategory->id,
            'name' => $blogCategory->name,
            'slug' => $blogCategory->slug,
            'description' => $blogCategory->description,
            'status' => $blogCategory->status,
            'meta_title' => $blogCategory->meta_title,
            'meta_description' => $blogCategory->meta_description,
            'image_url' => $blogCategory->getFirstMediaUrl('image') ?: null,
        ];
    }
}
