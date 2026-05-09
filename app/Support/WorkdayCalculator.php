<?php

namespace App\Support;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WorkdayCalculator
{
    /**
     * Check if the given date is a weekend (Saturday or Sunday).
     */
    public static function isWeekend(Carbon $date): bool
    {
        return $date->isWeekend();
    }

    /**
     * Check if the given date is a holiday.
     */
    public static function isHoliday(Carbon $date): bool
    {
        return Holiday::where('date', $date->toDateString())->exists();
    }

    /**
     * Check if the given date is a non-working day (weekend or holiday).
     */
    public static function isNonWorkingDay(Carbon $date): bool
    {
        return self::isWeekend($date) || self::isHoliday($date);
    }

    /**
     * Count working days (excluding weekends and holidays) between two dates.
     */
    public static function countWorkdaysExcludingHolidays(Carbon $start, Carbon $end): int
    {
        $workdays = 0;
        $current = $start->copy()->startOfDay();
        $endDate = $end->copy()->startOfDay();

        // Get all holidays in the range for performance
        $holidays = Holiday::whereBetween('date', [$current->toDateString(), $endDate->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->toArray();

        while ($current <= $endDate) {
            if (! $current->isWeekend() && ! in_array($current->toDateString(), $holidays)) {
                $workdays++;
            }
            $current->addDay();
        }

        return $workdays;
    }

    /**
     * Generate weekend holidays for a given year.
     *
     * @return array{inserted: int, skipped: int}
     */
    public static function generateWeekendForYear(int $year): array
    {
        $inserted = 0;
        $skipped = 0;

        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = Carbon::create($year, 12, 31)->endOfDay();

        // Get existing dates to avoid duplicates
        $existingDates = Holiday::whereYear('date', $year)
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->toArray();

        $weekendDates = [];
        $current = $start->copy();

        while ($current <= $end) {
            if ($current->isWeekend()) {
                $dateString = $current->toDateString();

                if (in_array($dateString, $existingDates)) {
                    $skipped++;
                } else {
                    $weekendDates[] = [
                        'date' => $dateString,
                        'name' => 'Weekend',
                        'type' => Holiday::TYPE_WEEKEND,
                        'is_official' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $inserted++;
                }
            }
            $current->addDay();
        }

        if (! empty($weekendDates)) {
            DB::table('holidays')->insert($weekendDates);
        }

        return [
            'inserted' => $inserted,
            'skipped' => $skipped,
        ];
    }
}
