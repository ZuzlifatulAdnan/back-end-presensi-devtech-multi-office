<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Office locations. Every row is a geofence used to validate WFO attendance,
 * so an installation may hold several active sites.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email');
            $table->string('address');

            // Stored as strings for backward compatibility with existing data.
            $table->string('latitude');
            $table->string('longitude');
            $table->string('radius_km');

            $table->string('attendance_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('logo_path')->nullable();

            $table->timestamps();

            $table->index('is_active', 'companies_is_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
