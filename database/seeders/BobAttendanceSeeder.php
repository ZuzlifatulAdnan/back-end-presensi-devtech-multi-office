<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ShiftKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BobAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bob = User::where('email', 'bob@company.com')->first();

        if (! $bob) {
            $this->command->warn('User bob@company.com not found');

            return;
        }

        $shift = $bob->shiftKerja ?? $bob->shiftKerjas->first() ?? ShiftKerja::first();

        if (! $shift) {
            $this->command->warn('No shift found for Bob');

            return;
        }

        // Delete existing attendances for Bob
        Attendance::where('user_id', $bob->id)->delete();

        // Real coordinates in Jakarta area (SCBD/Sudirman area with slight variations)
        $realCoordinates = [
            '-6.2253,106.8075', // SCBD area
            '-6.2251,106.8078', // Nearby office building
            '-6.2255,106.8072', // Pacific Place area
            '-6.2249,106.8080', // FX Sudirman area
            '-6.2256,106.8070', // Plaza Indonesia area
            '-6.2248,106.8082', // Senayan City area
            '-6.2257,106.8068', // Grand Indonesia area
            '-6.2250,106.8076', // Wisma 46 area
            '-6.2254,106.8074', // Equity Tower area
            '-6.2252,106.8077', // Energy Building area
        ];

        // Get 10 weekdays (Monday-Friday) starting from recent dates
        $workDates = [];
        $currentDate = Carbon::now()->startOfDay();

        while (count($workDates) < 10) {
            // Go backwards to get recent dates
            $currentDate->subDay();

            // Skip weekends
            if (! $currentDate->isWeekend()) {
                $workDates[] = $currentDate->copy();
            }
        }

        // Reverse to get chronological order (oldest to newest)
        $workDates = array_reverse($workDates);

        foreach ($workDates as $index => $date) {
            $shiftStart = $this->buildDateTime($date, $shift->getRawOriginal('start_time'));
            $shiftEnd = $this->buildDateTime($date, $shift->getRawOriginal('end_time'));

            if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
                $shiftEnd->addDay();
            }

            // Randomize attendance patterns for realistic data
            $patterns = ['on_time', 'on_time', 'on_time', 'slightly_late', 'slightly_late', 'early', 'very_on_time'];
            $pattern = $patterns[array_rand($patterns)];

            $flowData = match ($pattern) {
                'slightly_late' => $this->buildSlightlyLateFlow($shiftStart, $shiftEnd),
                'early' => $this->buildEarlyFlow($shiftStart, $shiftEnd),
                'very_on_time' => $this->buildVeryOnTimeFlow($shiftStart, $shiftEnd),
                default => $this->buildOnTimeFlow($shiftStart, $shiftEnd),
            };

            Attendance::create([
                'user_id' => $bob->id,
                'shift_id' => $shift->id,
                'date' => $date->toDateString(),
                'time_in' => $flowData['time_in']->format('H:i:s'),
                'time_out' => $flowData['time_out']?->format('H:i:s'),
                'latlon_in' => $realCoordinates[$index],
                'latlon_out' => $flowData['time_out'] ? $realCoordinates[$index] : null,
                'status' => $flowData['status'],
                'is_weekend' => false,
                'is_holiday' => false,
                'holiday_work' => false,
                'late_minutes' => $flowData['late_minutes'],
                'early_leave_minutes' => $flowData['early_leave_minutes'],
            ]);
        }

        $this->command->info('Created 10 attendance records for Bob with real Jakarta coordinates');
    }

    private function buildDateTime(Carbon $date, string $time): Carbon
    {
        $timeWithSeconds = strlen($time) === 5 ? $time.':00' : $time;

        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $date->format('Y-m-d').' '.$timeWithSeconds,
            config('app.timezone')
        );
    }

    private function buildOnTimeFlow(Carbon $shiftStart, Carbon $shiftEnd): array
    {
        $checkIn = (clone $shiftStart)->addMinutes(random_int(0, 10));
        $checkOut = (clone $shiftEnd)->addMinutes(random_int(0, 15));

        return [
            'time_in' => $checkIn,
            'time_out' => $checkOut,
            'status' => 'on_time',
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
        ];
    }

    private function buildSlightlyLateFlow(Carbon $shiftStart, Carbon $shiftEnd): array
    {
        $lateMinutes = random_int(5, 20);
        $checkIn = (clone $shiftStart)->addMinutes($lateMinutes);
        $checkOut = (clone $shiftEnd)->addMinutes(random_int(0, 10));

        return [
            'time_in' => $checkIn,
            'time_out' => $checkOut,
            'status' => 'late',
            'late_minutes' => $lateMinutes,
            'early_leave_minutes' => 0,
        ];
    }

    private function buildEarlyFlow(Carbon $shiftStart, Carbon $shiftEnd): array
    {
        $checkIn = (clone $shiftStart)->subMinutes(random_int(5, 15));
        $checkOut = (clone $shiftEnd)->addMinutes(random_int(5, 20));

        return [
            'time_in' => $checkIn,
            'time_out' => $checkOut,
            'status' => 'on_time',
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
        ];
    }

    private function buildVeryOnTimeFlow(Carbon $shiftStart, Carbon $shiftEnd): array
    {
        $checkIn = (clone $shiftStart)->addMinutes(random_int(-2, 2));
        $checkOut = (clone $shiftEnd)->addMinutes(random_int(0, 5));

        return [
            'time_in' => $checkIn,
            'time_out' => $checkOut,
            'status' => 'on_time',
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
        ];
    }
}
