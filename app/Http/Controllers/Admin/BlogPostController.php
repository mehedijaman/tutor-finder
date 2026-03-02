<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogPostStoreRequest;
use App\Http\Requests\Admin\BlogPostUpdateRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Support\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class BlogPostController extends Controller
{
    /**
     * Display blog post list or recycle bin.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $search = trim($request->string('search')->toString());
        $sort = $request->string('sort')->toString();
        $direction = strtolower($request->string('direction')->toString()) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, ['title', 'slug', 'status', 'published_at', 'updated_at', 'created_at'], true)) {
            $sort = 'updated_at';
        }

        $items = BlogPost::query()
            ->with(['categories:id,name', 'tags:id,name'])
            ->when($showTrash, fn ($query) => $query->onlyTrashed())
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (BlogPost $blogPost): array => [
                'id' => $blogPost->id,
                'title' => $blogPost->title,
                'slug' => $blogPost->slug,
                'status' => $blogPost->status,
                'summary' => $blogPost->summary,
                'published_at' => $blogPost->published_at?->toDateTimeString(),
                'categories' => $blogPost->categories->pluck('name')->values()->all(),
                'tags' => $blogPost->tags->pluck('name')->values()->all(),
                'cover_url' => $blogPost->getFirstMediaUrl('cover') ?: null,
                'updated_at' => $blogPost->updated_at?->toDateTimeString(),
                'deleted_at' => $blogPost->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/blog/posts/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'counts' => [
                'active' => BlogPost::query()->count(),
                'trash' => BlogPost::query()->onlyTrashed()->count(),
            ],
        ]);
    }

    /**
     * Show create post page.
     */
    public function create(): Response
    {
        return inertia('admin/blog/posts/Create', [
            'categories' => $this->activeCategories(),
            'tags' => $this->activeTags(),
        ]);
    }

    /**
     * Store a newly created blog post.
     */
    public function store(
        BlogPostStoreRequest $request,
        SlugService $slugService,
    ): RedirectResponse {
        $validated = $request->validated();
        $status = (string) $validated['status'];

        $this->ensurePublishPermission($request, $status);

        $title = trim((string) $validated['title']);
        $slugBase = trim((string) ($validated['slug'] ?: $title));

        $publishedAt = $this->normalizePublishedAt(
            $status,
            $validated['published_at'] ?? null,
        );

        $blogPost = DB::transaction(function () use (
            $request,
            $validated,
            $status,
            $title,
            $slugBase,
            $publishedAt,
            $slugService,
        ): BlogPost {
            $blogPost = BlogPost::query()->create([
                'title' => $title,
                'slug' => $slugService->unique(BlogPost::class, $slugBase),
                'summary' => $validated['summary'] ?? null,
                'content' => (string) $validated['content'],
                'status' => $status,
                'published_at' => $publishedAt,
                'author_admin_id' => $request->user()?->getAuthIdentifier(),
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
            ]);

            $blogPost->categories()->sync($validated['category_ids'] ?? []);
            $blogPost->tags()->sync($validated['tag_ids'] ?? []);

            if ($request->hasFile('cover')) {
                $blogPost
                    ->addMediaFromRequest('cover')
                    ->toMediaCollection('cover');
            }

            return $blogPost;
        });

        return redirect()
            ->route('admin.blog.posts.index')
            ->with('status', "Blog post \"{$blogPost->title}\" created successfully.");
    }

    /**
     * Show edit post page.
     */
    public function edit(BlogPost $blogPost): Response
    {
        $blogPost->load(['categories:id', 'tags:id']);

        return inertia('admin/blog/posts/Edit', [
            'post' => $this->toFormPayload($blogPost),
            'categories' => $this->activeCategories(),
            'tags' => $this->activeTags(),
        ]);
    }

    /**
     * Update the specified post.
     */
    public function update(
        BlogPostUpdateRequest $request,
        BlogPost $blogPost,
        SlugService $slugService,
    ): RedirectResponse {
        $validated = $request->validated();
        $status = (string) $validated['status'];

        $this->ensurePublishPermission($request, $status);

        $title = trim((string) $validated['title']);
        $slugBase = trim((string) ($validated['slug'] ?: $title));

        $publishedAt = $this->normalizePublishedAt(
            $status,
            $validated['published_at'] ?? null,
        );

        DB::transaction(function () use (
            $request,
            $validated,
            $blogPost,
            $status,
            $title,
            $slugBase,
            $publishedAt,
            $slugService,
        ): void {
            $blogPost->forceFill([
                'title' => $title,
                'slug' => $slugService->unique(BlogPost::class, $slugBase, $blogPost->id),
                'summary' => $validated['summary'] ?? null,
                'content' => (string) $validated['content'],
                'status' => $status,
                'published_at' => $publishedAt,
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
            ])->save();

            $blogPost->categories()->sync($validated['category_ids'] ?? []);
            $blogPost->tags()->sync($validated['tag_ids'] ?? []);

            if ($request->boolean('remove_cover')) {
                $blogPost->clearMediaCollection('cover');
            }

            if ($request->hasFile('cover')) {
                $blogPost
                    ->addMediaFromRequest('cover')
                    ->toMediaCollection('cover');
            }
        });

        return redirect()
            ->route('admin.blog.posts.index')
            ->with('status', "Blog post \"{$blogPost->title}\" updated successfully.");
    }

    /**
     * Move post to recycle bin.
     */
    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        $blogPost->delete();

        return redirect()
            ->back()
            ->with('status', 'Blog post moved to recycle bin.');
    }

    /**
     * Restore a post from recycle bin.
     */
    public function restore(BlogPost $blogPost): RedirectResponse
    {
        if (! $blogPost->trashed()) {
            return redirect()
                ->route('admin.blog.posts.index', ['trash' => 1])
                ->with('status', 'Blog post is already active.');
        }

        $blogPost->restore();

        return redirect()
            ->route('admin.blog.posts.index', ['trash' => 1])
            ->with('status', 'Blog post restored successfully.');
    }

    /**
     * Permanently delete a post from recycle bin.
     */
    public function forceDelete(BlogPost $blogPost): RedirectResponse
    {
        if (! $blogPost->trashed()) {
            return redirect()
                ->back()
                ->withErrors(['post' => 'Only trashed posts can be permanently deleted.']);
        }

        DB::transaction(function () use ($blogPost): void {
            $blogPost->categories()->detach();
            $blogPost->tags()->detach();
            $blogPost->clearMediaCollection('cover');
            $blogPost->forceDelete();
        });

        return redirect()
            ->back()
            ->with('status', 'Blog post permanently deleted.');
    }

    /**
     * Empty posts recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $count = 0;

        DB::transaction(function () use (&$count): void {
            BlogPost::query()
                ->onlyTrashed()
                ->get()
                ->each(function (BlogPost $blogPost) use (&$count): void {
                    $blogPost->categories()->detach();
                    $blogPost->tags()->detach();
                    $blogPost->clearMediaCollection('cover');
                    $blogPost->forceDelete();
                    $count++;
                });
        });

        return redirect()
            ->back()
            ->with('status', "Deleted {$count} post(s) from recycle bin.");
    }

    /**
     * Get active categories for post form options.
     *
     * @return array<int, array{id: int, name: string}>
     */
    protected function activeCategories(): array
    {
        return BlogCategory::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (BlogCategory $blogCategory): array => [
                'id' => $blogCategory->id,
                'name' => $blogCategory->name,
            ])
            ->all();
    }

    /**
     * Get active tags for post form options.
     *
     * @return array<int, array{id: int, name: string}>
     */
    protected function activeTags(): array
    {
        return BlogTag::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (BlogTag $blogTag): array => [
                'id' => $blogTag->id,
                'name' => $blogTag->name,
            ])
            ->all();
    }

    /**
     * Serialize post payload for form page.
     *
     * @return array<string, mixed>
     */
    protected function toFormPayload(BlogPost $blogPost): array
    {
        return [
            'id' => $blogPost->id,
            'title' => $blogPost->title,
            'slug' => $blogPost->slug,
            'summary' => $blogPost->summary,
            'content' => $blogPost->content,
            'status' => $blogPost->status,
            'published_at' => $blogPost->published_at?->format('Y-m-d\TH:i'),
            'meta_title' => $blogPost->meta_title,
            'meta_description' => $blogPost->meta_description,
            'category_ids' => $blogPost->categories->pluck('id')->values()->all(),
            'tag_ids' => $blogPost->tags->pluck('id')->values()->all(),
            'cover_url' => $blogPost->getFirstMediaUrl('cover') ?: null,
        ];
    }

    /**
     * Ensure user has permission to publish posts.
     */
    protected function ensurePublishPermission(Request $request, string $status): void
    {
        if ($status !== 'published') {
            return;
        }

        abort_unless($request->user()?->can('blog-post-publish') ?? false, 403);
    }

    /**
     * Normalize published timestamp from form payload.
     */
    protected function normalizePublishedAt(string $status, mixed $publishedAt): ?Carbon
    {
        if ($status !== 'published') {
            return null;
        }

        if ($publishedAt === null || trim((string) $publishedAt) === '') {
            return now();
        }

        return Carbon::parse((string) $publishedAt);
    }
}
