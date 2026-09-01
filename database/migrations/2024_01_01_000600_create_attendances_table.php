<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per employee per day, covering WFO, WFH and WFA presence.
 *
 * The unique key on (user_id, date) is what makes a double check-in impossible
 * even when two requests race each other.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shift_kerjas')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();

            $table->date('date');
            $table->time('time_in');
            $table->time('time_out')->nullable();

            // Check-in evidence
            $table->string('latlon_in');
            $table->string('photo_in')->nullable();
            $table->text('notes_in')->nullable();
            $table->string('address_in', 500)->nullable();
            $table->unsignedInteger('distance_in_meters')->nullable();

            // Check-out evidence
            $table->string('latlon_out')->nullable();
            $table->string('photo_out')->nullable();
            $table->text('notes_out')->nullable();
            $table->string('address_out', 500)->nullable();
            $table->unsignedInteger('distance_out_meters')->nullable();

            $table->boolean('is_mock_location')->default(false);
            $table->string('device_info')->nullable();

            $table->string('status')->default('on_time')->comment('on_time, late, absent');
            $table->enum('work_mode', ['wfo', 'wfh', 'wfa'])->default('wfo');

            $table->boolean('is_weekend')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->boolean('holiday_work')->default(false);

            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            $table->unsignedInteger('work_duration_minutes')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'date'], 'attendances_user_id_date_unique');
            $table->index(['date', 'status'], 'attendances_date_status_index');
            $table->index(['company_id', 'date'], 'attendances_company_id_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
