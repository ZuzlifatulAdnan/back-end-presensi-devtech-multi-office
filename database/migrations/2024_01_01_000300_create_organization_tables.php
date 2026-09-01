<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Organisation structure: positions (jabatans) and departments (departemens).
 *
 * The pivot tables predate the move to one-to-one relations on `users` and are
 * kept so existing data stays reachable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jabatans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('departemens', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('jabatan_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jabatan_id')->constrained('jabatans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['jabatan_id', 'user_id']);
        });

        Schema::create('departemen_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departemen_id')->constrained('departemens')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['departemen_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departemen_user');
        Schema::dropIfExists('jabatan_user');
        Schema::dropIfExists('departemens');
        Schema::dropIfExists('jabatans');
    }
};
