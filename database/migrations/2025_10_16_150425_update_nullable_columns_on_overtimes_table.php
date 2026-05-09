<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('overtimes')) {
            return;
        }

        DB::statement('ALTER TABLE `overtimes` MODIFY `end_time` TIME NULL');
        DB::statement('ALTER TABLE `overtimes` MODIFY `reason` TEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('overtimes')) {
            return;
        }

        DB::table('overtimes')
            ->whereNull('end_time')
            ->update(['end_time' => '00:00:00']);

        DB::table('overtimes')
            ->whereNull('reason')
            ->update(['reason' => '']);

        DB::statement('ALTER TABLE `overtimes` MODIFY `end_time` TIME NOT NULL');
        DB::statement('ALTER TABLE `overtimes` MODIFY `reason` TEXT NOT NULL');
    }
};
