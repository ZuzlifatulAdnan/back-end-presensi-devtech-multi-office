<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Attendance
 */
class AttendanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'date' => $this->date?->toDateString(),
            'time_in' => $this->formatTime($this->time_in),
            'time_out' => $this->formatTime($this->time_out),
            'status' => $this->status,
            'status_label' => match ($this->status) {
                Attendance::STATUS_ON_TIME => 'Tepat Waktu',
                Attendance::STATUS_LATE => 'Terlambat',
                Attendance::STATUS_ABSENT => 'Alpa',
                default => ucfirst((string) $this->status),
            },
            'work_mode' => $this->work_mode,
            'work_mode_label' => strtoupper((string) $this->work_mode),
            'is_remote' => $this->isRemote(),
            'is_checked_out' => $this->isCheckedOut(),
            'late_minutes' => (int) $this->late_minutes,
            'early_leave_minutes' => (int) $this->early_leave_minutes,
            'work_duration_minutes' => $this->workedMinutes(),
            'is_weekend' => (bool) $this->is_weekend,
            'is_holiday' => (bool) $this->is_holiday,
            'holiday_work' => (bool) $this->holiday_work,
            'is_mock_location' => (bool) $this->is_mock_location,
            'shift_id' => $this->shift_id,
            'company_id' => $this->company_id,
            'latlon_in' => $this->latlon_in,
            'latlon_out' => $this->latlon_out,
            'check_in' => [
                'latlon' => $this->latlon_in,
                'coordinates' => $this->coordinates($this->latlon_in),
                'address' => $this->address_in,
                'photo_url' => $this->photoInUrl(),
                'notes' => $this->notes_in,
                'distance_meters' => $this->distance_in_meters,
            ],
            'check_out' => [
                'latlon' => $this->latlon_out,
                'coordinates' => $this->coordinates($this->latlon_out),
                'address' => $this->address_out,
                'photo_url' => $this->photoOutUrl(),
                'notes' => $this->notes_out,
                'distance_meters' => $this->distance_out_meters,
            ],
            'shift' => $this->whenLoaded('shift', fn () => $this->shift ? [
                'id' => $this->shift->id,
                'name' => $this->shift->name,
                'start_time' => $this->formatTime($this->shift->getRawOriginal('start_time') ?? $this->shift->start_time),
                'end_time' => $this->formatTime($this->shift->getRawOriginal('end_time') ?? $this->shift->end_time),
                'is_cross_day' => (bool) $this->shift->is_cross_day,
            ] : null),
            'company' => $this->whenLoaded('company', fn () => $this->company ? [
                'id' => $this->company->id,
                'name' => $this->company->name,
                'address' => $this->company->address,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function formatTime(mixed $time): ?string
    {
        if (blank($time)) {
            return null;
        }

        return substr((string) $time, 0, 5);
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    private function coordinates(?string $latlon): ?array
    {
        if (blank($latlon) || ! str_contains($latlon, ',')) {
            return null;
        }

        [$latitude, $longitude] = array_pad(explode(',', $latlon, 2), 2, null);

        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return null;
        }

        return [
            'latitude' => (float) $latitude,
            'longitude' => (float) $longitude,
        ];
    }
}
