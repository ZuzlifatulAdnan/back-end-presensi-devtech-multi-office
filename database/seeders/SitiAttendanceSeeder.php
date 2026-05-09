<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ShiftKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SitiAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siti = User::where('email', 'siti@company.com')->first();

        if (! $siti) {
            $this->command->warn('User siti@company.com not found');

            return;
        }

        $shift = $siti->shiftKerja ?? $siti->shiftKerjas->first() ?? ShiftKerja::first();

        if (! $shift) {
            $this->command->warn('No shift found for Siti');

            return;
        }

        // Delete existing attendances for Siti
        Attendance::where('user_id', $siti->id)->delete();

        // Real coordinates in Bandung area (office areas: Dago, Pasteur, Sukajadi, etc.)
        $bandungCoordinates = [
            '-6.8990,107.6104', // Gedung Sate area
            '-6.8995,107.6102', // Dago area
            '-6.9005,107.6100', // Cikapayang area
            '-6.8985,107.6108', // Aceh Street area
            '-6.9012,107.6095', // Dipatiukur area
            '-6.8978,107.6115', // Asia Afrika area
            '-6.9020,107.6088', // Setiabudi area
            '-6.8972,107.6122', // Braga area
            '-6.9028,107.6080', // Pasteur area
            '-6.8965,107.6130', // Alun-alun area
            '-6.9035,107.6072', // Sukajadi area
            '-6.8958,107.6138', // Kebon Kawung area
            '-6.9042,107.6065', // Surya Sumantri area
            '-6.8950,107.6145', // Cicendo area
            '-6.9048,107.6058', // Cihampelas area
            '-6.8943,107.6152', // Pasir Kaliki area
            '-6.9055,107.6050', // Dr. Djunjunan area
            '-6.8936,107.6160', // Kopo area
            '-6.9062,107.6042', // Geger Kalong area
            '-6.8929,107.6168', // Buah Batu area
        ];

        // Get 20 weekdays (Monday-Friday) starting from recent dates, excluding holidays
        $workDates = [];
        $currentDate = Carbon::now()->startOfDay();

        // Get all holidays from database for 2025
        $holidays = \App\Models\Holiday::whereYear('date', Carbon::now()->year)
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        while (count($workDates) < 20) {
            // Go backwards to get recent dates
            $currentDate->subDay();

            // Skip weekends and holidays
            $isWeekend = $currentDate->isWeekend();
            $isHoliday = in_array($currentDate->format('Y-m-d'), $holidays);

            if (! $isWeekend && ! $isHoliday) {
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

            // Create realistic attendance patterns with variety
            $random = random_int(1, 100);

            if ($random <= 60) {
                // 60% on time
                $flowData = $this->buildOnTimeFlow($shiftStart, $shiftEnd);
            } elseif ($random <= 80) {
                // 20% slightly late
                $flowData = $this->buildSlightlyLateFlow($shiftStart, $shiftEnd);
            } elseif ($random <= 90) {
                // 10% early arrival
                $flowData = $this->buildEarlyFlow($shiftStart, $shiftEnd);
            } elseif ($random <= 95) {
                // 5% very punctual
                $flowData = $this->buildVeryOnTimeFlow($shiftStart, $shiftEnd);
            } else {
                // 5% late
                $flowData = $this->buildLateFlow($shiftStart, $shiftEnd);
            }

            Attendance::create([
                'user_id' => $siti->id,
                'shift_id' => $shift->id,
                'date' => $date->toDateString(),
                'time_in' => $flowData['time_in']->format('H:i:s'),
                'time_out' => $flowData['time_out']?->format('H:i:s'),
                'latlon_in' => $bandungCoordinates[$index],
                'latlon_out' => $flowData['time_out'] ? $bandungCoordinates[$index] : null,
                'status' => $flowData['status'],
                'is_weekend' => false,
                'is_holiday' => false,
                'holiday_work' => false,
                'late_minutes' => $flowData['late_minutes'],
                'early_leave_minutes' => $flowData['early_leave_minutes'],
            ]);
        }

        $this->command->info('Created 20 attendance records for Siti with real Bandung coordinates');
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
        $lateMinutes = random_int(5, 15);
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

    private function buildLateFlow(Carbon $shiftStart, Carbon $shiftEnd): array
    {
        $lateMinutes = random_int(20, 45);
        $checkIn = (clone $shiftStart)->addMinutes($lateMinutes);
        $checkOut = (clone $shiftEnd)->subMinutes(random_int(0, 10));

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
