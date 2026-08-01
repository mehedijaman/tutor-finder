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
            $table->string('emergency_contact')->nullable()->after('phone_alt');
            $table->string('relationship_to_student')->nullable()->after('guardian_name');
            $table->string('preferred_contact_time')->nullable()->after('notes');
            $table->string('city')->nullable()->after('address');
            $table->string('area')->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guardian_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'emergency_contact',
                'relationship_to_student',
                'preferred_contact_time',
                'city',
                'area',
            ]);
        });
    }
};
