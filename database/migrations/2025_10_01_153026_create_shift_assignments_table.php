<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shift_kerjas')->cascadeOnDelete();
            $table->date('date')->comment('Tanggal assignment shift');
            $table->string('status')->default('scheduled')->comment('scheduled, completed, absent, leave');
            $table->text('notes')->nullable()->comment('Catatan khusus untuk assignment ini');
            $table->timestamps();

            // Unique constraint: satu user hanya bisa punya satu shift per hari
            $table->unique(['user_id', 'date'], 'unique_user_shift_per_day');

            // Index untuk query performa
            $table->index(['date', 'shift_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_assignments');
    }
};
