<?php

namespace App\Services;

use App\Exceptions\AttendanceException;
use App\Models\AppSetting;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\ShiftKerja;
use App\Models\User;
use App\Support\WorkdayCalculator;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AttendanceService
{
    public function __construct(
        private readonly GeofenceService $geofence,
        private readonly ShiftResolver $shiftResolver,
    ) {}

    /**
     * Work modes the user is permitted to use. A user assigned to WFH may still
     * come to the office, but a WFO-only user can never self-declare remote.
     *
     * @return array<int, string>
     */
    public function allowedWorkModes(User $user): array
    {
        $settings = AppSetting::current();

        $modes = match ($user->work_mode) {
            Attendance::MODE_WFA => [Attendance::MODE_WFO, Attendance::MODE_WFH, Attendance::MODE_WFA],
            Attendance::MODE_WFH => [Attendance::MODE_WFO, Attendance::MODE_WFH],
            default => [Attendance::MODE_WFO],
        };

        if (! $settings->wfh_enabled) {
            $modes = [Attendance::MODE_WFO];
        }

        return $modes;
    }

    /**
     * Everything the app needs to render the map screen before the user presses
     * "Presensi": office pins, live distance, shift window and server clock.
     *
     * @return array<string, mixed>
     */
    public function preCheck(User $user, ?float $latitude, ?float $longitude): array
    {
        $settings = AppSetting::current();
        $now = now();
        $today = $now->copy()->startOfDay();

        $attendance = $this->findTodayAttendance($user);
        $shift = $this->shiftResolver->resolve($user, $today);
        $allowedModes = $this->allowedWorkModes($user);
        $defaultMode = in_array($user->work_mode, $allowedModes, true)
            ? $user->work_mode
            : Attendance::MODE_WFO;

        $hasPosition = $latitude !== null && $longitude !== null;

        $geo = $hasPosition
            ? $this->geofence->evaluate($user, $latitude, $longitude)
            : [
                'nearest' => null,
                'distance_meters' => null,
                'within_radius' => false,
                'matched' => null,
                'locations' => $this->geofence->companiesFor($user)
                    ->map(fn ($company): array => [
                        'id' => $company->id,
                        'name' => $company->name,
                        'address' => $company->address,
                        'latitude' => (float) $company->latitude,
                        'longitude' => (float) $company->longitude,
                        'radius_meters' => $company->radiusInMeters(),
                        'distance_meters' => null,
                        'within_radius' => false,
                    ])->all(),
            ];

        $activeLeave = $this->approvedLeaveOn($user, $today);
        $needsLocation = $defaultMode === Attendance::MODE_WFO;

        $blockers = [];

        if ($settings->maintenance_mode) {
            $blockers[] = $settings->maintenance_message ?: 'Aplikasi sedang dalam pemeliharaan.';
        }

        if ($activeLeave) {
            $blockers[] = 'Anda sedang dalam masa '.($activeLeave->leaveType->name ?? 'cuti').' yang telah disetujui.';
        }

        if ($attendance && $attendance->isCheckedOut()) {
            $blockers[] = 'Presensi hari ini sudah lengkap.';
        }

        if ($needsLocation && ! $hasPosition) {
            $blockers[] = 'Lokasi GPS belum terdeteksi.';
        } elseif ($needsLocation && ! $geo['within_radius']) {
            $blockers[] = 'Anda berada di luar radius kantor.';
        }

        return [
            'server_time' => $now->toIso8601String(),
            'date' => $today->toDateString(),
            'is_weekend' => WorkdayCalculator::isWeekend($today->copy()),
            'is_holiday' => WorkdayCalculator::isHoliday($today->copy()),
            'next_action' => match (true) {
                $attendance === null => 'check_in',
                ! $attendance->isCheckedOut() => 'check_out',
                default => 'done',
            },
            'can_check_in' => $attendance === null && $blockers === [],
            'can_check_out' => $attendance !== null && ! $attendance->isCheckedOut() && ! $settings->maintenance_mode,
            'blockers' => array_values(array_unique($blockers)),
            'work_mode' => [
                'default' => $defaultMode,
                'allowed' => $allowedModes,
                'requires_location' => $needsLocation,
                'location_ready' => ! $needsLocation || $geo['within_radius'],
            ],
            'requirements' => [
                'photo_required' => (bool) $settings->attendance_photo_required,
                'remote_photo_required' => (bool) $settings->wfh_photo_required,
                'remote_notes_required' => (bool) $settings->wfh_notes_required,
                'block_mock_location' => (bool) $settings->block_mock_location,
                'accuracy_tolerance_meters' => (int) $settings->location_accuracy_tolerance_meters,
            ],
            'map' => [
                'default_zoom' => (int) $settings->map_default_zoom,
                'tile_url' => $settings->map_tile_url ?: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                'attribution' => $settings->map_attribution ?: 'OpenStreetMap contributors',
                'user_position' => $hasPosition
                    ? ['latitude' => $latitude, 'longitude' => $longitude]
                    : null,
            ],
            'nearest_location' => $geo['nearest'] ? [
                'id' => $geo['nearest']->id,
                'name' => $geo['nearest']->name,
                'address' => $geo['nearest']->address,
                'latitude' => (float) $geo['nearest']->latitude,
                'longitude' => (float) $geo['nearest']->longitude,
                'radius_meters' => $geo['nearest']->radiusInMeters(),
                'distance_meters' => $geo['distance_meters'],
                'within_radius' => $geo['within_radius'],
            ] : null,
            'locations' => $geo['locations'],
            'shift' => $shift ? [
                'id' => $shift->id,
                'name' => $shift->name,
                'start_time' => $this->formatTime($shift->getRawOriginal('start_time') ?? $shift->start_time),
                'end_time' => $this->formatTime($shift->getRawOriginal('end_time') ?? $shift->end_time),
                'grace_period_minutes' => (int) ($shift->grace_period_minutes ?? 0),
                'is_cross_day' => (bool) $shift->is_cross_day,
                'starts_at' => $this->shiftResolver->startsAt($shift, $today)?->toIso8601String(),
                'ends_at' => $this->shiftResolver->endsAt($shift, $today)?->toIso8601String(),
            ] : null,
            'attendance' => $attendance,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AttendanceException
     */
    public function checkIn(User $user, array $data): Attendance
    {
        $settings = AppSetting::current();
        $this->assertNotInMaintenance($settings);

        $now = now();
        $today = $now->copy()->startOfDay();

        $workMode = $data['work_mode'] ?? $user->work_mode ?? Attendance::MODE_WFO;
        $allowedModes = $this->allowedWorkModes($user);

        if (! in_array($workMode, $allowedModes, true)) {
            throw new AttendanceException(
                'Mode kerja '.strtoupper((string) $workMode).' tidak diizinkan untuk akun Anda.',
                403,
                ['allowed_work_modes' => $allowedModes]
            );
        }

        $isRemote = in_array($workMode, [Attendance::MODE_WFH, Attendance::MODE_WFA], true);

        if ($settings->block_mock_location && ($data['is_mock_location'] ?? false)) {
            throw new AttendanceException('Lokasi palsu (fake GPS) terdeteksi. Matikan aplikasi lokasi palsu lalu coba lagi.', 422);
        }

        if (Attendance::query()->forUser($user->id)->onDate($today)->exists()) {
            throw new AttendanceException('Anda sudah melakukan absen masuk hari ini.', 409);
        }

        if ($leave = $this->approvedLeaveOn($user, $today)) {
            throw new AttendanceException(
                'Anda sedang dalam masa '.($leave->leaveType->name ?? 'cuti').' yang telah disetujui pada tanggal ini.',
                409
            );
        }

        $latitude = (float) $data['latitude'];
        $longitude = (float) $data['longitude'];

        $geo = $this->geofence->evaluate($user, $latitude, $longitude);
        $companyId = $user->company_id;
        $distance = $geo['distance_meters'];

        if (! $isRemote) {
            if ($this->geofence->companiesFor($user)->isEmpty()) {
                throw new AttendanceException('Belum ada lokasi kantor aktif yang dikonfigurasi. Hubungi admin.', 422);
            }

            if (! $geo['within_radius']) {
                throw new AttendanceException(
                    'Anda berada di luar radius kantor.',
                    422,
                    [
                        'distance_meters' => $geo['distance_meters'],
                        'nearest_location' => $geo['nearest']?->name,
                        'radius_meters' => $geo['nearest']?->radiusInMeters(),
                    ]
                );
            }

            $companyId = $geo['matched']->id;
        }

        $this->assertProofProvided($settings, $isRemote, $data, 'masuk');

        $shift = $this->shiftResolver->resolve($user, $today);
        $lateMinutes = $shift ? $this->shiftResolver->lateMinutes($shift, $now) : 0;
        $isWeekend = WorkdayCalculator::isWeekend($today->copy());
        $isHoliday = WorkdayCalculator::isHoliday($today->copy());

        $photoPath = ($data['photo'] ?? null) instanceof UploadedFile
            ? $this->storePhoto($data['photo'], $user, 'in')
            : null;

        try {
            return Attendance::create([
                'user_id' => $user->id,
                'shift_id' => $shift?->id,
                'company_id' => $companyId,
                'date' => $today->toDateString(),
                'time_in' => $now->toTimeString(),
                'latlon_in' => $latitude.','.$longitude,
                'photo_in' => $photoPath,
                'notes_in' => $data['notes'] ?? null,
                'address_in' => $data['address'] ?? null,
                'distance_in_meters' => $distance === null ? null : (int) round($distance),
                'is_mock_location' => (bool) ($data['is_mock_location'] ?? false),
                'device_info' => $data['device_info'] ?? null,
                'status' => $lateMinutes > 0 ? Attendance::STATUS_LATE : Attendance::STATUS_ON_TIME,
                'work_mode' => $workMode,
                'is_weekend' => $isWeekend,
                'is_holiday' => $isHoliday,
                'holiday_work' => $shift !== null && ($isWeekend || $isHoliday),
                'late_minutes' => $lateMinutes,
            ]);
        } catch (QueryException $exception) {
            if ($photoPath !== null) {
                Storage::disk('public')->delete($photoPath);
            }

            if ($this->isUniqueViolation($exception)) {
                throw new AttendanceException('Anda sudah melakukan absen masuk hari ini.', 409);
            }

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AttendanceException
     */
    public function checkOut(User $user, array $data): Attendance
    {
        $settings = AppSetting::current();
        $this->assertNotInMaintenance($settings);

        $now = now();
        $attendance = $this->findOpenAttendance($user);

        if (! $attendance) {
            if ($this->findTodayAttendance($user)?->isCheckedOut()) {
                throw new AttendanceException('Anda sudah melakukan absen pulang hari ini.', 409);
            }

            throw new AttendanceException('Anda belum melakukan absen masuk.', 409);
        }

        if ($settings->block_mock_location && ($data['is_mock_location'] ?? false)) {
            throw new AttendanceException('Lokasi palsu (fake GPS) terdeteksi. Matikan aplikasi lokasi palsu lalu coba lagi.', 422);
        }

        $isRemote = $attendance->isRemote();
        $latitude = (float) $data['latitude'];
        $longitude = (float) $data['longitude'];

        $geo = $this->geofence->evaluate($user, $latitude, $longitude);

        if (! $isRemote && ! $geo['within_radius'] && $this->geofence->companiesFor($user)->isNotEmpty()) {
            throw new AttendanceException(
                'Anda berada di luar radius kantor untuk absen pulang.',
                422,
                [
                    'distance_meters' => $geo['distance_meters'],
                    'nearest_location' => $geo['nearest']?->name,
                ]
            );
        }

        $this->assertProofProvided($settings, $isRemote, $data, 'pulang', (bool) $settings->attendance_photo_required);

        $photoPath = ($data['photo'] ?? null) instanceof UploadedFile
            ? $this->storePhoto($data['photo'], $user, 'out')
            : null;

        $shift = $attendance->shift_id ? ShiftKerja::query()->find($attendance->shift_id) : null;
        $attendanceDate = Carbon::parse(
            $attendance->date instanceof Carbon ? $attendance->date->toDateString() : (string) $attendance->date
        );

        $attendance->fill([
            'time_out' => $now->toTimeString(),
            'latlon_out' => $latitude.','.$longitude,
            'photo_out' => $photoPath ?? $attendance->photo_out,
            'notes_out' => $data['notes'] ?? $attendance->notes_out,
            'address_out' => $data['address'] ?? $attendance->address_out,
            'distance_out_meters' => $geo['distance_meters'] === null ? null : (int) round($geo['distance_meters']),
            'early_leave_minutes' => $shift ? $this->shiftResolver->earlyLeaveMinutes($shift, $now, $attendanceDate) : 0,
            'work_duration_minutes' => $this->durationMinutes($attendanceDate, $attendance->time_in, $now->toTimeString()),
        ]);

        $attendance->save();

        return $attendance;
    }

    public function findTodayAttendance(User $user): ?Attendance
    {
        return Attendance::query()
            ->with(['shift', 'company'])
            ->forUser($user->id)
            ->onDate(now())
            ->first();
    }

    /**
     * Today's open record, or yesterday's when the user is on a cross-day shift
     * and has not checked out yet.
     */
    public function findOpenAttendance(User $user): ?Attendance
    {
        $today = $this->findTodayAttendance($user);

        if ($today) {
            return $today->isCheckedOut() ? null : $today;
        }

        return Attendance::query()
            ->with(['shift', 'company'])
            ->forUser($user->id)
            ->onDate(now()->subDay())
            ->whereNull('time_out')
            ->whereHas('shift', fn ($query) => $query->where('is_cross_day', true))
            ->first();
    }

    /**
     * Monthly recap used by the app's dashboard.
     *
     * @return array<string, mixed>
     */
    public function monthlySummary(User $user, int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $row = Attendance::query()
            ->forUser($user->id)
            ->betweenDates($start->toDateString(), $end->toDateString())
            ->selectRaw('COUNT(*) as total_present')
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as on_time_count', [Attendance::STATUS_ON_TIME])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as late_count', [Attendance::STATUS_LATE])
            ->selectRaw('COALESCE(SUM(CASE WHEN work_mode = ? THEN 1 ELSE 0 END), 0) as wfo_count', [Attendance::MODE_WFO])
            ->selectRaw('COALESCE(SUM(CASE WHEN work_mode = ? THEN 1 ELSE 0 END), 0) as wfh_count', [Attendance::MODE_WFH])
            ->selectRaw('COALESCE(SUM(CASE WHEN work_mode = ? THEN 1 ELSE 0 END), 0) as wfa_count', [Attendance::MODE_WFA])
            ->selectRaw('COALESCE(SUM(late_minutes), 0) as late_minutes')
            ->selectRaw('COALESCE(SUM(work_duration_minutes), 0) as work_minutes')
            ->first();

        $approvedLeaveDays = (int) Leave::query()
            ->forEmployee($user->id)
            ->status(Leave::STATUS_APPROVED)
            ->overlapping($start->toDateString(), $end->toDateString())
            ->sum('total_days');

        return [
            'year' => $year,
            'month' => $month,
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'total_present' => (int) ($row->total_present ?? 0),
            'on_time' => (int) ($row->on_time_count ?? 0),
            'late' => (int) ($row->late_count ?? 0),
            'late_minutes' => (int) ($row->late_minutes ?? 0),
            'work_minutes' => (int) ($row->work_minutes ?? 0),
            'by_work_mode' => [
                'wfo' => (int) ($row->wfo_count ?? 0),
                'wfh' => (int) ($row->wfh_count ?? 0),
                'wfa' => (int) ($row->wfa_count ?? 0),
            ],
            'approved_leave_days' => $approvedLeaveDays,
        ];
    }

    private function approvedLeaveOn(User $user, Carbon $date): ?Leave
    {
        return Leave::query()
            ->with('leaveType')
            ->forEmployee($user->id)
            ->status(Leave::STATUS_APPROVED)
            ->overlapping($date->toDateString(), $date->toDateString())
            ->first();
    }

    private function assertNotInMaintenance(AppSetting $settings): void
    {
        if ($settings->maintenance_mode) {
            throw new AttendanceException(
                $settings->maintenance_message ?: 'Aplikasi sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.',
                503
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertProofProvided(AppSetting $settings, bool $isRemote, array $data, string $label, ?bool $requirePhoto = null): void
    {
        $photoRequired = $requirePhoto
            ?? ($settings->attendance_photo_required || ($isRemote && $settings->wfh_photo_required));

        if ($photoRequired && ! (($data['photo'] ?? null) instanceof UploadedFile)) {
            throw new AttendanceException('Foto bukti absen '.$label.' wajib dilampirkan.', 422);
        }

        if ($label === 'masuk' && $isRemote && $settings->wfh_notes_required && blank($data['notes'] ?? null)) {
            throw new AttendanceException('Catatan aktivitas wajib diisi untuk presensi WFH/WFA.', 422);
        }
    }

    private function storePhoto(UploadedFile $file, User $user, string $suffix): string
    {
        $name = sprintf('%d-%s-%s.%s', $user->id, $suffix, now()->format('YmdHis'), $file->extension() ?: 'jpg');

        return $file->storeAs('attendances/'.now()->format('Y/m'), $name, 'public');
    }

    private function durationMinutes(Carbon $date, ?string $timeIn, string $timeOut): ?int
    {
        if (blank($timeIn)) {
            return null;
        }

        $in = Carbon::parse($date->toDateString().' '.$timeIn);
        $out = Carbon::parse($date->toDateString().' '.$timeOut);

        if ($out->lessThan($in)) {
            $out->addDay();
        }

        return (int) $in->diffInMinutes($out);
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        return (string) ($exception->errorInfo[1] ?? '') === '1062'
            || (string) $exception->getCode() === '23000';
    }

    private function formatTime(?string $time): ?string
    {
        return blank($time) ? null : substr($time, 0, 5);
    }
}
