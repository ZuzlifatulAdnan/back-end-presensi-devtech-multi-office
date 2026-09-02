<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Reconciles the `migrations` table after the migration files were squashed.
 *
 * A database that already has the full schema must not run the squashed
 * migrations again — they would fail with "table already exists". This command
 * rewrites the bookkeeping rows so `php artisan migrate` becomes a no-op, and
 * never touches the schema itself.
 */
class BaselineMigrations extends Command
{
    use ConfirmableTrait;

    protected $signature = 'db:baseline-migrations
                            {--dry-run : Show what would change without writing anything}
                            {--force : Run without asking for confirmation in production}';

    protected $description = 'Sync the migrations table with the squashed migration files (schema is never modified)';

    /**
     * Migrations named before this are the squashed baseline: a database that
     * already carries the schema has effectively run them. Anything named after
     * it is a normal pending migration and is left for `php artisan migrate`.
     */
    private const BASELINE_CUTOFF = '2026_09_02_000000';

    /**
     * Last commit that still carried the 46 incremental migrations, used to
     * bring an older database up to the baseline before it can be synced.
     */
    private const PRE_SQUASH_COMMIT = 'f68d62b';

    public function handle(): int
    {
        if (! Schema::hasTable('migrations')) {
            $this->components->error('Tabel `migrations` tidak ada. Jalankan `php artisan migrate` seperti biasa.');

            return self::FAILURE;
        }

        if (! Schema::hasTable('users')) {
            $this->components->error('Database ini masih kosong. Jalankan `php artisan migrate` seperti biasa, bukan perintah ini.');

            return self::FAILURE;
        }

        $files = $this->migrationFilenames();

        if ($files === []) {
            $this->components->error('Tidak ada file migrasi di database/migrations.');

            return self::FAILURE;
        }

        if (($missingSchema = $this->missingSchema()) !== []) {
            $this->components->error('Database ini BELUM sampai versi baseline, jadi tidak boleh ditandai sudah dimigrasi.');
            $this->newLine();
            $this->components->twoColumnDetail('<fg=red>Belum ada di database</>', count($missingSchema).' objek');
            foreach ($missingSchema as $item) {
                $this->line('  ! '.$item);
            }
            $this->newLine();
            $this->components->warn('Bawa dulu database ini ke versi baseline memakai kode versi lama, baru ulangi perintah ini:');
            $this->line('  git stash -u                  # simpan perubahan termasuk file baru');
            $this->line('  git checkout '.self::PRE_SQUASH_COMMIT.'         # kode sebelum migrasi di-squash');
            $this->line('  php artisan migrate           # jalankan migrasi lama sampai tuntas');
            $this->line('  git checkout -                # kembali ke branch semula');
            $this->line('  git stash pop');
            $this->line('  php artisan db:baseline-migrations');
            $this->line('  php artisan migrate');
            $this->newLine();
            $this->components->warn('Alternatif: pulihkan database dari backup yang sudah sampai versi baseline.');

            return self::FAILURE;
        }

        $recorded = DB::table('migrations')->pluck('migration')->all();

        // Only the squashed baseline is assumed to be applied already. Anything
        // dated after the cutoff is a real migration that `migrate` must run.
        $baseline = array_values(array_filter(
            $files,
            fn (string $name): bool => $name < self::BASELINE_CUTOFF
        ));

        $stale = array_values(array_diff($recorded, $files));
        $missing = array_values(array_diff($baseline, $recorded));
        $pending = array_values(array_diff($files, $baseline, $recorded));

        $this->components->info(sprintf(
            'Tercatat di database: %d | File baseline: %d | Menunggu `migrate`: %d',
            count($recorded),
            count($baseline),
            count($pending)
        ));

        if ($stale === [] && $missing === []) {
            $this->components->info('Sudah sinkron, tidak ada yang perlu diubah.');
            $this->reportPending($pending);

            return self::SUCCESS;
        }

        if ($stale !== []) {
            $this->newLine();
            $this->components->twoColumnDetail('<fg=yellow>Dihapus dari tabel migrations</>', count($stale).' baris');
            foreach ($stale as $name) {
                $this->line('  - '.$name);
            }
        }

        if ($missing !== []) {
            $this->newLine();
            $this->components->twoColumnDetail('<fg=green>Ditambahkan ke tabel migrations</>', count($missing).' baris');
            foreach ($missing as $name) {
                $this->line('  + '.$name);
            }
        }

        $this->newLine();

        if ($this->option('dry-run')) {
            $this->components->warn('Dry run: tidak ada perubahan yang ditulis.');

            return self::SUCCESS;
        }

        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $batch = 1;

        DB::transaction(function () use ($stale, $missing, $batch): void {
            if ($stale !== []) {
                DB::table('migrations')->whereIn('migration', $stale)->delete();
            }

            if ($missing !== []) {
                DB::table('migrations')->insert(array_map(
                    fn (string $name): array => ['migration' => $name, 'batch' => $batch],
                    $missing
                ));
            }
        });

        $this->components->info('Tabel migrations disinkronkan.');
        $this->reportPending($pending);

        return self::SUCCESS;
    }

    /**
     * @param  array<int, string>  $pending
     */
    private function reportPending(array $pending): void
    {
        if ($pending === []) {
            $this->components->info('Tidak ada migrasi yang tertunda; `php artisan migrate` tidak akan menjalankan apa pun.');

            return;
        }

        $this->newLine();
        $this->components->twoColumnDetail('<fg=cyan>Menunggu dijalankan `php artisan migrate`</>', count($pending).' migrasi');
        foreach ($pending as $name) {
            $this->line('  * '.$name);
        }
        $this->newLine();
        $this->components->warn('Langkah berikutnya: php artisan migrate');
    }

    /**
     * Tables and columns the baseline migrations are expected to have created.
     *
     * Marking a migration as run without its schema being present would leave
     * the database permanently missing those objects, so anything absent here
     * means this database is older than the baseline.
     *
     * @var array<string, array<int, string>>
     */
    private const BASELINE_SIGNATURE = [
        'users' => ['work_mode', 'company_id', 'password_changed_at', 'shift_kerja_id'],
        'companies' => ['is_active', 'logo_path', 'attendance_type'],
        'attendances' => ['work_mode', 'photo_in', 'notes_in', 'distance_in_meters', 'company_id'],
        'leaves' => ['attachment_url', 'attachment_name', 'cancelled_at'],
        'leave_balances' => ['remaining_days'],
        'shift_assignments' => ['shift_id'],
        'holidays' => ['type'],
        'app_settings' => ['app_name', 'wfh_enabled', 'map_default_zoom'],
        'overtimes' => ['approved_by'],
        'notes' => ['note'],
        'personal_access_tokens' => ['token'],
    ];

    /**
     * @return array<int, string>
     */
    private function missingSchema(): array
    {
        $missing = [];

        foreach (self::BASELINE_SIGNATURE as $table => $columns) {
            if (! Schema::hasTable($table)) {
                $missing[] = "tabel `$table`";

                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    $missing[] = "kolom `$table`.`$column`";
                }
            }
        }

        return $missing;
    }

    /**
     * Migration class names as Laravel records them: the filename without `.php`.
     *
     * @return array<int, string>
     */
    private function migrationFilenames(): array
    {
        return collect(File::glob(database_path('migrations').'/*_*.php'))
            ->map(fn (string $path): string => basename($path, '.php'))
            ->sort()
            ->values()
            ->all();
    }
}
