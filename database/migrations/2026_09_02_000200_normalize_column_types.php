<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable(false)->change();
            $table->decimal('longitude', 10, 7)->nullable(false)->change();
            $table->decimal('radius_km', 6, 2)->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable(false)->default('employee')->change();
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedInteger('late_minutes')->nullable(false)->default(0)->change();
            $table->unsignedInteger('early_leave_minutes')->nullable(false)->default(0)->change();
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
    }
};
