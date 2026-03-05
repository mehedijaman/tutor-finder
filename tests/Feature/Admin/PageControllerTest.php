<?php

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->admin()->create();
    $this->admin->assignRole('super-admin');
});

it('admin can view pages index', function () {
    Page::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.pages.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('admin/pages/Index'));
});

it('admin can view trashed pages', function () {
    Page::factory()->count(2)->create();
    $trashedPage = Page::factory()->create();
    $trashedPage->delete();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.pages.index', ['trash' => 1]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/pages/Index')
        ->has('items.data', 1)
    );
});

it('admin can filter pages by status', function () {
    Page::factory()->active()->create();
    Page::factory()->inactive()->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.pages.index', ['status' => 'active']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/pages/Index')
        ->has('items.data', 1)
    );
});

it('admin can search pages by title', function () {
    Page::factory()->create(['title' => 'About Us']);
    Page::factory()->create(['title' => 'Privacy Policy']);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.pages.index', ['q' => 'About']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/pages/Index')
        ->has('items.data', 1)
    );
});

it('admin can view create page form', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.pages.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('admin/pages/Create'));
});

it('admin can create a page', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.pages.store'), [
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => '<p>Test content</p>',
        'status' => 'active',
        'meta_title' => 'Test Meta',
        'meta_description' => 'Test description',
    ]);

    $response->assertRedirect(route('admin.pages.index', absolute: false));

    $page = Page::query()->where('slug', 'test-page')->first();

    expect($page)->not->toBeNull();
    expect($page->title)->toBe('Test Page');
    expect($page->status)->toBe(PageStatus::Active);
    expect($page->is_system)->toBeFalse();
    expect($page->meta_title)->toBe('Test Meta');
});

it('admin can view edit page form', function () {
    $page = Page::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.pages.edit', $page));

    $response->assertOk();
    $response->assertInertia(fn ($p) => $p
        ->component('admin/pages/Edit')
        ->has('page')
    );
});

it('admin can update a page', function () {
    $page = Page::factory()->create(['title' => 'Old Title']);

    $response = $this->actingAs($this->admin)->put(route('admin.pages.update', $page), [
        'title' => 'New Title',
        'slug' => $page->slug,
        'content' => '<p>Updated content</p>',
        'status' => 'inactive',
    ]);

    $response->assertRedirect(route('admin.pages.index', absolute: false));

    $page->refresh();

    expect($page->title)->toBe('New Title');
    expect($page->status)->toBe(PageStatus::Inactive);
});

it('admin can soft delete a non-system page', function () {
    $page = Page::factory()->create(['is_system' => false]);

    $response = $this->actingAs($this->admin)->delete(route('admin.pages.destroy', $page));

    $response->assertRedirect();
    expect($page->fresh()?->trashed())->toBeTrue();
});

it('admin cannot soft delete a system page', function () {
    $page = Page::factory()->system()->create();

    $response = $this->actingAs($this->admin)->delete(route('admin.pages.destroy', $page));

    $response->assertRedirect();
    $response->assertSessionHasErrors('page');
    expect($page->fresh()?->trashed())->toBeFalse();
});

it('admin can restore a trashed page', function () {
    $page = Page::factory()->create();
    $page->delete();

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.pages.restore', $page));

    $response->assertRedirect();
    expect($page->fresh()?->trashed())->toBeFalse();
});

it('admin can permanently delete a non-system trashed page', function () {
    $page = Page::factory()->create(['is_system' => false]);
    $page->delete();

    $response = $this->actingAs($this->admin)
        ->delete(route('admin.pages.force-delete', $page));

    $response->assertRedirect();
    expect(Page::withTrashed()->find($page->id))->toBeNull();
});

it('admin cannot permanently delete a system page', function () {
    $page = Page::factory()->system()->create();
    $page->delete();

    $response = $this->actingAs($this->admin)
        ->delete(route('admin.pages.force-delete', $page));

    $response->assertRedirect();
    $response->assertSessionHasErrors('page');
    expect(Page::withTrashed()->find($page->id))->not->toBeNull();
});

it('admin can empty the recycle bin excluding system pages', function () {
    $customPage = Page::factory()->create(['is_system' => false]);
    $systemPage = Page::factory()->system()->create();
    $customPage->delete();
    $systemPage->delete();

    $this->actingAs($this->admin)->delete(route('admin.pages.empty-recycle-bin'));

    expect(Page::withTrashed()->find($customPage->id))->toBeNull();
    expect(Page::withTrashed()->find($systemPage->id))->not->toBeNull();
});

it('admin can update page status', function () {
    $page = Page::factory()->active()->create();

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.pages.status', $page), ['status' => 'inactive']);

    $response->assertRedirect();
    expect($page->fresh()->status)->toBe(PageStatus::Inactive);
});

it('validates required fields when creating a page', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.pages.store'), []);

    $response->assertSessionHasErrors(['title', 'slug']);
});

it('validates unique slug when creating a page', function () {
    Page::factory()->create(['slug' => 'existing-slug']);

    $response = $this->actingAs($this->admin)->post(route('admin.pages.store'), [
        'title' => 'Test',
        'slug' => 'existing-slug',
        'status' => 'active',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

it('public page route displays active page', function () {
    Page::factory()->create([
        'title' => 'About Us',
        'slug' => 'about-us',
        'content' => '<p>About content</p>',
        'status' => PageStatus::Active,
    ]);

    $response = $this->get(route('pages.show', 'about-us'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Page')
        ->where('page.title', 'About Us')
    );
});

it('public page route returns 404 for inactive page', function () {
    Page::factory()->create([
        'slug' => 'hidden-page',
        'status' => PageStatus::Inactive,
    ]);

    $response = $this->get(route('pages.show', 'hidden-page'));

    $response->assertNotFound();
});

it('privacy policy route loads from page model when available', function () {
    Page::factory()->create([
        'title' => 'Privacy Policy',
        'slug' => 'privacy-policy',
        'content' => '<p>Custom privacy content</p>',
        'status' => PageStatus::Active,
        'is_system' => true,
    ]);

    $response = $this->get(route('privacy-policy'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Page')
        ->where('page.title', 'Privacy Policy')
    );
});

it('terms of service route loads from page model when available', function () {
    Page::factory()->create([
        'title' => 'Terms of Service',
        'slug' => 'terms-of-service',
        'content' => '<p>Custom terms content</p>',
        'status' => PageStatus::Active,
        'is_system' => true,
    ]);

    $response = $this->get(route('terms-of-service'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Page')
        ->where('page.title', 'Terms of Service')
    );
});

it('refund policy route loads from page model when available', function () {
    Page::factory()->create([
        'title' => 'Refund Policy',
        'slug' => 'refund-policy',
        'content' => '<p>Custom refund policy content</p>',
        'status' => PageStatus::Active,
        'is_system' => true,
    ]);

    $response = $this->get(route('refund-policy'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Page')
        ->where('page.title', 'Refund Policy')
    );
});

it('admin can create a page with a featured image', function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);

    $response = $this->actingAs($this->admin)->post(route('admin.pages.store'), [
        'title' => 'Page With Image',
        'slug' => 'page-with-image',
        'content' => '<p>Content</p>',
        'status' => 'active',
        'featured_image' => UploadedFile::fake()->image('featured.jpg', 800, 600),
    ]);

    $response->assertRedirect(route('admin.pages.index', absolute: false));

    $page = Page::query()->where('slug', 'page-with-image')->first();

    expect($page)->not->toBeNull();
    expect($page->getFirstMediaUrl('featured_image'))->not->toBeEmpty();
});

it('admin can update a page with a featured image', function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);

    $page = Page::factory()->create();

    $response = $this->actingAs($this->admin)->put(route('admin.pages.update', $page), [
        'title' => $page->title,
        'slug' => $page->slug,
        'content' => $page->content,
        'status' => $page->status->value,
        'featured_image' => UploadedFile::fake()->image('new-featured.jpg', 800, 600),
    ]);

    $response->assertRedirect(route('admin.pages.index', absolute: false));

    $page->refresh();

    expect($page->getFirstMediaUrl('featured_image'))->not->toBeEmpty();
});

it('admin can remove a featured image from a page', function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);

    $page = Page::factory()->create();
    $page->addMedia(UploadedFile::fake()->image('existing.jpg'))
        ->toMediaCollection('featured_image');

    expect($page->getFirstMediaUrl('featured_image'))->not->toBeEmpty();

    $response = $this->actingAs($this->admin)->put(route('admin.pages.update', $page), [
        'title' => $page->title,
        'slug' => $page->slug,
        'content' => $page->content,
        'status' => $page->status->value,
        'remove_featured_image' => true,
    ]);

    $response->assertRedirect(route('admin.pages.index', absolute: false));

    $page->refresh();

    expect($page->getFirstMediaUrl('featured_image'))->toBeEmpty();
});

it('edit page includes featured image url', function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);

    $page = Page::factory()->create();
    $page->addMedia(UploadedFile::fake()->image('cover.jpg'))
        ->toMediaCollection('featured_image');

    $response = $this->actingAs($this->admin)->get(route('admin.pages.edit', $page));

    $response->assertOk();
    $response->assertInertia(fn ($p) => $p
        ->component('admin/pages/Edit')
        ->has('page')
        ->where('page.featured_image_url', fn ($url) => str_contains($url, 'cover.jpg'))
    );
});

it('public page includes featured image url', function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);

    $page = Page::factory()->create([
        'slug' => 'page-with-hero',
        'status' => PageStatus::Active,
    ]);
    $page->addMedia(UploadedFile::fake()->image('hero.jpg'))
        ->toMediaCollection('featured_image');

    $response = $this->get(route('pages.show', 'page-with-hero'));

    $response->assertOk();
    $response->assertInertia(fn ($p) => $p
        ->component('Page')
        ->where('page.featured_image_url', fn ($url) => str_contains($url, 'hero.jpg'))
    );
});

it('validates featured image must be an image file', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.pages.store'), [
        'title' => 'Test',
        'slug' => 'test',
        'status' => 'active',
        'featured_image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors(['featured_image']);
});
