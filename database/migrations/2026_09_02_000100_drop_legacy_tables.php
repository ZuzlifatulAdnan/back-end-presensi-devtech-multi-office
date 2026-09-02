<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes tables that no longer back any model.
 *
 * - `permissions` and `qr_absens`: the first izin and QR check-in features,
 *   replaced by `leaves` and the GPS/photo flow. Both were empty.
 * - the three `*_user` pivots: superseded by the jabatan_id / departemen_id /
 *   shift_kerja_id columns on `users`, which already hold the same assignments.
 *
 * A dump of the rows is kept at storage/app/backups/legacy-tables-*.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->backfillFromPivot('jabatan_user', 'jabatan_id');
        $this->backfillFromPivot('departemen_user', 'departemen_id');
        $this->backfillFromPivot('shift_kerja_user', 'shift_kerja_id');

        Schema::dropIfExists('shift_kerja_user');
        Schema::dropIfExists('departemen_user');
        Schema::dropIfExists('jabatan_user');
        Schema::dropIfExists('qr_absens');
        Schema::dropIfExists('permissions');
    }

    /**
     * Copies an assignment into `users` when the column is still empty, so a
     * pivot row that exists nowhere else is not lost with the table.
     *
     * Rows where `users` already holds a value are left alone: that column is
     * the one the application reads, so it wins over the older pivot.
     */
    private function backfillFromPivot(string $pivot, string $column): void
    {
        if (! Schema::hasTable($pivot)) {
            return;
        }

        $assignments = DB::table($pivot)
            ->select('user_id', $column)
            ->orderBy('id')
            ->get()
            ->unique('user_id');

        foreach ($assignments as $assignment) {
            DB::table('users')
                ->where('id', $assignment->user_id)
                ->whereNull($column)
                ->update([$column => $assignment->{$column}]);
        }
    }

    /**
     * Recreates the structures only. Restore the rows from the SQL dump above.
     */
    public function down(): void
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

        Schema::create('shift_kerja_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_kerja_id')->constrained('shift_kerjas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['shift_kerja_id', 'user_id']);
        });
    }
};
