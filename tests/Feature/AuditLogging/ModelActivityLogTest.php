<?php

use App\Enums\ApplicationStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\RefundRequest;
use App\Models\SiteSetting;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\TuitionJobAssignment;
use App\Models\User;
use App\Models\VerificationRequest;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    Activity::query()->delete();
});

it('logs TuitionJob creation with fillable attributes', function () {
    $guardian = User::factory()->guardian()->create();

    Activity::query()->delete();

    $job = TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'title' => 'Math Tutor Needed',
    ]);

    $log = Activity::query()->where('log_name', 'tuition-jobs')->latest('id')->first();

    expect($log)->not->toBeNull();
    expect($log->event)->toBe('created');
    expect($log->subject_type)->toBe(TuitionJob::class);
    expect($log->subject_id)->toBe($job->id);
    expect($log->properties['attributes']['title'])->toBe('Math Tutor Needed');
});

it('logs TuitionJob update with old and new values', function () {
    $job = TuitionJob::factory()->create(['title' => 'Old Title']);

    Activity::query()->delete();

    $job->update(['title' => 'New Title']);

    $log = Activity::query()->where('log_name', 'tuition-jobs')->latest('id')->first();

    expect($log)->not->toBeNull();
    expect($log->event)->toBe('updated');
    expect($log->properties['old']['title'])->toBe('Old Title');
    expect($log->properties['attributes']['title'])->toBe('New Title');
});

it('logs TuitionJob soft delete', function () {
    $job = TuitionJob::factory()->create();

    Activity::query()->delete();

    $job->delete();

    $log = Activity::query()->where('log_name', 'tuition-jobs')->latest('id')->first();

    expect($log)->not->toBeNull();
    expect($log->event)->toBe('deleted');
    expect($log->subject_id)->toBe($job->id);
});

it('logs TuitionJobApplication status change', function () {
    $application = TuitionJobApplication::factory()->create([
        'status' => ApplicationStatus::Applied,
    ]);

    Activity::query()->delete();

    $application->update(['status' => ApplicationStatus::Shortlisted->value]);

    $log = Activity::query()->where('log_name', 'applications')->latest('id')->first();

    expect($log)->not->toBeNull();
    expect($log->event)->toBe('updated');
    expect($log->properties['old']['status'])->toBe(ApplicationStatus::Applied->value);
    expect($log->properties['attributes']['status'])->toBe(ApplicationStatus::Shortlisted->value);
});

it('logs TuitionJobAssignment creation excluding metadata', function () {
    $tutor = User::factory()->tutor()->create();
    $job = TuitionJob::factory()->create();

    Activity::query()->delete();

    $assignment = TuitionJobAssignment::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
        'metadata' => ['foo' => 'bar'],
    ]);

    $log = Activity::query()->where('log_name', 'assignments')->latest('id')->first();

    expect($log)->not->toBeNull();
    expect($log->event)->toBe('created');
    expect($log->properties['attributes'])->not->toHaveKey('metadata');
});

it('logs Invoice status change excluding gateway_payload', function () {
    $user = User::factory()->guardian()->create();
    $invoice = Invoice::factory()->create([
        'user_id' => $user->id,
        'payer_user_id' => $user->id,
        'gateway_payload' => ['secret' => 'data'],
    ]);

    Activity::query()->delete();

    $invoice->update(['status' => 'paid']);

    $log = Activity::query()->where('log_name', 'invoices')->latest('id')->first();

    expect($log)->not->toBeNull();
    expect($log->properties['attributes'])->not->toHaveKey('gateway_payload');
});

it('logs Payment creation excluding provider_payload', function () {
    $payment = Payment::factory()->create([
        'provider_payload' => ['raw' => 'response'],
    ]);

    $log = Activity::query()->where('log_name', 'payments')->latest('id')->first();

    expect($log)->not->toBeNull();
    expect($log->event)->toBe('created');
    expect($log->properties['attributes'])->not->toHaveKey('provider_payload');
});

it('logs User update excluding password and remember_token', function () {
    $user = User::factory()->create(['name' => 'Original Name']);

    Activity::query()->delete();

    $user->update(['name' => 'Updated Name', 'password' => 'newsecret123']);

    $log = Activity::query()->where('log_name', 'users')->latest('id')->first();

    expect($log)->not->toBeNull();
    expect($log->event)->toBe('updated');
    expect($log->properties['attributes']['name'])->toBe('Updated Name');
    expect($log->properties['attributes'])->not->toHaveKey('password');
    expect($log->properties['attributes'])->not->toHaveKey('remember_token');
});

it('logs VerificationRequest creation excluding metadata', function () {
    $user = User::factory()->tutor()->create();

    Activity::query()->delete();

    $verification = VerificationRequest::factory()->create([
        'user_id' => $user->id,
        'role' => 'tutor',
        'metadata' => ['docs' => ['id_scan.pdf']],
    ]);

    $log = Activity::query()->where('log_name', 'verification')->latest('id')->first();

    expect($log)->not->toBeNull();
    expect($log->event)->toBe('created');
    expect($log->properties['attributes'])->not->toHaveKey('metadata');
});

it('logs RefundRequest creation', function () {
    Activity::query()->delete();

    $refund = RefundRequest::factory()->create();

    $log = Activity::query()->where('log_name', 'refunds')->latest('id')->first();

    expect($log)->not->toBeNull();
    expect($log->event)->toBe('created');
    expect($log->subject_type)->toBe(RefundRequest::class);
});

it('logs SiteSetting update', function () {
    $setting = SiteSetting::factory()->create(['site_name' => 'Old Name']);

    Activity::query()->delete();

    $setting->update(['site_name' => 'New Name']);

    $log = Activity::query()->where('log_name', 'settings')->latest('id')->first();

    expect($log)->not->toBeNull();
    expect($log->event)->toBe('updated');
    expect($log->properties['old']['site_name'])->toBe('Old Name');
    expect($log->properties['attributes']['site_name'])->toBe('New Name');
});

it('logs PaymentGateway update excluding credentials', function () {
    $gateway = PaymentGateway::query()->where('gateway', 'bkash')->first()
        ?? PaymentGateway::factory()->bkash()->create();

    $gateway->update(['credentials' => ['api_key' => 'secret123']]);

    Activity::query()->delete();

    $gateway->update(['status' => 'inactive']);

    $log = Activity::query()->where('log_name', 'settings')->latest('id')->first();

    expect($log)->not->toBeNull();
    expect($log->properties['attributes'])->not->toHaveKey('credentials');
});

it('does not log when no dirty attributes change', function () {
    $job = TuitionJob::factory()->create(['title' => 'Same Title']);

    Activity::query()->delete();

    $job->update(['title' => 'Same Title']);

    $log = Activity::query()->where('log_name', 'tuition-jobs')->where('event', 'updated')->first();

    expect($log)->toBeNull();
});
