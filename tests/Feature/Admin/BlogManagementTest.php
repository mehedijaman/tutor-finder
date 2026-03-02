<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

it('moves a category to recycle bin and hides it from active list', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $category = BlogCategory::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.blog.categories.destroy', $category))
        ->assertRedirect();

    expect(BlogCategory::withTrashed()->findOrFail($category->id)->trashed())->toBeTrue();

    $this->actingAs($admin)
        ->get(route('admin.blog.categories.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/blog/categories/Index')
            ->where('filters.trash', false)
            ->has('items.data', 0));

    $this->actingAs($admin)
        ->get(route('admin.blog.categories.index', ['trash' => 1]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/blog/categories/Index')
            ->where('filters.trash', true)
            ->has('items.data', 1)
            ->where('items.data.0.id', $category->id));
});

it('restores a trashed blog post back to active list', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $post = BlogPost::factory()->create();
    $post->delete();

    $this->actingAs($admin)
        ->patch(route('admin.blog.posts.restore', $post->id))
        ->assertRedirect();

    expect(BlogPost::withTrashed()->findOrFail($post->id)->trashed())->toBeFalse();
});

it('force deleting a post removes media and detaches pivots', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $category = BlogCategory::factory()->create();
    $tag = BlogTag::factory()->create();
    $post = BlogPost::factory()->create();

    $post->categories()->sync([$category->id]);
    $post->tags()->sync([$tag->id]);
    $post->addMedia(UploadedFile::fake()->image('cover.jpg'))->toMediaCollection('cover');
    $mediaId = $post->getFirstMedia('cover')?->id;

    $this->actingAs($admin)
        ->delete(route('admin.blog.posts.destroy', $post))
        ->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('admin.blog.posts.force-delete', $post->id))
        ->assertRedirect();

    expect(BlogPost::withTrashed()->find($post->id))->toBeNull();

    $this->assertDatabaseMissing('blog_category_post', [
        'post_id' => $post->id,
        'category_id' => $category->id,
    ]);

    $this->assertDatabaseMissing('blog_post_tag', [
        'post_id' => $post->id,
        'tag_id' => $tag->id,
    ]);

    if ($mediaId !== null) {
        $this->assertDatabaseMissing('media', [
            'id' => $mediaId,
        ]);
    }
});

it('force deleting a category removes category image media', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $category = BlogCategory::factory()->create();
    $category->addMedia(UploadedFile::fake()->image('category.jpg'))->toMediaCollection('image');
    $mediaId = $category->getFirstMedia('image')?->id;

    $this->actingAs($admin)
        ->delete(route('admin.blog.categories.destroy', $category))
        ->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('admin.blog.categories.force-delete', $category->id))
        ->assertRedirect();

    expect(BlogCategory::withTrashed()->find($category->id))->toBeNull();

    if ($mediaId !== null) {
        $this->assertDatabaseMissing('media', [
            'id' => $mediaId,
        ]);
    }
});

it('empty recycle bin is idempotent for blog tags', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $first = BlogTag::factory()->create();
    $second = BlogTag::factory()->create();
    $first->delete();
    $second->delete();

    $this->actingAs($admin)
        ->delete(route('admin.blog.tags.empty-recycle-bin'))
        ->assertRedirect();

    expect(BlogTag::withTrashed()->whereIn('id', [$first->id, $second->id])->exists())->toBeFalse();

    $this->actingAs($admin)
        ->delete(route('admin.blog.tags.empty-recycle-bin'))
        ->assertRedirect();
});

it('stores unique slugs for posts with duplicate titles', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $category = BlogCategory::factory()->create(['status' => 'active']);
    $tag = BlogTag::factory()->create(['status' => 'active']);

    $payload = [
        'title' => 'Same Blog Title',
        'slug' => '',
        'summary' => 'Summary text',
        'content' => '<p>Content</p>',
        'status' => 'draft',
        'published_at' => null,
        'category_ids' => [$category->id],
        'tag_ids' => [$tag->id],
        'remove_cover' => false,
        'meta_title' => null,
        'meta_description' => null,
    ];

    $this->actingAs($admin)
        ->post(route('admin.blog.posts.store'), $payload)
        ->assertRedirect(route('admin.blog.posts.index', absolute: false));

    $this->actingAs($admin)
        ->post(route('admin.blog.posts.store'), $payload)
        ->assertRedirect(route('admin.blog.posts.index', absolute: false));

    $slugs = BlogPost::query()
        ->where('title', 'Same Blog Title')
        ->orderBy('id')
        ->pluck('slug')
        ->all();

    expect($slugs)->toBe(['same-blog-title', 'same-blog-title-2']);
});

it('creates category from json request and returns created payload', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)
        ->postJson(route('admin.blog.categories.store'), [
            'name' => 'Academic Tips',
            'slug' => '',
            'description' => 'Inline category create payload',
            'status' => 'active',
            'remove_image' => false,
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('name', 'Academic Tips')
        ->assertJsonPath('status', 'active');

    $this->assertDatabaseHas('blog_categories', [
        'name' => 'Academic Tips',
        'status' => 'active',
    ]);
});

it('creates tag from json request and returns created payload', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)
        ->postJson(route('admin.blog.tags.store'), [
            'name' => 'Exam Prep',
            'slug' => '',
            'status' => 'active',
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('name', 'Exam Prep')
        ->assertJsonPath('status', 'active');

    $this->assertDatabaseHas('blog_tags', [
        'name' => 'Exam Prep',
        'status' => 'active',
    ]);
});

it('forbids admin users without blog permissions from accessing management routes', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $adminWithoutPermissions = User::factory()->admin()->create();
    $category = BlogCategory::factory()->create();
    $post = BlogPost::factory()->create();

    $this->actingAs($adminWithoutPermissions)
        ->get(route('admin.blog.categories.index'))
        ->assertForbidden();

    $this->actingAs($adminWithoutPermissions)
        ->put(route('admin.blog.posts.update', $post), [
            'title' => 'Unauthorized Update',
            'slug' => 'unauthorized-update',
            'summary' => 'Summary',
            'content' => '<p>Body</p>',
            'status' => 'draft',
            'published_at' => null,
            'category_ids' => [],
            'tag_ids' => [],
            'remove_cover' => false,
            'meta_title' => null,
            'meta_description' => null,
        ])
        ->assertForbidden();
});

it('persists seo fields for categories and posts', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $category = BlogCategory::factory()->create([
        'status' => 'active',
        'meta_title' => null,
        'meta_description' => null,
    ]);
    $tag = BlogTag::factory()->create(['status' => 'active']);
    $post = BlogPost::factory()->create([
        'status' => 'draft',
        'meta_title' => null,
        'meta_description' => null,
    ]);
    $post->categories()->sync([$category->id]);
    $post->tags()->sync([$tag->id]);

    $this->actingAs($admin)
        ->put(route('admin.blog.categories.update', $category), [
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'status' => 'active',
            'remove_image' => false,
            'meta_title' => 'Category SEO Title',
            'meta_description' => 'Category SEO Description',
        ])
        ->assertRedirect(route('admin.blog.categories.index', absolute: false));

    $this->actingAs($admin)
        ->put(route('admin.blog.posts.update', $post), [
            'title' => $post->title,
            'slug' => $post->slug,
            'summary' => $post->summary,
            'content' => $post->content,
            'status' => 'draft',
            'published_at' => null,
            'category_ids' => [$category->id],
            'tag_ids' => [$tag->id],
            'remove_cover' => false,
            'meta_title' => 'Post SEO Title',
            'meta_description' => 'Post SEO Description',
        ])
        ->assertRedirect(route('admin.blog.posts.index', absolute: false));

    $this->assertDatabaseHas('blog_categories', [
        'id' => $category->id,
        'meta_title' => 'Category SEO Title',
        'meta_description' => 'Category SEO Description',
    ]);

    $this->assertDatabaseHas('blog_posts', [
        'id' => $post->id,
        'meta_title' => 'Post SEO Title',
        'meta_description' => 'Post SEO Description',
    ]);
});
