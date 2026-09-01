<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Izin / cuti: the types, the per-year quota per employee, and the requests
 * themselves together with their supporting document.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('quota_days');
            $table->boolean('is_paid')->default(true);
            $table->timestamps();
        });

        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->year('year');
            $table->integer('quota_days')->default(0);
            $table->integer('used_days')->default(0);
            $table->integer('remaining_days')->default(0);
            $table->integer('carry_over_days')->default(0);
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id', 'year']);
        });

        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();

            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason')->nullable();

            // Supporting document (surat dokter, surat tugas, dsb.)
            $table->string('attachment_url')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime', 100)->nullable();
            $table->unsignedInteger('attachment_size')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'status'], 'leaves_employee_id_status_index');
            $table->index(['start_date', 'end_date'], 'leaves_start_date_end_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaves');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_types');
    }
};
