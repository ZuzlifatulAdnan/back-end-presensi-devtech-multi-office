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
        Schema::table('shift_kerjas', function (Blueprint $table) {
            $table->boolean('is_cross_day')->default(false)->after('end_time')
                ->comment('Apakah shift melewati tengah malam');
            $table->integer('grace_period_minutes')->default(10)->after('is_cross_day')
                ->comment('Toleransi keterlambatan dalam menit');
            $table->boolean('is_active')->default(true)->after('grace_period_minutes')
                ->comment('Status aktif shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_kerjas', function (Blueprint $table) {
            $table->dropColumn(['is_cross_day', 'grace_period_minutes', 'is_active']);
        });
    }
};
