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
        Schema::table('users', function (Blueprint $table) {
            // Add foreign key columns for one-to-one relationships
            $table->foreignId('jabatan_id')->nullable()->after('department')->constrained('jabatans')->nullOnDelete();
            $table->foreignId('departemen_id')->nullable()->after('jabatan_id')->constrained('departemens')->nullOnDelete();
            $table->foreignId('shift_kerja_id')->nullable()->after('departemen_id')->constrained('shift_kerjas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['jabatan_id']);
            $table->dropForeign(['departemen_id']);
            $table->dropForeign(['shift_kerja_id']);
            $table->dropColumn(['jabatan_id', 'departemen_id', 'shift_kerja_id']);
        });
    }
};
