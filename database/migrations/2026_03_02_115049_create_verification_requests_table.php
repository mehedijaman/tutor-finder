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
        Schema::create('verification_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('role', 20);
            $table->string('status', 30)->default('pending')->index();
            $table->decimal('fee_amount', 12, 2)->default(500.00);
            $table->string('currency', 10)->default('BDT');
            $table->timestamp('submitted_at')->useCurrent();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('decision_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('created_at');
            $table->index(['user_id', 'status']);
            $table->index('deleted_at');
        });

        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $versionData = DB::selectOne('select version() as version');
            $version = strtolower((string) ($versionData->version ?? ''));
            $numericVersion = preg_replace('/[^0-9.].*/', '', $version);

            if (! str_contains($version, 'mariadb') && version_compare($numericVersion, '8.0.0', '>=')) {
                DB::statement("ALTER TABLE verification_requests ADD active_guard TINYINT AS (CASE WHEN status IN ('pending','approved','invoiced') THEN 1 ELSE NULL END) STORED");
                DB::statement('ALTER TABLE verification_requests ADD UNIQUE KEY verification_requests_unique_active_request (user_id, active_guard)');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_requests');
    }
};
