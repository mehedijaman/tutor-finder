<?php

use App\Enums\TutorialAudience;
use App\Models\Tutorial;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->admin()->create();
    $this->admin->assignRole('super-admin');
});

it('admin can view tutorials index', function () {
    Tutorial::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.tutorials.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('admin/tutorials/Index'));
});

it('admin can filter tutorials by audience', function () {
    Tutorial::factory()->forTutor()->create();
    Tutorial::factory()->forGuardian()->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.tutorials.index', ['audience' => 'tutor']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/tutorials/Index')
        ->has('items.data', 1)
    );
});

it('admin can search tutorials by title', function () {
    Tutorial::factory()->create(['title' => 'How to Register']);
    Tutorial::factory()->create(['title' => 'Payment Method']);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.tutorials.index', ['q' => 'Register']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/tutorials/Index')
        ->has('items.data', 1)
    );
});

it('admin can view create tutorial form', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.tutorials.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('admin/tutorials/Create'));
});

it('admin can create a tutorial', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.tutorials.store'), [
        'title' => 'How to Sign In',
        'slug' => 'how-to-sign-in',
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'audience' => 'tutor',
        'description' => 'Learn how to sign in.',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $response->assertRedirect(route('admin.tutorials.index', absolute: false));

    $tutorial = Tutorial::query()->where('slug', 'how-to-sign-in')->first();

    expect($tutorial)->not->toBeNull();
    expect($tutorial->title)->toBe('How to Sign In');
    expect($tutorial->audience)->toBe(TutorialAudience::Tutor);
    expect($tutorial->is_active)->toBeTrue();
    expect($tutorial->sort_order)->toBe(1);
});

it('admin can create a tutorial with a thumbnail', function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);

    $response = $this->actingAs($this->admin)->post(route('admin.tutorials.store'), [
        'title' => 'Tutorial With Thumbnail',
        'slug' => 'tutorial-with-thumbnail',
        'video_url' => 'https://www.youtube.com/watch?v=abc123def45',
        'audience' => 'all',
        'thumbnail' => UploadedFile::fake()->image('thumb.jpg', 640, 360),
    ]);

    $response->assertRedirect(route('admin.tutorials.index', absolute: false));

    $tutorial = Tutorial::query()->where('slug', 'tutorial-with-thumbnail')->first();

    expect($tutorial)->not->toBeNull();
    expect($tutorial->getFirstMediaUrl('thumbnail'))->not->toBeEmpty();
});

it('admin can view edit tutorial form', function () {
    $tutorial = Tutorial::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.tutorials.edit', $tutorial));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/tutorials/Edit')
        ->has('tutorial')
    );
});

it('admin can update a tutorial', function () {
    $tutorial = Tutorial::factory()->create(['title' => 'Old Title']);

    $response = $this->actingAs($this->admin)->put(route('admin.tutorials.update', $tutorial), [
        'title' => 'New Title',
        'slug' => $tutorial->slug,
        'video_url' => $tutorial->video_url,
        'audience' => 'guardian',
        'sort_order' => 5,
    ]);

    $response->assertRedirect(route('admin.tutorials.index', absolute: false));

    $tutorial->refresh();

    expect($tutorial->title)->toBe('New Title');
    expect($tutorial->audience)->toBe(TutorialAudience::Guardian);
    expect($tutorial->sort_order)->toBe(5);
});

it('admin can remove a thumbnail from a tutorial', function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);

    $tutorial = Tutorial::factory()->create();
    $tutorial->addMedia(UploadedFile::fake()->image('existing.jpg'))
        ->toMediaCollection('thumbnail');

    expect($tutorial->getFirstMediaUrl('thumbnail'))->not->toBeEmpty();

    $response = $this->actingAs($this->admin)->put(route('admin.tutorials.update', $tutorial), [
        'title' => $tutorial->title,
        'slug' => $tutorial->slug,
        'video_url' => $tutorial->video_url,
        'audience' => $tutorial->audience->value,
        'remove_thumbnail' => true,
    ]);

    $response->assertRedirect(route('admin.tutorials.index', absolute: false));

    $tutorial->refresh();

    expect($tutorial->getFirstMediaUrl('thumbnail'))->toBeEmpty();
});

it('admin can delete a tutorial (soft delete)', function () {
    $tutorial = Tutorial::factory()->create();

    $response = $this->actingAs($this->admin)->delete(route('admin.tutorials.destroy', $tutorial));

    $response->assertRedirect();
    expect(Tutorial::query()->find($tutorial->id))->toBeNull();
    expect(Tutorial::withTrashed()->find($tutorial->id))->not->toBeNull();
});

it('admin can toggle tutorial status', function () {
    $tutorial = Tutorial::factory()->create(['is_active' => true]);

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.tutorials.status', $tutorial), ['is_active' => false]);

    $response->assertRedirect();
    expect($tutorial->fresh()->is_active)->toBeFalse();
});

it('validates required fields when creating a tutorial', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.tutorials.store'), []);

    $response->assertSessionHasErrors(['title', 'slug', 'video_url']);
});

it('validates unique slug when creating a tutorial', function () {
    Tutorial::factory()->create(['slug' => 'existing-slug']);

    $response = $this->actingAs($this->admin)->post(route('admin.tutorials.store'), [
        'title' => 'Test',
        'slug' => 'existing-slug',
        'video_url' => 'https://www.youtube.com/watch?v=test12345ab',
        'audience' => 'all',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

it('validates video_url must be a valid url', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.tutorials.store'), [
        'title' => 'Test',
        'slug' => 'test',
        'video_url' => 'not-a-url',
        'audience' => 'all',
    ]);

    $response->assertSessionHasErrors(['video_url']);
});

it('validates thumbnail must be an image file', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.tutorials.store'), [
        'title' => 'Test',
        'slug' => 'test',
        'video_url' => 'https://www.youtube.com/watch?v=test12345ab',
        'audience' => 'all',
        'thumbnail' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors(['thumbnail']);
});

it('edit tutorial includes thumbnail url', function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);

    $tutorial = Tutorial::factory()->create();
    $tutorial->addMedia(UploadedFile::fake()->image('cover.jpg'))
        ->toMediaCollection('thumbnail');

    $response = $this->actingAs($this->admin)->get(route('admin.tutorials.edit', $tutorial));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/tutorials/Edit')
        ->has('tutorial')
        ->where('tutorial.thumbnail_url', fn ($url) => str_contains($url, 'cover.jpg'))
    );
});

it('public tutorials page displays active tutorials', function () {
    Tutorial::factory()->create(['title' => 'Active Tutorial', 'is_active' => true]);
    Tutorial::factory()->create(['title' => 'Inactive Tutorial', 'is_active' => false]);

    $response = $this->get(route('tutorials'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Tutorials')
        ->has('tutorials', 1)
        ->where('tutorials.0.title', 'Active Tutorial')
    );
});

it('public tutorials page returns audience options', function () {
    $response = $this->get(route('tutorials'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Tutorials')
        ->has('audienceOptions', 3)
    );
});

it('admin can view trashed tutorials', function () {
    Tutorial::factory()->create(['title' => 'Active Tutorial']);
    $trashed = Tutorial::factory()->create(['title' => 'Trashed Tutorial']);
    $trashed->delete();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.tutorials.index', ['trash' => 1]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/tutorials/Index')
        ->has('items.data', 1)
        ->where('filters.trash', true)
    );
});

it('admin can restore a trashed tutorial', function () {
    $tutorial = Tutorial::factory()->create();
    $tutorial->delete();

    expect(Tutorial::query()->find($tutorial->id))->toBeNull();

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.tutorials.restore', $tutorial));

    $response->assertRedirect();
    expect(Tutorial::query()->find($tutorial->id))->not->toBeNull();
});

it('admin can force delete a trashed tutorial', function () {
    $tutorial = Tutorial::factory()->create();
    $tutorial->delete();

    $response = $this->actingAs($this->admin)
        ->delete(route('admin.tutorials.force-delete', $tutorial));

    $response->assertRedirect();
    expect(Tutorial::withTrashed()->find($tutorial->id))->toBeNull();
});

it('admin can empty the recycle bin', function () {
    $tutorial1 = Tutorial::factory()->create();
    $tutorial2 = Tutorial::factory()->create();
    $tutorial1->delete();
    $tutorial2->delete();

    Tutorial::factory()->create();

    $response = $this->actingAs($this->admin)
        ->delete(route('admin.tutorials.empty-recycle-bin'));

    $response->assertRedirect();
    expect(Tutorial::withTrashed()->count())->toBe(1);
    expect(Tutorial::onlyTrashed()->count())->toBe(0);
});

it('index returns active and trash counts', function () {
    Tutorial::factory()->count(3)->create();
    $trashed = Tutorial::factory()->create();
    $trashed->delete();

    $response = $this->actingAs($this->admin)->get(route('admin.tutorials.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/tutorials/Index')
        ->where('counts.active', 3)
        ->where('counts.trash', 1)
    );
});

it('tutor can view tutorials for tutor audience', function () {
    $tutor = User::factory()->tutor()->create();
    Tutorial::factory()->forTutor()->create(['title' => 'Tutor Tutorial']);
    Tutorial::factory()->forAll()->create(['title' => 'All Tutorial']);
    Tutorial::factory()->forGuardian()->create(['title' => 'Guardian Tutorial']);

    $response = $this->actingAs($tutor)->get(route('tutor.tutorials.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('tutor/Tutorials')
        ->has('tutorials', 2)
    );
});

it('guardian can view tutorials for guardian audience', function () {
    $guardian = User::factory()->guardian()->create();
    Tutorial::factory()->forGuardian()->create(['title' => 'Guardian Tutorial']);
    Tutorial::factory()->forAll()->create(['title' => 'All Tutorial']);
    Tutorial::factory()->forTutor()->create(['title' => 'Tutor Tutorial']);

    $response = $this->actingAs($guardian)->get(route('guardian.tutorials.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('guardian/Tutorials')
        ->has('tutorials', 2)
    );
});
