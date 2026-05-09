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
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropColumn('office_id');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null')->after('shift_kerja_id');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropColumn('office_id');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null')->after('shift_id');
        });

        Schema::dropIfExists('offices');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('latitude');
            $table->string('longitude');
            $table->integer('radius_km');
            $table->string('address')->nullable();
            $table->string('attendance_type')->nullable()->default('location_based_only');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
            $table->foreignId('office_id')->nullable()->constrained('offices')->onDelete('set null')->after('shift_kerja_id');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
            $table->foreignId('office_id')->nullable()->constrained('offices')->onDelete('set null')->after('shift_id');
        });
    }
};
