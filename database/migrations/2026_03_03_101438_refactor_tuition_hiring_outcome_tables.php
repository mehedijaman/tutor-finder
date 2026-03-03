<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tuition_job_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_id')->unique()->constrained('tuition_jobs')->cascadeOnDelete();
            $table->foreignId('tutor_user_id')->constrained('users')->restrictOnDelete();
            $table->index('tutor_user_id');

            $table->dateTime('appointed_at')->nullable();
            $table->dateTime('confirmed_at')->nullable()->index();
            $table->dateTime('cancelled_at')->nullable()->index();

            $table->enum('cancelled_by', ['tutor', 'guardian', 'admin', 'system'])->nullable();
            $table->enum('fault', ['tutor_fault', 'guardian_fault', 'mutual', 'valid_other'])->nullable()->index();
            $table->text('cancel_reason')->nullable();
            $table->boolean('reported_within_24h')->default(false);

            $table->enum('duration_type', ['long_term', 'short_term'])->default('long_term')->index();
            $table->unsignedTinyInteger('short_term_months')->nullable();
            $table->decimal('service_fee_rate', 6, 5)->nullable();
            $table->decimal('service_fee_amount', 12, 2)->nullable();
            $table->char('fee_currency', 3)->default('BDT');
            $table->dateTime('fee_due_at')->nullable();
            $table->enum('fee_payment_mode', ['pay_before', 'pay_after_first_month'])->default('pay_before');

            $table->boolean('month1_escrow_required')->default(false);
            $table->dateTime('month1_escrow_paid_at')->nullable();
            $table->dateTime('first_month_received_at')->nullable();
            $table->dateTime('month1_ended_at')->nullable();
            $table->dateTime('month1_settled_at')->nullable();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::table('tuition_job_applications', function (Blueprint $table): void {
            $table->foreignId('job_id')->nullable()->constrained('tuition_jobs')->cascadeOnDelete();
            $table->foreignId('tutor_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->decimal('expected_salary_amount', 12, 2)->nullable();
            $table->char('salary_currency', 3)->default('BDT');
            $table->string('status_new', 32)->default('applied');
            $table->text('cancel_reason')->nullable();
            $table->json('metadata')->nullable();
        });

        $now = now();

        DB::table('tuition_job_applications')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($now): void {
                foreach ($rows as $row) {
                    $statusNew = $this->mapLegacyApplicationStatus((string) $row->status);
                    $cancelReason = null;

                    if ($statusNew === 'cancelled') {
                        $cancelReason = $row->guardian_note;

                        if ($cancelReason === null || trim((string) $cancelReason) === '') {
                            $cancelReason = $row->status === 'withdrawn'
                                ? 'Migrated legacy withdrawn application.'
                                : 'Migrated legacy cancelled application.';
                        }
                    }

                    $metadata = [
                        'legacy_backfill' => true,
                        'legacy_status' => (string) $row->status,
                    ];

                    if ($row->reviewed_by !== null) {
                        $metadata['legacy_reviewed_by'] = (int) $row->reviewed_by;
                    }

                    if ($row->reviewed_at !== null) {
                        $metadata['legacy_reviewed_at'] = (string) $row->reviewed_at;
                    }

                    DB::table('tuition_job_applications')
                        ->where('id', $row->id)
                        ->update([
                            'job_id' => $row->tuition_job_id,
                            'tutor_user_id' => $row->tutor_id,
                            'expected_salary_amount' => $row->expected_salary,
                            'salary_currency' => 'BDT',
                            'status_new' => $statusNew,
                            'cancel_reason' => $cancelReason,
                            'metadata' => json_encode($metadata),
                            'updated_at' => $now,
                        ]);
                }
            });

        DB::table('tuition_jobs')
            ->where('status', 'confirmed')
            ->orderBy('id')
            ->chunkById(100, function ($jobs) use ($now): void {
                foreach ($jobs as $job) {
                    if ($job->selected_tutor_id === null) {
                        DB::table('tuition_jobs')
                            ->where('id', $job->id)
                            ->update([
                                'status' => 'live',
                                'confirmed_by' => null,
                                'confirmed_at' => null,
                                'updated_at' => $now,
                            ]);

                        continue;
                    }

                    $confirmedAt = $job->confirmed_at ?? $now;

                    $assignmentExists = DB::table('tuition_job_assignments')
                        ->where('job_id', $job->id)
                        ->exists();

                    if (! $assignmentExists) {
                        DB::table('tuition_job_assignments')->insert([
                            'job_id' => $job->id,
                            'tutor_user_id' => $job->selected_tutor_id,
                            'appointed_at' => $confirmedAt,
                            'confirmed_at' => $confirmedAt,
                            'cancelled_at' => null,
                            'cancelled_by' => null,
                            'fault' => null,
                            'cancel_reason' => null,
                            'reported_within_24h' => false,
                            'duration_type' => 'long_term',
                            'short_term_months' => null,
                            'service_fee_rate' => null,
                            'service_fee_amount' => null,
                            'fee_currency' => 'BDT',
                            'fee_due_at' => null,
                            'fee_payment_mode' => 'pay_before',
                            'month1_escrow_required' => false,
                            'month1_escrow_paid_at' => null,
                            'first_month_received_at' => null,
                            'month1_ended_at' => null,
                            'month1_settled_at' => null,
                            'notes' => null,
                            'metadata' => json_encode([
                                'legacy_backfill' => true,
                                'phase1_timestamps_equal' => true,
                            ]),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    $selectedApplication = null;

                    if ($job->selected_application_id !== null) {
                        $selectedApplication = DB::table('tuition_job_applications')
                            ->where('id', $job->selected_application_id)
                            ->where('tuition_job_id', $job->id)
                            ->first();
                    }

                    if ($selectedApplication === null) {
                        $selectedApplication = DB::table('tuition_job_applications')
                            ->where('tuition_job_id', $job->id)
                            ->where('tutor_id', $job->selected_tutor_id)
                            ->first();
                    }

                    if ($selectedApplication === null) {
                        $syntheticId = DB::table('tuition_job_applications')->insertGetId([
                            'tuition_job_id' => $job->id,
                            'tutor_id' => $job->selected_tutor_id,
                            'cover_letter' => null,
                            'expected_salary' => null,
                            'status' => 'shortlisted',
                            'guardian_note' => 'Synthetic row created during legacy backfill.',
                            'reviewed_by' => $job->confirmed_by,
                            'reviewed_at' => $confirmedAt,
                            'job_id' => $job->id,
                            'tutor_user_id' => $job->selected_tutor_id,
                            'expected_salary_amount' => null,
                            'salary_currency' => 'BDT',
                            'status_new' => 'confirmed',
                            'cancel_reason' => null,
                            'metadata' => json_encode([
                                'synthetic' => true,
                                'source' => 'legacy_backfill',
                                'legacy_selected_application_id' => $job->selected_application_id,
                            ]),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);

                        $selectedApplication = (object) ['id' => $syntheticId];
                    }

                    $selectedMetadata = DB::table('tuition_job_applications')
                        ->where('id', $selectedApplication->id)
                        ->value('metadata');

                    $selectedMetadataPayload = $this->decodeMetadata($selectedMetadata);
                    $selectedMetadataPayload['selected_from_legacy_confirmed_job'] = true;

                    DB::table('tuition_job_applications')
                        ->where('id', $selectedApplication->id)
                        ->update([
                            'status_new' => 'confirmed',
                            'cancel_reason' => null,
                            'metadata' => json_encode($selectedMetadataPayload),
                            'updated_at' => $now,
                        ]);

                    DB::table('tuition_job_applications')
                        ->where('tuition_job_id', $job->id)
                        ->where('id', '!=', $selectedApplication->id)
                        ->whereIn('status_new', ['applied', 'shortlisted', 'appointed'])
                        ->update([
                            'status_new' => 'cancelled',
                            'cancel_reason' => 'Job confirmed with another tutor (legacy migration backfill).',
                            'updated_at' => $now,
                        ]);
                }
            });

        $this->dropLegacyApplicationStatusCheck();

        Schema::table('tuition_job_applications', function (Blueprint $table): void {
            $table->dropUnique(['tuition_job_id', 'tutor_id']);
            $table->dropIndex(['tutor_id', 'status']);
            $table->dropIndex(['tuition_job_id', 'status']);
            $table->dropIndex(['status']);
        });

        Schema::table('tuition_job_applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropConstrainedForeignId('tuition_job_id');
            $table->dropConstrainedForeignId('tutor_id');
            $table->dropColumn(['expected_salary', 'guardian_note', 'reviewed_at', 'status']);
            $table->renameColumn('status_new', 'status');
        });

        Schema::table('tuition_job_applications', function (Blueprint $table): void {
            $table->unsignedBigInteger('job_id')->nullable(false)->change();
            $table->unsignedBigInteger('tutor_user_id')->nullable(false)->change();
            $table->unique(['job_id', 'tutor_user_id']);
            $table->index(['tutor_user_id', 'status']);
            $table->index(['job_id', 'status']);
        });

        Schema::table('tuition_jobs', function (Blueprint $table): void {
            $table->dropIndex(['selected_tutor_id']);
            $table->dropIndex(['selected_application_id']);
            $table->dropConstrainedForeignId('selected_application_id');
            $table->dropConstrainedForeignId('selected_tutor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tuition_jobs', function (Blueprint $table): void {
            $table->foreignId('selected_tutor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('selected_application_id')
                ->nullable()
                ->constrained('tuition_job_applications')
                ->nullOnDelete();
            $table->index('selected_tutor_id');
            $table->index('selected_application_id');
        });

        Schema::table('tuition_job_applications', function (Blueprint $table): void {
            $table->dropUnique(['job_id', 'tutor_user_id']);
            $table->dropIndex(['tutor_user_id', 'status']);
            $table->dropIndex(['job_id', 'status']);
        });

        Schema::table('tuition_job_applications', function (Blueprint $table): void {
            $table->enum('status_old', ['pending', 'shortlisted', 'rejected', 'withdrawn'])->default('pending');
            $table->foreignId('tuition_job_id')->nullable()->constrained('tuition_jobs')->cascadeOnDelete();
            $table->foreignId('tutor_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->decimal('expected_salary', 12, 2)->nullable();
            $table->text('guardian_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
        });

        DB::table('tuition_job_applications')
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    $legacyStatus = match ((string) $row->status) {
                        'applied' => 'pending',
                        'shortlisted' => 'shortlisted',
                        'appointed' => 'shortlisted',
                        'confirmed' => 'shortlisted',
                        'cancelled' => 'rejected',
                        default => 'pending',
                    };

                    DB::table('tuition_job_applications')
                        ->where('id', $row->id)
                        ->update([
                            'status_old' => $legacyStatus,
                            'tuition_job_id' => $row->job_id,
                            'tutor_id' => $row->tutor_user_id,
                            'expected_salary' => $row->expected_salary_amount,
                            'guardian_note' => $row->cancel_reason,
                        ]);
                }
            });

        Schema::table('tuition_job_applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('job_id');
            $table->dropConstrainedForeignId('tutor_user_id');
            $table->dropColumn(['expected_salary_amount', 'salary_currency', 'status', 'cancel_reason', 'metadata']);
            $table->renameColumn('status_old', 'status');
            $table->unsignedBigInteger('tuition_job_id')->nullable(false)->change();
            $table->unsignedBigInteger('tutor_id')->nullable(false)->change();
            $table->unique(['tuition_job_id', 'tutor_id']);
            $table->index('status');
            $table->index(['tutor_id', 'status']);
            $table->index(['tuition_job_id', 'status']);
        });

        Schema::dropIfExists('tuition_job_assignments');
    }

    private function mapLegacyApplicationStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'applied',
            'shortlisted' => 'shortlisted',
            'rejected', 'withdrawn' => 'cancelled',
            default => 'applied',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeMetadata(mixed $value): array
    {
        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        try {
            $decoded = json_decode($value, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function dropLegacyApplicationStatusCheck(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'tuition_job_applications')
                ->where('CONSTRAINT_NAME', 'tuition_job_applications_status_check')
                ->exists();

            if ($exists) {
                DB::statement('ALTER TABLE tuition_job_applications DROP CHECK tuition_job_applications_status_check');
            }
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE tuition_job_applications DROP CONSTRAINT IF EXISTS tuition_job_applications_status_check');
        }
    }
};
