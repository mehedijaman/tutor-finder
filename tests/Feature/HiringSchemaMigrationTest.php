<?php

use App\Models\TuitionJob;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

const HIRING_OUTCOME_REFACTOR_MIGRATION = 'database/migrations/2026_03_03_101438_refactor_tuition_hiring_outcome_tables.php';

it('applies the refactored hiring schema', function () {
    expect(Schema::hasTable('tuition_job_assignments'))->toBeTrue();

    expect(Schema::hasColumns('tuition_job_applications', [
        'job_id',
        'tutor_user_id',
        'expected_salary_amount',
        'salary_currency',
        'status',
        'cancel_reason',
        'metadata',
    ]))->toBeTrue();

    expect(Schema::hasColumn('tuition_job_applications', 'tuition_job_id'))->toBeFalse();
    expect(Schema::hasColumn('tuition_job_applications', 'tutor_id'))->toBeFalse();
    expect(Schema::hasColumn('tuition_job_applications', 'expected_salary'))->toBeFalse();
    expect(Schema::hasColumn('tuition_job_applications', 'guardian_note'))->toBeFalse();
    expect(Schema::hasColumn('tuition_job_applications', 'reviewed_by'))->toBeFalse();
    expect(Schema::hasColumn('tuition_job_applications', 'reviewed_at'))->toBeFalse();

    expect(Schema::hasColumn('tuition_jobs', 'selected_tutor_id'))->toBeFalse();
    expect(Schema::hasColumn('tuition_jobs', 'selected_application_id'))->toBeFalse();
});

it('enforces one assignment row per job', function () {
    $job = TuitionJob::factory()->live()->create();
    $firstTutor = User::factory()->tutor()->create();
    $secondTutor = User::factory()->tutor()->create();

    DB::table('tuition_job_assignments')->insert([
        'job_id' => $job->id,
        'tutor_user_id' => $firstTutor->id,
        'appointed_at' => now(),
        'confirmed_at' => now(),
        'duration_type' => 'long_term',
        'fee_currency' => 'BDT',
        'fee_payment_mode' => 'pay_before',
        'month1_escrow_required' => false,
        'reported_within_24h' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::table('tuition_job_assignments')->insert([
        'job_id' => $job->id,
        'tutor_user_id' => $secondTutor->id,
        'appointed_at' => now(),
        'confirmed_at' => now(),
        'duration_type' => 'long_term',
        'fee_currency' => 'BDT',
        'fee_payment_mode' => 'pay_before',
        'month1_escrow_required' => false,
        'reported_within_24h' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('migration backfill maps legacy application statuses to the new funnel', function () {
    if (DB::getDriverName() === 'sqlite') {
        $this->markTestSkipped('Legacy backfill assertions require non-SQLite schema alteration behavior.');
    }

    rollbackHiringOutcomeRefactorMigration();

    expect(Schema::hasColumn('tuition_job_applications', 'tuition_job_id'))->toBeTrue();
    expect(Schema::hasColumn('tuition_job_applications', 'status'))->toBeTrue();
    expect(Schema::hasColumn('tuition_job_applications', 'job_id'))->toBeFalse();

    $job = TuitionJob::factory()->live()->create();

    $appliedTutor = User::factory()->tutor()->create();
    $shortlistedTutor = User::factory()->tutor()->create();
    $rejectedTutor = User::factory()->tutor()->create();
    $withdrawnTutor = User::factory()->tutor()->create();

    DB::table('tuition_job_applications')->insert([
        [
            'tuition_job_id' => $job->id,
            'tutor_id' => $appliedTutor->id,
            'cover_letter' => 'Applied legacy row',
            'expected_salary' => 12000,
            'status' => 'pending',
            'guardian_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'tuition_job_id' => $job->id,
            'tutor_id' => $shortlistedTutor->id,
            'cover_letter' => 'Shortlisted legacy row',
            'expected_salary' => 13000,
            'status' => 'shortlisted',
            'guardian_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'tuition_job_id' => $job->id,
            'tutor_id' => $rejectedTutor->id,
            'cover_letter' => 'Rejected legacy row',
            'expected_salary' => 14000,
            'status' => 'rejected',
            'guardian_note' => 'Not selected',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'tuition_job_id' => $job->id,
            'tutor_id' => $withdrawnTutor->id,
            'cover_letter' => 'Withdrawn legacy row',
            'expected_salary' => 15000,
            'status' => 'withdrawn',
            'guardian_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    runHiringOutcomeRefactorMigration();

    $appliedRow = DB::table('tuition_job_applications')
        ->where('job_id', $job->id)
        ->where('tutor_user_id', $appliedTutor->id)
        ->first();
    $shortlistedRow = DB::table('tuition_job_applications')
        ->where('job_id', $job->id)
        ->where('tutor_user_id', $shortlistedTutor->id)
        ->first();
    $rejectedRow = DB::table('tuition_job_applications')
        ->where('job_id', $job->id)
        ->where('tutor_user_id', $rejectedTutor->id)
        ->first();
    $withdrawnRow = DB::table('tuition_job_applications')
        ->where('job_id', $job->id)
        ->where('tutor_user_id', $withdrawnTutor->id)
        ->first();

    expect($appliedRow)->not->toBeNull();
    expect($shortlistedRow)->not->toBeNull();
    expect($rejectedRow)->not->toBeNull();
    expect($withdrawnRow)->not->toBeNull();

    expect($appliedRow->status)->toBe('applied');
    expect($shortlistedRow->status)->toBe('shortlisted');
    expect($rejectedRow->status)->toBe('cancelled');
    expect($rejectedRow->cancel_reason)->toBe('Not selected');
    expect($withdrawnRow->status)->toBe('cancelled');
    expect($withdrawnRow->cancel_reason)->toBe('Migrated legacy withdrawn application.');
});

it('migration backfill creates synthetic confirmed application when selected application is missing', function () {
    if (DB::getDriverName() === 'sqlite') {
        $this->markTestSkipped('Legacy backfill assertions require non-SQLite schema alteration behavior.');
    }

    rollbackHiringOutcomeRefactorMigration();

    $admin = User::factory()->admin()->create();
    $guardian = User::factory()->guardian()->create();
    $selectedTutor = User::factory()->tutor()->create();
    $otherTutor = User::factory()->tutor()->create();
    $confirmedAt = now()->subHour();

    $job = TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => TuitionJob::STATUS_CONFIRMED,
        'published_at' => now()->subDay(),
        'expires_at' => now()->addDays(7),
        'confirmed_by' => $admin->id,
        'confirmed_at' => $confirmedAt,
    ]);

    DB::table('tuition_jobs')
        ->where('id', $job->id)
        ->update([
            'selected_tutor_id' => $selectedTutor->id,
            'selected_application_id' => null,
        ]);

    expect(DB::table('tuition_jobs')->where('id', $job->id)->value('selected_tutor_id'))
        ->toBe($selectedTutor->id);

    DB::table('tuition_job_applications')->insert([
        'tuition_job_id' => $job->id,
        'tutor_id' => $otherTutor->id,
        'cover_letter' => 'Legacy open application',
        'expected_salary' => 14000,
        'status' => 'pending',
        'guardian_note' => null,
        'reviewed_by' => null,
        'reviewed_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    runHiringOutcomeRefactorMigration();

    $this->assertDatabaseHas('tuition_job_assignments', [
        'job_id' => $job->id,
        'tutor_user_id' => $selectedTutor->id,
    ]);

    $syntheticApplication = DB::table('tuition_job_applications')
        ->where('job_id', $job->id)
        ->where('tutor_user_id', $selectedTutor->id)
        ->first();

    expect($syntheticApplication)->not->toBeNull();
    expect($syntheticApplication->status)->toBe('confirmed');

    $metadata = json_decode((string) $syntheticApplication->metadata, true);

    expect($metadata)->toBeArray()
        ->and($metadata['synthetic'] ?? false)->toBeTrue()
        ->and($metadata['source'] ?? null)->toBe('legacy_backfill');

    $this->assertDatabaseHas('tuition_job_applications', [
        'job_id' => $job->id,
        'tutor_user_id' => $otherTutor->id,
        'status' => 'cancelled',
    ]);
});

it('migration backfill downgrades orphan confirmed jobs without selected tutor to live', function () {
    if (DB::getDriverName() === 'sqlite') {
        $this->markTestSkipped('Legacy backfill assertions require non-SQLite schema alteration behavior.');
    }

    rollbackHiringOutcomeRefactorMigration();

    $admin = User::factory()->admin()->create();
    $guardian = User::factory()->guardian()->create();

    $job = TuitionJob::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => TuitionJob::STATUS_CONFIRMED,
        'published_at' => now()->subDay(),
        'expires_at' => now()->addDays(5),
        'confirmed_by' => $admin->id,
        'confirmed_at' => now()->subHour(),
    ]);

    DB::table('tuition_jobs')
        ->where('id', $job->id)
        ->update([
            'selected_tutor_id' => null,
            'selected_application_id' => null,
        ]);

    runHiringOutcomeRefactorMigration();

    $this->assertDatabaseHas('tuition_jobs', [
        'id' => $job->id,
        'status' => TuitionJob::STATUS_LIVE,
        'confirmed_by' => null,
        'confirmed_at' => null,
    ]);
    $this->assertDatabaseMissing('tuition_job_assignments', [
        'job_id' => $job->id,
    ]);
});

function rollbackHiringOutcomeRefactorMigration(): void
{
    hiringOutcomeRefactorMigration()->down();
}

function runHiringOutcomeRefactorMigration(): void
{
    hiringOutcomeRefactorMigration()->up();
}

function hiringOutcomeRefactorMigration(): Migration
{
    /** @var Migration $migration */
    $migration = require base_path(HIRING_OUTCOME_REFACTOR_MIGRATION);

    return $migration;
}
