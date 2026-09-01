<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * White-label configuration read by the mobile app on boot: branding, feature
 * flags, map settings and the supported app versions. Holds a single row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();

            $table->string('app_name')->default('Absensi');
            $table->string('app_short_name')->nullable();
            $table->string('tagline')->nullable();

            $table->string('logo_path')->nullable();
            $table->string('logo_dark_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('login_banner_path')->nullable();

            $table->string('primary_color', 20)->default('#2563eb');
            $table->string('secondary_color', 20)->nullable();

            $table->string('company_name')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_phone', 32)->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();

            $table->string('android_latest_version', 20)->nullable();
            $table->string('android_min_version', 20)->nullable();
            $table->string('ios_latest_version', 20)->nullable();
            $table->string('ios_min_version', 20)->nullable();
            $table->boolean('force_update')->default(false);

            $table->boolean('maintenance_mode')->default(false);
            $table->text('maintenance_message')->nullable();

            $table->boolean('attendance_photo_required')->default(false);
            $table->boolean('wfh_enabled')->default(true);
            $table->boolean('wfh_photo_required')->default(true);
            $table->boolean('wfh_notes_required')->default(true);
            $table->boolean('block_mock_location')->default(true);
            $table->unsignedSmallInteger('location_accuracy_tolerance_meters')->default(50);

            $table->unsignedTinyInteger('map_default_zoom')->default(17);
            $table->string('map_tile_url')->nullable();
            $table->string('map_attribution')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
