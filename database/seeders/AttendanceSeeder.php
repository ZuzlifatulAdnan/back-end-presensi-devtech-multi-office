<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ShiftKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::with(['shiftKerja', 'shiftKerjas'])->get();
        $shifts = ShiftKerja::all();

        if ($users->isEmpty() || $shifts->isEmpty()) {
            return;
        }

        Attendance::query()->delete();

        $flowTypes = ['on_time', 'late', 'absent', 'early_leave'];
        $users = $users->take(max(1, intdiv(20, count($flowTypes))));

        $recordIndex = 0;

        foreach ($users as $user) {
            $shift = $user->shiftKerja
                ?? $user->shiftKerjas->first()
                ?? $shifts->first();

            foreach ($flowTypes as $flowType) {
                $date = Carbon::today()->subDays($recordIndex);
                $flowData = $this->generateFlowData($shift, $date, $flowType);

                Attendance::create([
                    'user_id' => $user->id,
                    'shift_id' => $shift->id,
                    'date' => $date->toDateString(),
                    'time_in' => $flowData['time_in']->format('H:i:s'),
                    'time_out' => $flowData['time_out']?->format('H:i:s'),
                    'latlon_in' => fake()->latitude().','.fake()->longitude(),
                    'latlon_out' => $flowData['time_out'] ? fake()->latitude().','.fake()->longitude() : null,
                    'status' => $flowData['status'],
                    'is_weekend' => $date->isWeekend(),
                    'is_holiday' => false,
                    'holiday_work' => false,
                    'late_minutes' => $flowData['late_minutes'],
                    'early_leave_minutes' => $flowData['early_leave_minutes'],
                ]);

                $recordIndex++;
            }
        }
    }

    private function generateFlowData(ShiftKerja $shift, Carbon $date, string $flowType): array
    {
        $shiftStart = $this->buildDateTime($date, $shift->getRawOriginal('start_time'));
        $shiftEnd = $this->buildDateTime($date, $shift->getRawOriginal('end_time'));

        if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
            $shiftEnd->addDay();
        }

        return match ($flowType) {
            'late' => $this->buildLateFlow($shiftStart, $shiftEnd),
            'absent' => $this->buildAbsentFlow($shiftStart),
            'early_leave' => $this->buildEarlyLeaveFlow($shiftStart, $shiftEnd),
            default => $this->buildOnTimeFlow($shiftStart, $shiftEnd),
        };
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
        $checkIn = (clone $shiftStart)->addMinutes(random_int(0, 5));
        $checkOut = (clone $shiftEnd)->subMinutes(random_int(0, 5));

        return [
            'time_in' => $checkIn,
            'time_out' => $checkOut,
            'status' => 'on_time',
            'late_minutes' => 0,
            'early_leave_minutes' => $shiftEnd->diffInMinutes($checkOut),
        ];
    }

    private function buildLateFlow(Carbon $shiftStart, Carbon $shiftEnd): array
    {
        $lateMinutes = random_int(15, 45);
        $checkIn = (clone $shiftStart)->addMinutes($lateMinutes);
        $checkOut = (clone $shiftEnd)->subMinutes(random_int(0, 15));

        return [
            'time_in' => $checkIn,
            'time_out' => $checkOut,
            'status' => 'late',
            'late_minutes' => $lateMinutes,
            'early_leave_minutes' => $shiftEnd->diffInMinutes($checkOut),
        ];
    }

    private function buildAbsentFlow(Carbon $shiftStart): array
    {
        $lateMinutes = random_int(90, 180);
        $checkIn = (clone $shiftStart)->addMinutes($lateMinutes);

        return [
            'time_in' => $checkIn,
            'time_out' => null,
            'status' => 'absent',
            'late_minutes' => $lateMinutes,
            'early_leave_minutes' => 0,
        ];
    }

    private function buildEarlyLeaveFlow(Carbon $shiftStart, Carbon $shiftEnd): array
    {
        $checkIn = (clone $shiftStart)->addMinutes(random_int(0, 10));
        $earlyLeave = random_int(30, 90);
        $checkOut = (clone $shiftEnd)->subMinutes($earlyLeave);

        return [
            'time_in' => $checkIn,
            'time_out' => $checkOut,
            'status' => 'on_time',
            'late_minutes' => 0,
            'early_leave_minutes' => $earlyLeave,
        ];
    }
}
