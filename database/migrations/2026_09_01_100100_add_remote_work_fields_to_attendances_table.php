<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('photo_in')->nullable()->after('latlon_out');
            $table->string('photo_out')->nullable()->after('photo_in');
            $table->text('notes_in')->nullable()->after('photo_out');
            $table->text('notes_out')->nullable()->after('notes_in');
            $table->string('address_in', 500)->nullable()->after('notes_out');
            $table->string('address_out', 500)->nullable()->after('address_in');
            $table->unsignedInteger('distance_in_meters')->nullable()->after('address_out');
            $table->unsignedInteger('distance_out_meters')->nullable()->after('distance_in_meters');
            $table->boolean('is_mock_location')->default(false)->after('distance_out_meters');
            $table->unsignedInteger('work_duration_minutes')->nullable()->after('early_leave_minutes');
            $table->string('device_info')->nullable()->after('work_duration_minutes');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(['user_id', 'date'], 'attendances_user_id_date_unique');
            $table->index(['date', 'status'], 'attendances_date_status_index');
            $table->index(['company_id', 'date'], 'attendances_company_id_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_user_id_date_unique');
            $table->dropIndex('attendances_date_status_index');
            $table->dropIndex('attendances_company_id_date_index');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'photo_in',
                'photo_out',
                'notes_in',
                'notes_out',
                'address_in',
                'address_out',
                'distance_in_meters',
                'distance_out_meters',
                'is_mock_location',
                'work_duration_minutes',
                'device_info',
            ]);
        });
    }
};
