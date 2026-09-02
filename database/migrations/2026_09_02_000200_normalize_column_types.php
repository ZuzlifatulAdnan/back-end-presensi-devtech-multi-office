<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Gives columns the type their data actually has.
 *
 * Coordinates and the geofence radius were stored as strings, which made every
 * distance calculation cast at runtime and allowed non-numeric values to be
 * saved. The minute counters can never be negative.
 *
 * `change()` must restate every attribute of the column, otherwise the ones
 * left out are dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Rows written by the old check-out path counted "early leave" backwards,
        // so leaving after the shift ended produced a negative figure. Zero is
        // the correct value, and the column below can no longer hold a negative.
        DB::table('attendances')->where('early_leave_minutes', '<', 0)->update(['early_leave_minutes' => 0]);
        DB::table('attendances')->where('late_minutes', '<', 0)->update(['late_minutes' => 0]);

        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable(false)->change();
            $table->decimal('longitude', 10, 7)->nullable(false)->change();
            $table->decimal('radius_km', 6, 2)->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable(false)->default('employee')->change();
            $table->string('work_mode')->nullable(false)->default('wfo')->change();
        });

        // Older installations stored the work mode as a free-text column, so an
        // unknown value could be written. The enum is the contract the API and
        // the admin panel already assume.
        DB::table('attendances')
            ->whereNull('work_mode')
            ->orWhereNotIn('work_mode', ['wfo', 'wfh', 'wfa'])
            ->update(['work_mode' => 'wfo']);

        Schema::table('attendances', function (Blueprint $table) {
            $table->enum('work_mode', ['wfo', 'wfh', 'wfa'])->nullable(false)->default('wfo')->change();
            $table->unsignedInteger('late_minutes')->nullable(false)->default(0)->change();
            $table->unsignedInteger('early_leave_minutes')->nullable(false)->default(0)->change();
        });

        Schema::table('shift_kerjas', function (Blueprint $table) {
            $table->boolean('is_cross_day')->nullable(false)->default(false)
                ->comment('Apakah shift melewati tengah malam')->change();
            $table->integer('grace_period_minutes')->nullable(false)->default(10)
                ->comment('Toleransi keterlambatan dalam menit')->change();
            $table->boolean('is_active')->nullable(false)->default(true)
                ->comment('Status aktif shift')->change();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('latitude')->nullable(false)->change();
            $table->string('longitude')->nullable(false)->change();
            $table->string('radius_km')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable(false)->default('user')->change();
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->integer('late_minutes')->nullable(false)->default(0)->change();
            $table->integer('early_leave_minutes')->nullable(false)->default(0)->change();
        });

        // The work mode and shift flags keep their tightened definition: the
        // application has always treated them that way, and loosening them again
        // would let invalid values back in.
    }
};
