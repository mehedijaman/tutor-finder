<?php

use App\Enums\PaymentGatewayType;
use App\Enums\TaxonomyStatus;
use App\Models\Category;
use App\Models\Country;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\RefundRequest;
use App\Models\Testimonial;
use App\Models\TuitionJobApplication;
use App\Models\TuitionJobAssignment;
use App\Models\TuitionType;
use App\Models\WalletLedgerEntry;

// --- TaxonomyStatus enum casting tests ---

it('casts taxonomy model status to TaxonomyStatus enum', function (string $modelClass): void {
    $model = $modelClass::factory()->create(['status' => 'active']);

    expect($model->status)->toBeInstanceOf(TaxonomyStatus::class)
        ->and($model->status)->toBe(TaxonomyStatus::Active);

    $model->update(['status' => TaxonomyStatus::Inactive]);
    $model->refresh();

    expect($model->status)->toBe(TaxonomyStatus::Inactive);
})->with([
    'Category' => Category::class,
    'Country' => Country::class,
    'TuitionType' => TuitionType::class,
    'Testimonial' => Testimonial::class,
]);

it('casts PaymentGateway gateway to PaymentGatewayType enum', function (): void {
    $gateway = PaymentGateway::query()->updateOrCreate(
        ['gateway' => PaymentGatewayType::Bkash->value],
        [
            'name' => 'bKash',
            'status' => TaxonomyStatus::Active,
            'credentials' => [],
        ],
    );

    $gateway->refresh();

    expect($gateway->gateway)->toBeInstanceOf(PaymentGatewayType::class)
        ->and($gateway->gateway)->toBe(PaymentGatewayType::Bkash)
        ->and($gateway->status)->toBeInstanceOf(TaxonomyStatus::class)
        ->and($gateway->status)->toBe(TaxonomyStatus::Active);
});

it('stores enum values correctly in database for taxonomy models', function (): void {
    $category = Category::factory()->create(['status' => TaxonomyStatus::Active]);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'status' => 'active',
    ]);

    $category->update(['status' => TaxonomyStatus::Inactive]);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'status' => 'inactive',
    ]);
});

it('filters taxonomy models by enum where clause', function (): void {
    Category::factory()->create(['status' => TaxonomyStatus::Active]);
    Category::factory()->create(['status' => TaxonomyStatus::Active]);
    Category::factory()->create(['status' => TaxonomyStatus::Inactive]);

    $active = Category::query()->where('status', TaxonomyStatus::Active)->get();
    $inactive = Category::query()->where('status', TaxonomyStatus::Inactive)->get();

    expect($active)->toHaveCount(2)
        ->and($inactive)->toHaveCount(1);
});

// --- SoftDeletes on financial models ---

it('soft deletes payment and excludes from default query', function (): void {
    $payment = Payment::factory()->create();

    expect(Payment::query()->count())->toBe(1);

    $payment->delete();

    expect(Payment::query()->count())->toBe(0)
        ->and(Payment::withTrashed()->count())->toBe(1)
        ->and($payment->refresh()->trashed())->toBeTrue();
});

it('soft deletes tuition job application', function (): void {
    $application = TuitionJobApplication::factory()->create();

    $application->delete();

    expect(TuitionJobApplication::query()->count())->toBe(0)
        ->and(TuitionJobApplication::withTrashed()->count())->toBe(1)
        ->and($application->refresh()->trashed())->toBeTrue();
});

it('soft deletes tuition job assignment', function (): void {
    $assignment = TuitionJobAssignment::factory()->create();

    $assignment->delete();

    expect(TuitionJobAssignment::query()->count())->toBe(0)
        ->and(TuitionJobAssignment::withTrashed()->count())->toBe(1)
        ->and($assignment->refresh()->trashed())->toBeTrue();
});

it('soft deletes refund request', function (): void {
    $refund = RefundRequest::factory()->create();

    $refund->delete();

    expect(RefundRequest::query()->count())->toBe(0)
        ->and(RefundRequest::withTrashed()->count())->toBe(1)
        ->and($refund->refresh()->trashed())->toBeTrue();
});

it('soft deletes wallet ledger entry', function (): void {
    $entry = WalletLedgerEntry::factory()->create();

    $entry->delete();

    expect(WalletLedgerEntry::query()->count())->toBe(0)
        ->and(WalletLedgerEntry::withTrashed()->count())->toBe(1)
        ->and($entry->refresh()->trashed())->toBeTrue();
});

it('restores soft deleted financial models', function (): void {
    $payment = Payment::factory()->create();
    $payment->delete();

    expect(Payment::query()->count())->toBe(0);

    $payment->restore();

    expect(Payment::query()->count())->toBe(1)
        ->and($payment->refresh()->trashed())->toBeFalse();
});

it('force deletes soft deleted financial models permanently', function (): void {
    $payment = Payment::factory()->create();
    $paymentId = $payment->id;
    $payment->delete();
    $payment->forceDelete();

    expect(Payment::withTrashed()->find($paymentId))->toBeNull();
});

// --- TaxonomyStatus label method ---

it('TaxonomyStatus enum provides human-readable labels', function (): void {
    expect(TaxonomyStatus::Active->label())->toBe('Active')
        ->and(TaxonomyStatus::Inactive->label())->toBe('Inactive');
});
