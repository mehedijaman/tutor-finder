<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $columns = [
                'bkash_app_key',
                'bkash_app_secret',
                'bkash_username',
                'bkash_password',
                'bkash_base_url',
                'sslcommerz_store_id',
                'sslcommerz_store_password',
                'sslcommerz_mode',
            ];

            $existingColumns = array_filter($columns, fn (string $column): bool => Schema::hasColumn('site_settings', $column));

            if ($existingColumns === []) {
                return;
            }

            $table->dropColumn($existingColumns);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_settings', 'bkash_app_key')) {
                $table->string('bkash_app_key')->nullable()->after('bin_no');
            }

            if (! Schema::hasColumn('site_settings', 'bkash_app_secret')) {
                $table->text('bkash_app_secret')->nullable()->after('bkash_app_key');
            }

            if (! Schema::hasColumn('site_settings', 'bkash_username')) {
                $table->string('bkash_username')->nullable()->after('bkash_app_secret');
            }

            if (! Schema::hasColumn('site_settings', 'bkash_password')) {
                $table->text('bkash_password')->nullable()->after('bkash_username');
            }

            if (! Schema::hasColumn('site_settings', 'bkash_base_url')) {
                $table->string('bkash_base_url')->nullable()->after('bkash_password');
            }

            if (! Schema::hasColumn('site_settings', 'sslcommerz_store_id')) {
                $table->string('sslcommerz_store_id')->nullable()->after('bkash_base_url');
            }

            if (! Schema::hasColumn('site_settings', 'sslcommerz_store_password')) {
                $table->text('sslcommerz_store_password')->nullable()->after('sslcommerz_store_id');
            }

            if (! Schema::hasColumn('site_settings', 'sslcommerz_mode')) {
                $table->string('sslcommerz_mode', 20)->nullable()->after('sslcommerz_store_password');
            }
        });
    }
};
