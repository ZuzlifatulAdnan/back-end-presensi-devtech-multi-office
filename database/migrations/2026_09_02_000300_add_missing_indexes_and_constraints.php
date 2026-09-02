<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the filters the admin panel and the API actually run, plus the
 * foreign key that ties a session to its user.
 */
return new class extends Migration
{
    public function up(): void
    {
        // A session pointing at a deleted user would block the constraint.
        DB::table('sessions')
            ->whereNotNull('user_id')
            ->whereNotIn('user_id', DB::table('users')->select('id'))
            ->update(['user_id' => null]);

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_index');
            $table->index('work_mode', 'users_work_mode_index');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index('work_mode', 'attendances_work_mode_index');
        });

        Schema::table('overtimes', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'overtimes_user_id_date_index');
            $table->index('status', 'overtimes_status_index');
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->index('status', 'leaves_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropIndex('leaves_status_index');
        });

        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropIndex('overtimes_status_index');
        });

        // MySQL refuses to drop the last index a foreign key can use, and the
        // composite index below may be exactly that one.
        if (! $this->hasIndex('overtimes', 'overtimes_user_id_foreign')) {
            Schema::table('overtimes', function (Blueprint $table) {
                $table->index('user_id', 'overtimes_user_id_foreign');
            });
        }

        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropIndex('overtimes_user_id_date_index');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_work_mode_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_work_mode_index');
            $table->dropIndex('users_role_index');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        return DB::select(
            'SELECT 1 FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
            [$table, $index]
        ) !== [];
    }
};
