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
        Schema::table('attendances', function (Blueprint $table) {
            $table->enum('work_mode', ['wfo', 'wfh', 'wfa'])->default('wfo')->after('status');
            $table->foreignId('office_id')->nullable()->constrained('offices')->onDelete('set null')->after('shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropColumn(['work_mode', 'office_id']);
        });
    }
};
