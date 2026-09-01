<?php

namespace App\Services;

use App\Models\ShiftAssignment;
use App\Models\ShiftKerja;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Figures out which shift applies to a user on a given day and turns the shift's
 * "HH:MM" columns into real timestamps, including cross-midnight shifts.
 */
class ShiftResolver
{
    public function resolve(User $user, Carbon $date): ?ShiftKerja
    {
        $scheduledShiftId = ShiftAssignment::query()
            ->forUser($user->id)
            ->forDate($date)
            ->scheduled()
            ->value('shift_id');

        $shiftId = $scheduledShiftId ?? $user->shift_kerja_id;

        if (! $shiftId) {
            return null;
        }

        return ShiftKerja::query()->find($shiftId);
    }

    public function startsAt(ShiftKerja $shift, Carbon $date): ?Carbon
    {
        return $this->timeOn($date, $shift->getRawOriginal('start_time') ?? $shift->start_time);
    }

    /**
     * End of the shift, pushed to the next day when the shift crosses midnight.
     */
    public function endsAt(ShiftKerja $shift, Carbon $date): ?Carbon
    {
        $start = $this->startsAt($shift, $date);
        $end = $this->timeOn($date, $shift->getRawOriginal('end_time') ?? $shift->end_time);

        if ($end === null) {
            return null;
        }

        if ($start !== null && $end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return $end;
    }

    /**
     * Minutes the user is late, after applying the shift's grace period.
     */
    public function lateMinutes(ShiftKerja $shift, Carbon $checkInAt): int
    {
        $start = $this->startsAt($shift, $checkInAt->copy()->startOfDay());

        if ($start === null) {
            return 0;
        }

        if ($shift->is_cross_day && $checkInAt->lessThan($start)) {
            $start->subDay();
        }

        $threshold = $start->copy()->addMinutes((int) ($shift->grace_period_minutes ?? 0));

        return $checkInAt->greaterThan($threshold)
            ? (int) $threshold->diffInMinutes($checkInAt)
            : 0;
    }

    /**
     * Minutes the user left before the shift ended.
     */
    public function earlyLeaveMinutes(ShiftKerja $shift, Carbon $checkOutAt, Carbon $attendanceDate): int
    {
        $end = $this->endsAt($shift, $attendanceDate->copy()->startOfDay());

        if ($end === null) {
            return 0;
        }

        return $checkOutAt->lessThan($end)
            ? (int) $checkOutAt->diffInMinutes($end)
            : 0;
    }

    private function timeOn(Carbon $date, ?string $time): ?Carbon
    {
        if (blank($time)) {
            return null;
        }

        $time = substr($time, 0, 8);
        $normalized = strlen($time) === 5 ? $time.':00' : $time;

        try {
            return Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $date->toDateString().' '.$normalized,
                config('app.timezone')
            );
        } catch (\Throwable) {
            return null;
        }
    }
}
