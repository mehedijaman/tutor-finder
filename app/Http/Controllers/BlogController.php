<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Response;

class BlogController extends Controller
{
    /**
     * Display published blog posts with optional filters.
     */
    public function index(Request $request): Response
    {
        $query = trim($request->string('q')->toString());
        $categorySlug = trim($request->string('category')->toString());
        $tagSlug = trim($request->string('tag')->toString());

        $posts = $this->publishedPostsQuery()
            ->with(['categories:id,name,slug', 'tags:id,name,slug', 'author:id,name'])
            ->when($query !== '', function (Builder $builder) use ($query): void {
                $builder->where(function (Builder $nested) use ($query): void {
                    $nested
                        ->where('title', 'like', "%{$query}%")
                        ->orWhere('summary', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                });
            })
            ->when($categorySlug !== '', function (Builder $builder) use ($categorySlug): void {
                $builder->whereHas('categories', function (Builder $relatedQuery) use ($categorySlug): void {
                    $relatedQuery->where('slug', $categorySlug);
                });
            })
            ->when($tagSlug !== '', function (Builder $builder) use ($tagSlug): void {
                $builder->whereHas('tags', function (Builder $relatedQuery) use ($tagSlug): void {
                    $relatedQuery->where('slug', $tagSlug);
                });
            })
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString()
            ->through(fn (BlogPost $blogPost): array => [
                'id' => $blogPost->id,
                'title' => $blogPost->title,
                'slug' => $blogPost->slug,
                'summary' => $blogPost->summary,
                'published_at' => $blogPost->published_at?->toDateTimeString(),
                'cover_url' => $blogPost->getFirstMediaUrl('cover') ?: null,
                'author_name' => $blogPost->author?->name,
                'categories' => $blogPost->categories->map(fn (BlogCategory $category): array => [
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])->values()->all(),
                'tags' => $blogPost->tags->map(fn (BlogTag $tag): array => [
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])->values()->all(),
            ]);

        $categories = BlogCategory::query()
            ->where('status', 'active')
            ->whereHas('posts', fn (Builder $builder) => $this->applyPublishedScope($builder))
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (BlogCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->values()
            ->all();

        $tags = BlogTag::query()
            ->where('status', 'active')
            ->whereHas('posts', fn (Builder $builder) => $this->applyPublishedScope($builder))
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (BlogTag $tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])
            ->values()
            ->all();

        $selectedCategory = null;

        if ($categorySlug !== '') {
            $selectedCategory = BlogCategory::query()
                ->where('status', 'active')
                ->where('slug', $categorySlug)
                ->first();
        }

        $metaTitle = $selectedCategory?->meta_title
            ?: ($selectedCategory?->name
                ? "{$selectedCategory->name} Blog"
                : ((string) config('app.name')).' Blog');
        $metaDescription = $selectedCategory?->meta_description
            ?: ($selectedCategory?->description
                ? (string) $selectedCategory->description
                : 'Read the latest articles, announcements, and educational resources.');

        return inertia('Blog', [
            'posts' => $posts,
            'filters' => [
                'q' => $query,
                'category' => $categorySlug,
                'tag' => $tagSlug,
            ],
            'categories' => $categories,
            'tags' => $tags,
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
            ],
        ]);
    }

    /**
     * Display a published blog post by slug.
     */
    public function show(string $slug): Response
    {
        $post = $this->publishedPostsQuery()
            ->with(['categories:id,name,slug', 'tags:id,name,slug', 'author:id,name'])
            ->where('slug', $slug)
            ->firstOrFail();

        $metaTitle = $post->meta_title ?: $post->title;
        $metaDescription = $post->meta_description
            ?: (trim((string) $post->summary) !== ''
                ? (string) $post->summary
                : 'Read this article on our blog.');

        return inertia('PostShow', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'summary' => $post->summary,
                'content' => $post->content,
                'published_at' => $post->published_at?->toDateTimeString(),
                'cover_url' => $post->getFirstMediaUrl('cover') ?: null,
                'author_name' => $post->author?->name,
                'categories' => $post->categories->map(fn (BlogCategory $category): array => [
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])->values()->all(),
                'tags' => $post->tags->map(fn (BlogTag $tag): array => [
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])->values()->all(),
                'meta_title' => $post->meta_title,
                'meta_description' => $post->meta_description,
            ],
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
            ],
        ]);
    }

    /**
     * Get base query for publicly visible published posts.
     */
    protected function publishedPostsQuery(): Builder
    {
        return BlogPost::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Apply published visibility scope on relation queries.
     */
    protected function applyPublishedScope(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
