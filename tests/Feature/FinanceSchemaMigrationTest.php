<?php

use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

const FINANCE_CUTOVER_V3_MIGRATION = 'database/migrations/2026_03_03_131226_finance_cutover_v3_refactor_invoices_and_add_finance_policies.php';

it('applies finance cutover schema on invoices, payments, ledger, and refund tables', function () {
    expect(Schema::hasTable('payments'))->toBeTrue();
    expect(Schema::hasTable('wallet_ledger_entries'))->toBeTrue();
    expect(Schema::hasTable('refund_requests'))->toBeTrue();

    expect(Schema::hasColumns('invoices', [
        'invoice_no',
        'payer_user_id',
        'payee_user_id',
        'type',
        'job_assignment_id',
        'status',
    ]))->toBeTrue();

    expect(Schema::hasColumns('payments', [
        'invoice_id',
        'gateway',
        'provider_txn_id',
        'amount',
        'status',
        'provider_payload',
        'pending_guard',
    ]))->toBeTrue();

    expect(Schema::hasColumns('wallet_ledger_entries', [
        'journal_uuid',
        'owner_user_id',
        'type',
        'amount',
        'currency',
        'reference_type',
        'reference_id',
        'posted_at',
        'is_reversal',
        'reverses_journal_uuid',
    ]))->toBeTrue();

    expect(Schema::hasColumns('refund_requests', [
        'job_assignment_id',
        'requested_by_user_id',
        'reason_text',
        'requested_at',
        'status',
        'amount',
        'currency',
        'decision_by_admin_id',
        'decision_note',
        'decided_at',
        'paid_at',
        'payment_id',
    ]))->toBeTrue();
});

it('bootstraps platform system user and finance defaults in site settings', function () {
    $platformUser = DB::table('users')
        ->where('email', 'platform@system.local')
        ->first();

    expect($platformUser)->not->toBeNull();
    expect($platformUser?->role)->toBe('platform');
    expect($platformUser?->status)->toBe('active');

    $siteSetting = SiteSetting::current();
    $resolvedPlatformOwnerUserId = $siteSetting->platformOwnerUserId();

    expect($resolvedPlatformOwnerUserId)->toBe((int) $platformUser?->id);
    expect((int) $siteSetting->platform_service_fee_due_days)->toBeGreaterThan(0);
    expect(strtoupper((string) $siteSetting->default_fee_currency))->toBe('BDT');
});

it('keeps legacy status mapping strict during invoice backfill', function () {
    /** @var \Illuminate\Database\Migrations\Migration $migration */
    $migration = require base_path(FINANCE_CUTOVER_V3_MIGRATION);

    $mapLegacyStatus = new \ReflectionMethod($migration, 'mapLegacyStatus');
    $mapLegacyStatus->setAccessible(true);

    expect($mapLegacyStatus->invoke($migration, 'paid'))->toBe('paid');
    expect($mapLegacyStatus->invoke($migration, 'unpaid'))->toBe('unpaid');
    expect($mapLegacyStatus->invoke($migration, 'processing'))->toBe('unpaid');
    expect($mapLegacyStatus->invoke($migration, 'failed'))->toBe('void');
    expect($mapLegacyStatus->invoke($migration, 'cancelled'))->toBe('void');
});
