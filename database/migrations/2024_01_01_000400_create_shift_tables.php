<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Work shifts, plus the per-day roster that overrides an employee's default shift.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_kerjas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_cross_day')->default(false)
                ->comment('Apakah shift melewati tengah malam');
            $table->integer('grace_period_minutes')->default(10)
                ->comment('Toleransi keterlambatan dalam menit');
            $table->boolean('is_active')->default(true)
                ->comment('Status aktif shift');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('shift_kerja_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_kerja_id')->constrained('shift_kerjas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['shift_kerja_id', 'user_id']);
        });

        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shift_kerjas')->cascadeOnDelete();
            $table->date('date')->comment('Tanggal assignment shift');
            $table->string('status')->default('scheduled')
                ->comment('scheduled, completed, absent, leave');
            $table->text('notes')->nullable()
                ->comment('Catatan khusus untuk assignment ini');
            $table->timestamps();

            $table->unique(['user_id', 'date'], 'unique_user_shift_per_day');
            $table->index(['date', 'shift_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_assignments');
        Schema::dropIfExists('shift_kerja_user');
        Schema::dropIfExists('shift_kerjas');
    }
};
