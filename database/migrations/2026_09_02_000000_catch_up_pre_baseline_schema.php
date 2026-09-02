<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Brings a database that predates the squashed baseline up to it.
 *
 * The baseline migrations create these objects for a fresh install, but they
 * cannot run against a database whose tables already exist. This migration adds
 * only what is missing, so both paths converge on the same schema. On an
 * installation that is already at the baseline every step is skipped.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addUserColumns();
        $this->addCompanyColumns();
        $this->addAttendanceColumns();
        $this->addAttendanceIndexes();
        $this->addLeaveAttachmentColumns();
        $this->createAppSettingsTable();
    }

    /**
     * Deliberately empty: every step above is additive and guarded, and the
     * baseline migrations own the structures being completed here.
     */
    public function down(): void {}

    private function addUserColumns(): void
    {
        if (Schema::hasColumn('users', 'password_changed_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('password_changed_at')->nullable()->after('password');
        });
    }

    private function addCompanyColumns(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('attendance_type');
            }

            if (! Schema::hasColumn('companies', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('is_active');
            }
        });

        if (! $this->hasIndex('companies', 'companies_is_active_index')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->index('is_active', 'companies_is_active_index');
            });
        }
    }

    private function addAttendanceColumns(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'photo_in')) {
                $table->string('photo_in')->nullable()->after('latlon_out');
                $table->string('photo_out')->nullable()->after('photo_in');
                $table->text('notes_in')->nullable()->after('photo_out');
                $table->text('notes_out')->nullable()->after('notes_in');
                $table->string('address_in', 500)->nullable()->after('notes_out');
                $table->string('address_out', 500)->nullable()->after('address_in');
                $table->unsignedInteger('distance_in_meters')->nullable()->after('address_out');
                $table->unsignedInteger('distance_out_meters')->nullable()->after('distance_in_meters');
                $table->boolean('is_mock_location')->default(false)->after('distance_out_meters');
            }

            if (! Schema::hasColumn('attendances', 'work_duration_minutes')) {
                $table->unsignedInteger('work_duration_minutes')->nullable()->after('early_leave_minutes');
                $table->string('device_info')->nullable()->after('work_duration_minutes');
            }
        });
    }

    /**
     * The unique key is what makes a double check-in impossible, so any rows
     * left over from the old flow have to be resolved before it can exist.
     */
    private function addAttendanceIndexes(): void
    {
        if (! $this->hasIndex('attendances', 'attendances_user_id_date_unique')) {
            $this->removeDuplicateAttendances();

            Schema::table('attendances', function (Blueprint $table) {
                $table->unique(['user_id', 'date'], 'attendances_user_id_date_unique');
            });
        }

        if (! $this->hasIndex('attendances', 'attendances_date_status_index')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->index(['date', 'status'], 'attendances_date_status_index');
            });
        }

        if (! $this->hasIndex('attendances', 'attendances_company_id_date_index')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->index(['company_id', 'date'], 'attendances_company_id_date_index');
            });
        }
    }

    /**
     * The old check-out path could file a second check-in for the same day: a
     * row whose `time_in` is really the check-out moment, with no `time_out` and
     * a nonsense late count, while the genuine row already recorded the
     * check-out seconds later.
     *
     * The row keeping the most information wins — a check-out present, then the
     * earliest check-in. Discarded rows are written to storage/app/backups
     * before they are deleted.
     */
    private function removeDuplicateAttendances(): void
    {
        $duplicates = DB::table('attendances')
            ->select('user_id', 'date')
            ->groupBy('user_id', 'date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            return;
        }

        $discarded = collect();

        foreach ($duplicates as $duplicate) {
            $rows = DB::table('attendances')
                ->where('user_id', $duplicate->user_id)
                ->where('date', $duplicate->date)
                ->orderBy('id')
                ->get();

            $keep = $rows
                ->sortBy([
                    fn ($row) => $row->time_out === null ? 1 : 0,
                    fn ($row) => $row->time_in,
                ])
                ->first();

            $discarded = $discarded->merge($rows->reject(fn ($row) => $row->id === $keep->id));
        }

        if ($discarded->isEmpty()) {
            return;
        }

        $this->backupRows('attendances', $discarded);

        DB::table('attendances')->whereIn('id', $discarded->pluck('id'))->delete();
    }

    private function addLeaveAttachmentColumns(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            if (! Schema::hasColumn('leaves', 'attachment_name')) {
                $table->string('attachment_name')->nullable()->after('attachment_url');
                $table->string('attachment_mime', 100)->nullable()->after('attachment_name');
                $table->unsignedInteger('attachment_size')->nullable()->after('attachment_mime');
            }

            if (! Schema::hasColumn('leaves', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('approved_at');
            }
        });

        $status = DB::selectOne(
            "SELECT COLUMN_TYPE t FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'leaves' AND COLUMN_NAME = 'status'"
        );

        if ($status && ! str_contains($status->t, 'cancelled') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `leaves` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
        }

        if (! $this->hasIndex('leaves', 'leaves_employee_id_status_index')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->index(['employee_id', 'status'], 'leaves_employee_id_status_index');
            });
        }

        if (! $this->hasIndex('leaves', 'leaves_start_date_end_date_index')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->index(['start_date', 'end_date'], 'leaves_start_date_end_date_index');
            });
        }
    }

    private function createAppSettingsTable(): void
    {
        if (Schema::hasTable('app_settings')) {
            return;
        }

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

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $rows
     */
    private function backupRows(string $table, $rows): void
    {
        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);

        $path = $directory.'/'.$table.'-dihapus-'.now()->format('Y-m-d-His').'.sql';

        $sql = "-- Baris `$table` yang dibuang saat migrasi 2026_09_02_000000\n";
        $sql .= '-- Dibuat: '.now()->toDateTimeString()."\n\n";

        foreach ($rows as $row) {
            $data = (array) $row;
            $columns = implode('`, `', array_keys($data));
            $values = implode(', ', array_map(
                fn ($value) => $value === null ? 'NULL' : DB::getPdo()->quote((string) $value),
                array_values($data)
            ));
            $sql .= "INSERT INTO `$table` (`$columns`) VALUES ($values);\n";
        }

        File::put($path, $sql);
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
