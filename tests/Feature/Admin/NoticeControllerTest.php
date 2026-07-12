<?php

use App\Enums\NoticeAudience;
use App\Enums\UserStatus;
use App\Jobs\SendNoticeNotificationsJob;
use App\Models\Notice;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->admin()->create();
    $this->admin->assignRole('super-admin');
});

it('admin can view notices index', function () {
    Notice::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.notices.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('admin/notices/Index'));
});

it('admin can filter notices by audience', function () {
    Notice::factory()->forTutors()->create(['title' => 'Tutor Notice']);
    Notice::factory()->forGuardians()->create(['title' => 'Guardian Notice']);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.notices.index', ['audience' => 'tutor']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/notices/Index')
        ->has('items.data', 1)
    );
});

it('admin can filter notices by status', function () {
    Notice::factory()->create(['is_active' => true, 'expires_at' => now()->addDay()]);
    Notice::factory()->inactive()->create();
    Notice::factory()->expired()->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.notices.index', ['status' => 'active']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/notices/Index')
        ->has('items.data', 1)
    );
});

it('admin can view create notice page', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.notices.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('admin/notices/Create'));
});

it('admin can create a notice', function () {
    Queue::fake();

    $response = $this->actingAs($this->admin)->post(route('admin.notices.store'), [
        'title' => 'Important Announcement',
        'body' => '<p>This is an important notice.</p>',
        'audience' => 'both',
        'is_active' => true,
        'published_at' => now()->toDateTimeString(),
        'expires_at' => now()->addWeek()->toDateTimeString(),
    ]);

    $response->assertRedirect(route('admin.notices.index', absolute: false));

    $notice = Notice::query()->first();

    expect($notice)->not->toBeNull();
    expect($notice?->title)->toBe('Important Announcement');
    expect($notice?->audience)->toBe(NoticeAudience::Both);
    expect($notice?->is_active)->toBeTrue();
    expect($notice?->created_by_user_id)->toBe($this->admin->id);

    Queue::assertPushed(SendNoticeNotificationsJob::class);
});

it('admin can view edit notice page', function () {
    $notice = Notice::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.notices.edit', $notice));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/notices/Edit')
        ->has('notice')
    );
});

it('admin can update a notice', function () {
    $notice = Notice::factory()->create(['title' => 'Old Title']);

    $response = $this->actingAs($this->admin)->put(route('admin.notices.update', $notice), [
        'title' => 'New Title',
        'body' => '<p>Updated content.</p>',
        'audience' => 'tutor',
        'is_active' => true,
        'published_at' => now()->toDateTimeString(),
        'expires_at' => null,
    ]);

    $response->assertRedirect(route('admin.notices.index', absolute: false));

    $notice->refresh();

    expect($notice->title)->toBe('New Title');
    expect($notice->audience)->toBe(NoticeAudience::Tutor);
});

it('admin can soft delete a notice', function () {
    $notice = Notice::factory()->create();

    $response = $this->actingAs($this->admin)->delete(route('admin.notices.destroy', $notice));

    $response->assertRedirect();
    expect($notice->fresh()?->trashed())->toBeTrue();
});

it('admin can restore a soft deleted notice', function () {
    $notice = Notice::factory()->create();
    $notice->delete();

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.notices.restore', $notice));

    $response->assertRedirect();
    expect($notice->fresh()?->trashed())->toBeFalse();
});

it('admin can permanently delete a notice', function () {
    $notice = Notice::factory()->create();
    $notice->delete();

    $response = $this->actingAs($this->admin)
        ->delete(route('admin.notices.force-delete', $notice));

    $response->assertRedirect();
    expect(Notice::withTrashed()->find($notice->id))->toBeNull();
});

it('admin can empty the recycle bin', function () {
    Notice::factory()->count(3)->create()->each->delete();

    $this->actingAs($this->admin)->delete(route('admin.notices.empty-recycle-bin'));

    expect(Notice::withTrashed()->count())->toBe(0);
});

it('validates required fields when creating notice', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.notices.store'), []);

    $response->assertSessionHasErrors(['title', 'body', 'audience']);
});

it('validates title max length', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.notices.store'), [
        'title' => str_repeat('a', 181),
        'body' => '<p>Content</p>',
        'audience' => 'both',
    ]);

    $response->assertSessionHasErrors(['title']);
});

it('sends notifications to active users when notice is created', function () {
    $activeTutor = User::factory()->tutor()->create(['status' => UserStatus::Active]);
    $suspendedTutor = User::factory()->tutor()->create(['status' => UserStatus::Suspended]);
    $activeGuardian = User::factory()->guardian()->create(['status' => UserStatus::Active]);

    $this->actingAs($this->admin)->post(route('admin.notices.store'), [
        'title' => 'Test Notice',
        'body' => '<p>Body</p>',
        'audience' => 'tutor',
        'is_active' => true,
    ]);

    expect($activeTutor->notifications()->count())->toBe(1);
    expect($suspendedTutor->notifications()->count())->toBe(0);
    expect($activeGuardian->notifications()->count())->toBe(0);
});

it('sends notifications to both tutors and guardians for both audience', function () {
    $activeTutor = User::factory()->tutor()->create(['status' => UserStatus::Active]);
    $activeGuardian = User::factory()->guardian()->create(['status' => UserStatus::Active]);

    $this->actingAs($this->admin)->post(route('admin.notices.store'), [
        'title' => 'Both Audience Notice',
        'body' => '<p>Body</p>',
        'audience' => 'both',
        'is_active' => true,
    ]);

    expect($activeTutor->notifications()->count())->toBe(1);
    expect($activeGuardian->notifications()->count())->toBe(1);
});
