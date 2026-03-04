<?php

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\JobStatus;
use App\Enums\PaymentGatewayType;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RefundRequest;
use App\Models\TuitionJob;
use App\Models\TuitionJobAssignment;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;

it('tutor can submit refund request and duplicate pending request is blocked', function () {
    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $platformUser = User::factory()->admin()->create([
        'role' => 'platform',
        'status' => 'active',
    ]);

    $job = TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => JobStatus::Confirmed,
        'published_at' => now()->subDay(),
        'confirmed_at' => now()->subDay(),
        'expires_at' => now()->addDays(10),
    ]);

    $assignment = TuitionJobAssignment::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
    ]);

    Invoice::factory()->create([
        'invoiceable_type' => TuitionJobAssignment::class,
        'invoiceable_id' => $assignment->id,
        'job_assignment_id' => $assignment->id,
        'user_id' => $tutor->id,
        'payer_user_id' => $tutor->id,
        'payee_user_id' => $platformUser->id,
        'type' => InvoiceType::PlatformServiceFee,
        'status' => InvoiceStatus::Paid,
        'amount' => 6000,
        'currency' => 'BDT',
        'paid_at' => now()->subHour(),
    ]);

    $this->actingAs($tutor)
        ->post(route('tutor.finance.refunds.store', ['assignment' => $assignment->id]), [
            'reason_text' => 'First class did not happen and guardian cancelled immediately.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('refund_requests', [
        'job_assignment_id' => $assignment->id,
        'requested_by_user_id' => $tutor->id,
        'status' => RefundStatus::Pending->value,
    ]);

    $this->actingAs($tutor)
        ->from(route('tutor.finance.refunds.index'))
        ->post(route('tutor.finance.refunds.store', ['assignment' => $assignment->id]), [
            'reason_text' => 'Trying duplicate pending request.',
        ])
        ->assertRedirect(route('tutor.finance.refunds.index', absolute: false))
        ->assertSessionHasErrors(['refund']);

    expect(
        RefundRequest::query()
            ->where('job_assignment_id', $assignment->id)
            ->where('status', RefundStatus::Pending)
            ->count()
    )->toBe(1);
});

it('admin can approve and mark paid refund which updates invoice and ledger', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $guardian = User::factory()->guardian()->create();
    $tutor = User::factory()->tutor()->create();
    $platformUser = User::factory()->admin()->create([
        'role' => 'platform',
        'status' => 'active',
    ]);

    $job = TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => JobStatus::Confirmed,
        'published_at' => now()->subDay(),
        'confirmed_at' => now()->subDay(),
        'expires_at' => now()->addDays(10),
    ]);

    $assignment = TuitionJobAssignment::factory()->create([
        'job_id' => $job->id,
        'tutor_user_id' => $tutor->id,
    ]);

    $serviceFeeInvoice = Invoice::factory()->create([
        'invoiceable_type' => TuitionJobAssignment::class,
        'invoiceable_id' => $assignment->id,
        'job_assignment_id' => $assignment->id,
        'user_id' => $tutor->id,
        'payer_user_id' => $tutor->id,
        'payee_user_id' => $platformUser->id,
        'type' => InvoiceType::PlatformServiceFee,
        'status' => InvoiceStatus::Paid,
        'amount' => 8000,
        'currency' => 'BDT',
        'paid_at' => now()->subHour(),
    ]);

    $refundRequest = RefundRequest::query()->create([
        'job_assignment_id' => $assignment->id,
        'requested_by_user_id' => $tutor->id,
        'reason_text' => 'Guardian ended the engagement before first month started.',
        'requested_at' => now()->subMinutes(30),
        'status' => RefundStatus::Pending,
        'amount' => 8000,
        'currency' => 'BDT',
        'decision_by_admin_id' => null,
        'decision_note' => null,
        'decided_at' => null,
        'paid_at' => null,
        'payment_id' => null,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.finance.refund-requests.decision', $refundRequest), [
            'status' => RefundStatus::Approved->value,
            'decision_note' => 'Approved after reviewing evidence.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('refund_requests', [
        'id' => $refundRequest->id,
        'status' => RefundStatus::Approved->value,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.finance.refund-requests.mark-paid', $refundRequest), [
            'gateway' => PaymentGatewayType::Manual->value,
            'provider_txn_id' => 'REFUND-TXN-1001',
            'note' => 'Refund settled by manual transfer.',
        ])
        ->assertRedirect();

    $refundRequest->refresh();
    $serviceFeeInvoice->refresh();

    expect($refundRequest->status)->toBe(RefundStatus::Paid);
    expect($refundRequest->payment_id)->not->toBeNull();
    expect($serviceFeeInvoice->status)->toBe(InvoiceStatus::Refunded);

    $refundPayment = Payment::query()->findOrFail($refundRequest->payment_id);
    expect($refundPayment->status)->toBe(PaymentStatus::Refunded);
    expect((float) $refundPayment->amount)->toBe(8000.0);

    $ledgerEntries = DB::table('wallet_ledger_entries')
        ->where('reference_type', 'refund_request')
        ->where('reference_id', $refundRequest->id)
        ->get();

    expect($ledgerEntries)->toHaveCount(2);
    expect($ledgerEntries->pluck('type')->sort()->values()->all())->toBe(['credit', 'debit']);
    expect($ledgerEntries->where('is_reversal', 1))->toHaveCount(2);
});
