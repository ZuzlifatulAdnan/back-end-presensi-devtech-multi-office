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
            $table->boolean('is_weekend')->default(false)->after('status');
            $table->boolean('is_holiday')->default(false)->after('is_weekend');
            $table->boolean('holiday_work')->default(false)->after('is_holiday');
            $table->integer('late_minutes')->default(0)->after('holiday_work');
            $table->integer('early_leave_minutes')->default(0)->after('late_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['is_weekend', 'is_holiday', 'holiday_work', 'late_minutes', 'early_leave_minutes']);
        });
    }
};
