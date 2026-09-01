<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employees and the framework's authentication tables.
 *
 * The foreign keys to companies, jabatans, departemens and shift_kerjas are
 * added in a later migration, once those tables exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->timestamp('password_changed_at')->nullable();
            $table->rememberToken();

            // Two-factor authentication (Laravel Fortify)
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();

            // Contact & access
            $table->string('phone')->nullable();
            $table->string('role')->default('user');
            $table->string('work_mode')->default('wfo');

            // Free-text organisation labels kept for backward compatibility
            $table->string('position')->nullable();
            $table->string('department')->nullable();

            // Organisation relations (constraints added later)
            $table->foreignId('jabatan_id')->nullable();
            $table->foreignId('departemen_id')->nullable();
            $table->foreignId('shift_kerja_id')->nullable();
            $table->foreignId('company_id')->nullable();

            // Mobile app
            $table->text('face_embedding')->nullable();
            $table->string('image_url')->nullable();
            $table->string('fcm_token')->nullable();

            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
