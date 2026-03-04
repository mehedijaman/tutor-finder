<?php

use App\Enums\UserStatus;
use App\Events\NoticeCreated;
use App\Jobs\SendNoticeNotificationsJob;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();
});

it('broadcasts to tutor channel when audience is tutor', function (): void {
    $notice = Notice::factory()->forTutors()->create();

    $event = new NoticeCreated($notice);
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1);
    expect($channels[0])->toBeInstanceOf(PrivateChannel::class);
    expect($channels[0]->name)->toBe('private-role.tutor');
});

it('broadcasts to guardian channel when audience is guardian', function (): void {
    $notice = Notice::factory()->forGuardians()->create();

    $event = new NoticeCreated($notice);
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1);
    expect($channels[0])->toBeInstanceOf(PrivateChannel::class);
    expect($channels[0]->name)->toBe('private-role.guardian');
});

it('broadcasts to both channels when audience is both', function (): void {
    $notice = Notice::factory()->forAll()->create();

    $event = new NoticeCreated($notice);
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(2);
    expect(collect($channels)->pluck('name')->all())->toBe([
        'private-role.tutor',
        'private-role.guardian',
    ]);
});

it('uses correct broadcast name', function (): void {
    $notice = Notice::factory()->create();

    $event = new NoticeCreated($notice);

    expect($event->broadcastAs())->toBe('notice.created');
});

it('broadcasts minimal payload', function (): void {
    $notice = Notice::factory()->forTutors()->create([
        'title' => 'Test Notice Title',
    ]);

    $event = new NoticeCreated($notice);
    $payload = $event->broadcastWith();

    expect($payload)->toHaveKeys(['notice_id', 'title', 'audience', 'created_at']);
    expect($payload['notice_id'])->toBe($notice->id);
    expect($payload['title'])->toBe('Test Notice Title');
    expect($payload['audience'])->toBe('tutor');
});

it('job dispatches NoticeCreated event', function (): void {
    Event::fake([NoticeCreated::class]);

    $notice = Notice::factory()->forTutors()->create();

    SendNoticeNotificationsJob::dispatchSync($notice);

    Event::assertDispatched(NoticeCreated::class, fn ($event) => $event->notice->id === $notice->id);
});

it('job creates database notifications for targeted users', function (): void {
    $tutor = User::factory()->tutor()->create(['status' => UserStatus::Active]);
    $guardian = User::factory()->guardian()->create(['status' => UserStatus::Active]);

    $notice = Notice::factory()->forTutors()->create();

    SendNoticeNotificationsJob::dispatchSync($notice);

    expect($tutor->notifications()->count())->toBe(1);
    expect($guardian->notifications()->count())->toBe(0);
});

it('job creates notifications for both roles when audience is both', function (): void {
    $tutor = User::factory()->tutor()->create(['status' => UserStatus::Active]);
    $guardian = User::factory()->guardian()->create(['status' => UserStatus::Active]);

    $notice = Notice::factory()->forAll()->create();

    SendNoticeNotificationsJob::dispatchSync($notice);

    expect($tutor->notifications()->count())->toBe(1);
    expect($guardian->notifications()->count())->toBe(1);
});

it('job does not create notifications for inactive users', function (): void {
    $activeTutor = User::factory()->tutor()->create(['status' => UserStatus::Active]);
    $suspendedTutor = User::factory()->tutor()->create(['status' => UserStatus::Suspended]);

    $notice = Notice::factory()->forTutors()->create();

    SendNoticeNotificationsJob::dispatchSync($notice);

    expect($activeTutor->notifications()->count())->toBe(1);
    expect($suspendedTutor->notifications()->count())->toBe(0);
});

it('job does not process expired notices', function (): void {
    Event::fake([NoticeCreated::class]);

    $tutor = User::factory()->tutor()->create(['status' => UserStatus::Active]);
    $notice = Notice::factory()->forTutors()->create([
        'expires_at' => now()->subDay(),
    ]);

    SendNoticeNotificationsJob::dispatchSync($notice);

    Event::assertNotDispatched(NoticeCreated::class);
    expect($tutor->notifications()->count())->toBe(0);
});
