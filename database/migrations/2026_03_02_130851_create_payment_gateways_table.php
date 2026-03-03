<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table): void {
            $table->id();
            $table->string('gateway', 40)->unique();
            $table->string('name', 120);
            $table->string('status', 20)->default('active')->index();
            $table->json('credentials')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('deleted_at');
        });

        $siteSetting = null;

        if (Schema::hasTable('site_settings')) {
            $siteSetting = DB::table('site_settings')->where('id', 1)->first();
        }

        $bkashCredentials = [];
        $sslCommerzCredentials = [];

        if ($siteSetting !== null) {
            if (Schema::hasColumn('site_settings', 'bkash_app_key') && ! empty($siteSetting->bkash_app_key)) {
                $bkashCredentials['app_key'] = $siteSetting->bkash_app_key;
            }

            if (Schema::hasColumn('site_settings', 'bkash_app_secret') && ! empty($siteSetting->bkash_app_secret)) {
                $bkashCredentials['app_secret'] = $siteSetting->bkash_app_secret;
            }

            if (Schema::hasColumn('site_settings', 'bkash_username') && ! empty($siteSetting->bkash_username)) {
                $bkashCredentials['username'] = $siteSetting->bkash_username;
            }

            if (Schema::hasColumn('site_settings', 'bkash_password') && ! empty($siteSetting->bkash_password)) {
                $bkashCredentials['password'] = $siteSetting->bkash_password;
            }

            if (Schema::hasColumn('site_settings', 'bkash_base_url') && ! empty($siteSetting->bkash_base_url)) {
                $bkashCredentials['base_url'] = $siteSetting->bkash_base_url;
            }

            if (Schema::hasColumn('site_settings', 'sslcommerz_store_id') && ! empty($siteSetting->sslcommerz_store_id)) {
                $sslCommerzCredentials['store_id'] = $siteSetting->sslcommerz_store_id;
            }

            if (Schema::hasColumn('site_settings', 'sslcommerz_store_password') && ! empty($siteSetting->sslcommerz_store_password)) {
                $sslCommerzCredentials['store_password'] = $siteSetting->sslcommerz_store_password;
            }

            if (Schema::hasColumn('site_settings', 'sslcommerz_mode') && ! empty($siteSetting->sslcommerz_mode)) {
                $sslCommerzCredentials['mode'] = $siteSetting->sslcommerz_mode;
            }
        }

        $now = now();
        $encryptCredentials = static fn (array $credentials): string => Crypt::encryptString(
            json_encode($credentials, JSON_THROW_ON_ERROR),
        );

        DB::table('payment_gateways')->insert([
            [
                'gateway' => 'bkash',
                'name' => 'bKash',
                'status' => 'active',
                'credentials' => $encryptCredentials($bkashCredentials),
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'gateway' => 'sslcommerz',
                'name' => 'SSLCommerz',
                'status' => 'active',
                'credentials' => $encryptCredentials($sslCommerzCredentials),
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'gateway' => 'manual',
                'name' => 'Manual',
                'status' => 'active',
                'credentials' => $encryptCredentials([]),
                'notes' => 'Manual payment requires admin approval.',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
