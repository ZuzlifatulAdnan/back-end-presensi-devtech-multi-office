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

        $recorded = DB::table('migrations')->pluck('migration')->all();

        $stale = array_values(array_diff($recorded, $files));
        $missing = array_values(array_diff($files, $recorded));

        $this->components->info(sprintf(
            'Tercatat di database: %d | File migrasi: %d',
            count($recorded),
            count($files)
        ));

        if ($stale === [] && $missing === []) {
            $this->components->info('Sudah sinkron, tidak ada yang perlu diubah.');

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

        $this->components->info('Tabel migrations disinkronkan. `php artisan migrate` sekarang tidak akan menjalankan apa pun.');

        return self::SUCCESS;
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
