<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update old attendance_type values to new format
        DB::table('companies')
            ->where('attendance_type', 'gps')
            ->update(['attendance_type' => 'location_based_only']);

        DB::table('companies')
            ->where('attendance_type', 'qr')
            ->update(['attendance_type' => 'location_based_only']);

        DB::table('companies')
            ->where('attendance_type', 'both')
            ->update(['attendance_type' => 'hybrid']);

        // Set default if null
        DB::table('companies')
            ->whereNull('attendance_type')
            ->update(['attendance_type' => 'location_based_only']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old format
        DB::table('companies')
            ->where('attendance_type', 'location_based_only')
            ->update(['attendance_type' => 'gps']);

        DB::table('companies')
            ->where('attendance_type', 'face_recognition_only')
            ->update(['attendance_type' => 'gps']);

        DB::table('companies')
            ->where('attendance_type', 'hybrid')
            ->update(['attendance_type' => 'both']);
    }
};
