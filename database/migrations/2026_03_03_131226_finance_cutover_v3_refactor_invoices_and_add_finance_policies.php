<?php

use Carbon\CarbonInterface;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        if (! Schema::hasColumn('tuition_job_assignments', 'salary_base_amount')) {
            Schema::table('tuition_job_assignments', function (Blueprint $table): void {
                $table->decimal('salary_base_amount', 12, 2)->nullable()->after('short_term_months');
                $table->string('salary_base_source', 32)->nullable()->after('salary_base_amount');
            });
        }

        Schema::table('site_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_settings', 'platform_owner_user_id')) {
                $table->foreignId('platform_owner_user_id')->nullable()->after('bin_no')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('site_settings', 'platform_service_fee_rate')) {
                $table->decimal('platform_service_fee_rate', 6, 5)->nullable()->after('platform_owner_user_id');
            }

            if (! Schema::hasColumn('site_settings', 'platform_service_fee_due_days')) {
                $table->unsignedTinyInteger('platform_service_fee_due_days')->default(10)->after('platform_service_fee_rate');
            }

            if (! Schema::hasColumn('site_settings', 'default_fee_currency')) {
                $table->char('default_fee_currency', 3)->default('BDT')->after('platform_service_fee_due_days');
            }

            if (! Schema::hasColumn('site_settings', 'default_fee_payment_mode')) {
                $table->string('default_fee_payment_mode', 40)->default('pay_before')->after('default_fee_currency');
            }
        });

        Schema::table('invoices', function (Blueprint $table): void {
            if (! Schema::hasColumn('invoices', 'payer_user_id')) {
                $table->foreignId('payer_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('invoices', 'payee_user_id')) {
                $table->foreignId('payee_user_id')->nullable()->after('payer_user_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('invoices', 'type')) {
                $table->string('type', 60)->nullable()->after('payee_user_id');
                $table->index('type');
            }

            if (! Schema::hasColumn('invoices', 'job_assignment_id')) {
                $table->foreignId('job_assignment_id')->nullable()->after('invoiceable_id')->constrained('tuition_job_assignments')->nullOnDelete();
            }

            if (! Schema::hasColumn('invoices', 'status_new')) {
                $table->string('status_new', 30)->default('unpaid')->after('status');
                $table->index('status_new');
            }

            if (! Schema::hasColumn('invoices', 'due_at')) {
                $table->timestamp('due_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('invoices', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('due_at');
            }
        });

        $platformUserId = $this->resolvePlatformUserId();
        $this->ensureSiteSettingsFinanceDefaults($platformUserId, $now);

        DB::table('invoices')
            ->orderBy('id')
            ->chunkById(200, function ($invoices) use ($platformUserId, $now): void {
                foreach ($invoices as $invoice) {
                    $legacyStatus = strtolower(trim((string) $invoice->status));
                    $mappedStatus = $this->mapLegacyStatus($legacyStatus);
                    $invoiceType = $this->resolveLegacyInvoiceType((string) $invoice->invoiceable_type, (int) $invoice->invoiceable_id);
                    $invoiceDate = $invoice->created_at
                        ? Carbon::parse((string) $invoice->created_at)->format('Ymd')
                        : $now->format('Ymd');
                    $invoiceNo = trim((string) $invoice->invoice_no);

                    if ($invoiceNo === '') {
                        $invoiceNo = sprintf('INV-%s-%06d', $invoiceDate, $invoice->id);
                    }

                    $metadata = $this->decodeLegacyPayload($invoice->gateway_payload);
                    $metadata['legacy_status'] = $legacyStatus;

                    if ($legacyStatus === 'processing') {
                        $metadata['legacy_status_hint'] = 'processing_migrated_to_unpaid';
                    }

                    DB::table('invoices')
                        ->where('id', $invoice->id)
                        ->update([
                            'invoice_no' => $invoiceNo,
                            'payer_user_id' => $invoice->user_id,
                            'payee_user_id' => $platformUserId,
                            'type' => $invoiceType,
                            'job_assignment_id' => $invoice->job_assignment_id,
                            'status_new' => $mappedStatus,
                            'user_id' => $invoice->user_id,
                            'gateway_payload' => json_encode($metadata),
                            'updated_at' => $now,
                        ]);
                }
            });

        try {
            Schema::table('invoices', function (Blueprint $table): void {
                $table->dropIndex(['status']);
            });
        } catch (Throwable) {
            // Keep migration idempotent across engines/schema histories.
        }

        Schema::table('invoices', function (Blueprint $table): void {
            if (Schema::hasColumn('invoices', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('invoices', function (Blueprint $table): void {
            if (Schema::hasColumn('invoices', 'status_new')) {
                $table->renameColumn('status_new', 'status');
                $table->index('status');
            }

            if (Schema::hasColumn('invoices', 'payer_user_id')) {
                $table->index('payer_user_id');
            }

            if (Schema::hasColumn('invoices', 'payee_user_id')) {
                $table->index('payee_user_id');
            }

            if (Schema::hasColumn('invoices', 'job_assignment_id')) {
                $table->index('job_assignment_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            if (Schema::hasColumn('invoices', 'job_assignment_id')) {
                $table->dropConstrainedForeignId('job_assignment_id');
            }

            if (Schema::hasColumn('invoices', 'payee_user_id')) {
                $table->dropConstrainedForeignId('payee_user_id');
            }

            if (Schema::hasColumn('invoices', 'payer_user_id')) {
                $table->dropConstrainedForeignId('payer_user_id');
            }

            $dropColumns = [];

            if (Schema::hasColumn('invoices', 'type')) {
                $dropColumns[] = 'type';
            }

            if (Schema::hasColumn('invoices', 'status_new')) {
                $dropColumns[] = 'status_new';
            }

            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });

        Schema::table('site_settings', function (Blueprint $table): void {
            $dropColumns = [];

            if (Schema::hasColumn('site_settings', 'platform_owner_user_id')) {
                $table->dropConstrainedForeignId('platform_owner_user_id');
            }

            foreach ([
                'platform_service_fee_rate',
                'platform_service_fee_due_days',
                'default_fee_currency',
                'default_fee_payment_mode',
            ] as $column) {
                if (Schema::hasColumn('site_settings', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });

        Schema::table('tuition_job_assignments', function (Blueprint $table): void {
            $dropColumns = [];

            if (Schema::hasColumn('tuition_job_assignments', 'salary_base_amount')) {
                $dropColumns[] = 'salary_base_amount';
            }

            if (Schema::hasColumn('tuition_job_assignments', 'salary_base_source')) {
                $dropColumns[] = 'salary_base_source';
            }

            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }

    private function resolvePlatformUserId(): int
    {
        $email = 'platform@system.local';

        $platformUser = DB::table('users')
            ->where('email', $email)
            ->first();

        if ($platformUser === null) {
            $id = DB::table('users')->insertGetId([
                'name' => 'Platform System',
                'email' => $email,
                'password' => Hash::make((string) str()->random(40)),
                'role' => 'platform',
                'status' => 'active',
                'phone' => null,
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return (int) $id;
        }

        DB::table('users')
            ->where('id', $platformUser->id)
            ->update([
                'role' => 'platform',
                'status' => 'active',
                'updated_at' => now(),
            ]);

        return (int) $platformUser->id;
    }

    private function ensureSiteSettingsFinanceDefaults(int $platformUserId, CarbonInterface $now): void
    {
        $siteSetting = DB::table('site_settings')->where('id', 1)->first();

        if ($siteSetting === null) {
            return;
        }

        DB::table('site_settings')
            ->where('id', 1)
            ->update([
                'platform_owner_user_id' => $siteSetting->platform_owner_user_id ?: $platformUserId,
                'platform_service_fee_rate' => $siteSetting->platform_service_fee_rate ?? 0.60000,
                'platform_service_fee_due_days' => $siteSetting->platform_service_fee_due_days ?? 10,
                'default_fee_currency' => $siteSetting->default_fee_currency ?? 'BDT',
                'default_fee_payment_mode' => $siteSetting->default_fee_payment_mode ?? 'pay_before',
                'updated_at' => $now,
            ]);
    }

    private function resolveLegacyInvoiceType(string $invoiceableType, int $invoiceableId): string
    {
        if ($invoiceableType === 'App\\Models\\VerificationRequest') {
            $role = DB::table('verification_requests')
                ->where('id', $invoiceableId)
                ->value('role');

            return $role === 'guardian'
                ? 'guardian_verification_fee'
                : 'tutor_verification_fee';
        }

        return 'platform_service_fee';
    }

    private function mapLegacyStatus(string $legacyStatus): string
    {
        return match ($legacyStatus) {
            'paid' => 'paid',
            'void' => 'void',
            'unpaid' => 'unpaid',
            'processing' => 'unpaid',
            'failed' => 'void',
            'cancelled' => 'void',
            default => 'unpaid',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeLegacyPayload(mixed $payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (is_string($payload) && trim($payload) !== '') {
            $decoded = json_decode($payload, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
};
