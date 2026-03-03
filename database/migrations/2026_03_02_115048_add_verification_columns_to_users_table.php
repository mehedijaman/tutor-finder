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
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('verified_at')->nullable()->index()->after('phone_verified_at');
            $table->string('verification_status', 30)->default('unverified')->index()->after('verified_at');
            $table->string('verification_type', 20)->nullable()->index()->after('verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'verified_at',
                'verification_status',
                'verification_type',
            ]);
        });
    }
};
