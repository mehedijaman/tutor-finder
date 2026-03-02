<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Inertia\Testing\AssertableInertia as Assert;

it('shows only published and non-trashed posts on the blog index', function () {
    $publishedPost = BlogPost::factory()->create([
        'status' => 'published',
        'published_at' => now()->subHour(),
    ]);
    BlogPost::factory()->create([
        'status' => 'draft',
        'published_at' => now()->subHour(),
    ]);
    BlogPost::factory()->create([
        'status' => 'published',
        'published_at' => now()->addHour(),
    ]);
    $trashedPost = BlogPost::factory()->create([
        'status' => 'published',
        'published_at' => now()->subHour(),
    ]);
    $trashedPost->delete();

    $this->get(route('blog'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Blog')
            ->has('posts.data', 1)
            ->where('posts.data.0.slug', $publishedPost->slug));
});

it('returns 404 for unpublished, future, or trashed blog detail pages', function () {
    $publishedPost = BlogPost::factory()->create([
        'status' => 'published',
        'published_at' => now()->subHour(),
    ]);
    $draftPost = BlogPost::factory()->create([
        'status' => 'draft',
        'published_at' => now()->subHour(),
    ]);
    $futurePost = BlogPost::factory()->create([
        'status' => 'published',
        'published_at' => now()->addHour(),
    ]);
    $trashedPost = BlogPost::factory()->create([
        'status' => 'published',
        'published_at' => now()->subHour(),
    ]);
    $trashedPost->delete();

    $this->get(route('blog.show', $publishedPost->slug))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('PostShow')
            ->where('post.slug', $publishedPost->slug));

    $this->get(route('blog.show', $draftPost->slug))->assertNotFound();
    $this->get(route('blog.show', $futurePost->slug))->assertNotFound();
    $this->get(route('blog.show', $trashedPost->slug))->assertNotFound();
});

it('uses seo fallback for post detail when meta fields are empty', function () {
    $post = BlogPost::factory()->create([
        'title' => 'Fallback SEO Title Source',
        'summary' => 'Fallback SEO Description Source',
        'meta_title' => null,
        'meta_description' => null,
        'status' => 'published',
        'published_at' => now()->subHour(),
    ]);

    $this->get(route('blog.show', $post->slug))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('PostShow')
            ->where('meta.title', 'Fallback SEO Title Source')
            ->where('meta.description', 'Fallback SEO Description Source'));
});

it('uses explicit seo fields for post detail when provided', function () {
    $post = BlogPost::factory()->create([
        'meta_title' => 'Explicit Post Meta Title',
        'meta_description' => 'Explicit Post Meta Description',
        'status' => 'published',
        'published_at' => now()->subHour(),
    ]);

    $this->get(route('blog.show', $post->slug))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('PostShow')
            ->where('meta.title', 'Explicit Post Meta Title')
            ->where('meta.description', 'Explicit Post Meta Description'));
});

it('uses category seo fields on filtered blog index and falls back when empty', function () {
    $seoCategory = BlogCategory::factory()->create([
        'status' => 'active',
        'meta_title' => 'Category Meta Title',
        'meta_description' => 'Category Meta Description',
    ]);
    $fallbackCategory = BlogCategory::factory()->create([
        'status' => 'active',
        'name' => 'Fallback Category',
        'slug' => 'fallback-category',
        'description' => 'Fallback category description',
        'meta_title' => null,
        'meta_description' => null,
    ]);
    $tag = BlogTag::factory()->create(['status' => 'active']);

    $seoPost = BlogPost::factory()->create([
        'status' => 'published',
        'published_at' => now()->subHour(),
    ]);
    $seoPost->categories()->sync([$seoCategory->id]);
    $seoPost->tags()->sync([$tag->id]);

    $fallbackPost = BlogPost::factory()->create([
        'status' => 'published',
        'published_at' => now()->subHour(),
    ]);
    $fallbackPost->categories()->sync([$fallbackCategory->id]);
    $fallbackPost->tags()->sync([$tag->id]);

    $this->get(route('blog', ['category' => $seoCategory->slug]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Blog')
            ->where('meta.title', 'Category Meta Title')
            ->where('meta.description', 'Category Meta Description'));

    $this->get(route('blog', ['category' => $fallbackCategory->slug]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Blog')
            ->where('meta.title', 'Fallback Category Blog')
            ->where('meta.description', 'Fallback category description'));
});
