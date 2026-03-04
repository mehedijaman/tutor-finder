<?php

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\OtpRequest;

it('deletes expired otp requests', function (): void {
    OtpRequest::query()->create([
        'phone' => '01700000001',
        'purpose' => 'registration',
        'otp_hash' => hash('sha256', '123456'),
        'expires_at' => now()->subHour(),
    ]);
    OtpRequest::query()->create([
        'phone' => '01700000002',
        'purpose' => 'registration',
        'otp_hash' => hash('sha256', '654321'),
        'expires_at' => now()->subDay(),
    ]);
    OtpRequest::query()->create([
        'phone' => '01700000003',
        'purpose' => 'registration',
        'otp_hash' => hash('sha256', '111111'),
        'expires_at' => now()->addHour(),
    ]);

    expect(OtpRequest::query()->count())->toBe(3);

    $this->artisan('app:cleanup')
        ->expectsOutput('Deleted 2 expired OTP requests.')
        ->assertSuccessful();

    expect(OtpRequest::query()->count())->toBe(1);
});

it('marks expired unpaid invoices as expired', function (): void {
    Invoice::factory()->create([
        'status' => InvoiceStatus::Unpaid,
        'expires_at' => now()->subDay(),
    ]);

    Invoice::factory()->create([
        'status' => InvoiceStatus::Draft,
        'expires_at' => now()->subWeek(),
    ]);

    Invoice::factory()->create([
        'status' => InvoiceStatus::Unpaid,
        'expires_at' => now()->addWeek(),
    ]);

    Invoice::factory()->create([
        'status' => InvoiceStatus::Paid,
        'expires_at' => now()->subDay(),
    ]);

    $this->artisan('app:cleanup')
        ->expectsOutput('Marked 2 invoices as expired.')
        ->assertSuccessful();

    $expiredCount = Invoice::query()
        ->where('status', InvoiceStatus::Expired)
        ->count();

    expect($expiredCount)->toBe(2);
});

it('runs in dry-run mode without making changes', function (): void {
    OtpRequest::query()->create([
        'phone' => '01700000001',
        'purpose' => 'registration',
        'otp_hash' => hash('sha256', '123456'),
        'expires_at' => now()->subHour(),
    ]);

    Invoice::factory()->create([
        'status' => InvoiceStatus::Unpaid,
        'expires_at' => now()->subDay(),
    ]);

    $this->artisan('app:cleanup', ['--dry-run' => true])
        ->expectsOutput('Running in dry-run mode. No changes will be made.')
        ->expectsOutput('Would delete 1 expired OTP requests.')
        ->expectsOutput('Would mark 1 invoices as expired.')
        ->assertSuccessful();

    expect(OtpRequest::query()->count())->toBe(1);
    expect(Invoice::query()->where('status', InvoiceStatus::Unpaid)->count())->toBe(1);
});
