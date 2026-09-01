<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Wires the employee record to its office, position, department and default
 * shift. Kept separate because `users` is created before those tables exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('jabatan_id')->references('id')->on('jabatans')->nullOnDelete();
            $table->foreign('departemen_id')->references('id')->on('departemens')->nullOnDelete();
            $table->foreign('shift_kerja_id')->references('id')->on('shift_kerjas')->nullOnDelete();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['jabatan_id']);
            $table->dropForeign(['departemen_id']);
            $table->dropForeign(['shift_kerja_id']);
            $table->dropForeign(['company_id']);
        });
    }
};
