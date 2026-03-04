<?php

use App\Enums\NoticeAudience;
use App\Enums\UserRole;
use App\Models\Notice;

it('scope active filters active non-expired notices', function () {
    Notice::factory()->create([
        'is_active' => true,
        'expires_at' => now()->addDay(),
    ]);
    Notice::factory()->inactive()->create();
    Notice::factory()->expired()->create();

    $active = Notice::query()->active()->get();

    expect($active)->toHaveCount(1);
});

it('scope forAudience filters by tutor role', function () {
    Notice::factory()->forTutors()->create();
    Notice::factory()->forGuardians()->create();
    Notice::factory()->forBoth()->create();

    $tutorNotices = Notice::query()->forAudience(UserRole::Tutor)->get();

    expect($tutorNotices)->toHaveCount(2);
});

it('scope forAudience filters by guardian role', function () {
    Notice::factory()->forTutors()->create();
    Notice::factory()->forGuardians()->create();
    Notice::factory()->forBoth()->create();

    $guardianNotices = Notice::query()->forAudience(UserRole::Guardian)->get();

    expect($guardianNotices)->toHaveCount(2);
});

it('isExpired returns true for expired notice', function () {
    $notice = Notice::factory()->expired()->make();

    expect($notice->isExpired())->toBeTrue();
});

it('isExpired returns false for non-expired notice', function () {
    $notice = Notice::factory()->make([
        'expires_at' => now()->addDay(),
    ]);

    expect($notice->isExpired())->toBeFalse();
});

it('isExpired returns false when expires_at is null', function () {
    $notice = Notice::factory()->make([
        'expires_at' => null,
    ]);

    expect($notice->isExpired())->toBeFalse();
});

it('isVisibleFor returns true for tutor when audience is both', function () {
    $notice = Notice::factory()->forBoth()->make([
        'is_active' => true,
        'expires_at' => now()->addDay(),
    ]);

    expect($notice->isVisibleFor(UserRole::Tutor))->toBeTrue();
});

it('isVisibleFor returns false for tutor when audience is guardian', function () {
    $notice = Notice::factory()->forGuardians()->make([
        'is_active' => true,
        'expires_at' => now()->addDay(),
    ]);

    expect($notice->isVisibleFor(UserRole::Tutor))->toBeFalse();
});

it('isVisibleFor returns false when inactive', function () {
    $notice = Notice::factory()->inactive()->make();

    expect($notice->isVisibleFor(UserRole::Tutor))->toBeFalse();
});

it('isVisibleFor returns false when expired', function () {
    $notice = Notice::factory()->expired()->forTutors()->make();

    expect($notice->isVisibleFor(UserRole::Tutor))->toBeFalse();
});
