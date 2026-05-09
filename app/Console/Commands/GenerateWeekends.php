<?php

namespace App\Console\Commands;

use App\Support\WorkdayCalculator;
use Illuminate\Console\Command;

class GenerateWeekends extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:generate-weekends {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate weekend holidays for a given year';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $year = $this->argument('year') ?? now()->year;

        if (! is_numeric($year) || $year < 2020 || $year > 2100) {
            $this->error('Invalid year. Please provide a year between 2020 and 2100.');

            return self::FAILURE;
        }

        $this->info("Generating weekend holidays for year {$year}...");

        $result = WorkdayCalculator::generateWeekendForYear((int) $year);

        $this->newLine();
        $this->info("✓ Inserted: {$result['inserted']} weekend holidays");
        $this->info("⊘ Skipped: {$result['skipped']} existing holidays");
        $this->newLine();
        $this->info('Weekend generation completed successfully!');

        return self::SUCCESS;
    }
}
