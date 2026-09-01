<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->string('attachment_name')->nullable()->after('attachment_url');
            $table->string('attachment_mime', 100)->nullable()->after('attachment_name');
            $table->unsignedInteger('attachment_size')->nullable()->after('attachment_mime');
            $table->timestamp('cancelled_at')->nullable()->after('approved_at');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `leaves` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('leaves', function (Blueprint $table) {
            $table->index(['employee_id', 'status'], 'leaves_employee_id_status_index');
            $table->index(['start_date', 'end_date'], 'leaves_start_date_end_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropIndex('leaves_employee_id_status_index');
            $table->dropIndex('leaves_start_date_end_date_index');
            $table->dropColumn(['attachment_name', 'attachment_mime', 'attachment_size', 'cancelled_at']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `leaves` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        }
    }
};
