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
        Schema::table('guardian_profiles', function (Blueprint $table): void {
            $table->text('admin_notes')->nullable()->after('notes');
        });

        Schema::table('tutor_profiles', function (Blueprint $table): void {
            $table->text('admin_notes')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guardian_profiles', function (Blueprint $table): void {
            $table->dropColumn('admin_notes');
        });

        Schema::table('tutor_profiles', function (Blueprint $table): void {
            $table->dropColumn('admin_notes');
        });
    }
};
