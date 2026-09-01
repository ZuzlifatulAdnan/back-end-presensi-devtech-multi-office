<?php

namespace Tests\Unit;

use App\Models\ShiftKerja;
use App\Services\ShiftResolver;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ShiftResolverTest extends TestCase
{
    private ShiftResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new ShiftResolver;
    }

    private function dayShift(int $gracePeriodMinutes = 10): ShiftKerja
    {
        return new ShiftKerja([
            'name' => 'Shift Pagi',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'grace_period_minutes' => $gracePeriodMinutes,
            'is_cross_day' => false,
        ]);
    }

    private function nightShift(): ShiftKerja
    {
        return new ShiftKerja([
            'name' => 'Shift Malam',
            'start_time' => '22:00',
            'end_time' => '06:00',
            'grace_period_minutes' => 10,
            'is_cross_day' => true,
        ]);
    }

    public function test_arriving_within_the_grace_period_is_not_late(): void
    {
        $lateMinutes = $this->resolver->lateMinutes(
            $this->dayShift(),
            Carbon::parse('2026-09-01 08:09:00')
        );

        $this->assertSame(0, $lateMinutes);
    }

    public function test_late_minutes_are_counted_from_the_end_of_the_grace_period(): void
    {
        $lateMinutes = $this->resolver->lateMinutes(
            $this->dayShift(),
            Carbon::parse('2026-09-01 08:40:00')
        );

        $this->assertSame(30, $lateMinutes);
    }

    public function test_early_leave_is_measured_against_the_shift_end(): void
    {
        $earlyMinutes = $this->resolver->earlyLeaveMinutes(
            $this->dayShift(),
            Carbon::parse('2026-09-01 16:15:00'),
            Carbon::parse('2026-09-01')
        );

        $this->assertSame(45, $earlyMinutes);
    }

    public function test_leaving_after_the_shift_end_is_not_early_leave(): void
    {
        $earlyMinutes = $this->resolver->earlyLeaveMinutes(
            $this->dayShift(),
            Carbon::parse('2026-09-01 17:30:00'),
            Carbon::parse('2026-09-01')
        );

        $this->assertSame(0, $earlyMinutes);
    }

    public function test_a_cross_day_shift_ends_on_the_following_day(): void
    {
        $endsAt = $this->resolver->endsAt($this->nightShift(), Carbon::parse('2026-09-01'));

        $this->assertSame('2026-09-02 06:00:00', $endsAt?->format('Y-m-d H:i:s'));
    }

    public function test_checking_in_after_midnight_on_a_cross_day_shift_is_measured_against_the_previous_evening(): void
    {
        $lateMinutes = $this->resolver->lateMinutes(
            $this->nightShift(),
            Carbon::parse('2026-09-02 00:30:00')
        );

        // 22:00 the night before + 10 minutes grace, so 00:30 is 140 minutes late.
        $this->assertSame(140, $lateMinutes);
    }

    public function test_a_shift_without_times_never_reports_lateness(): void
    {
        $shift = new ShiftKerja(['name' => 'Fleksibel', 'grace_period_minutes' => 0]);

        $this->assertSame(0, $this->resolver->lateMinutes($shift, Carbon::parse('2026-09-01 23:00:00')));
        $this->assertNull($this->resolver->endsAt($shift, Carbon::parse('2026-09-01')));
    }
}
