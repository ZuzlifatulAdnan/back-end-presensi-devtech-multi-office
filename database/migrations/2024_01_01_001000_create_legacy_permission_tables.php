<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Legacy tables from the first version of the app.
 *
 * `permissions` was the original izin feature, since replaced by `leaves`, and
 * `qr_absens` backed the daily QR check-in. Neither has an Eloquent model any
 * more; they are kept so historical rows stay readable. Drop them in a separate
 * migration once the data has been archived.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date_permission');
            $table->text('reason');
            $table->string('image')->nullable();
            $table->string('document')->nullable();
            $table->string('signed_form_path')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });

        Schema::create('qr_absens', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('qr_checkin')->nullable();
            $table->string('qr_checkout')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_absens');
        Schema::dropIfExists('permissions');
    }
};
