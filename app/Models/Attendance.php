<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Attendance extends Model
{
    use HasFactory;

    public const MODE_WFO = 'wfo';

    public const MODE_WFH = 'wfh';

    public const MODE_WFA = 'wfa';

    public const MODES = [self::MODE_WFO, self::MODE_WFH, self::MODE_WFA];

    public const STATUS_ON_TIME = 'on_time';

    public const STATUS_LATE = 'late';

    public const STATUS_ABSENT = 'absent';

    protected $fillable = [
        'user_id',
        'shift_id',
        'company_id',
        'date',
        'time_in',
        'time_out',
        'latlon_in',
        'latlon_out',
        'photo_in',
        'photo_out',
        'notes_in',
        'notes_out',
        'address_in',
        'address_out',
        'distance_in_meters',
        'distance_out_meters',
        'is_mock_location',
        'status',
        'work_mode',
        'is_weekend',
        'is_holiday',
        'holiday_work',
        'late_minutes',
        'early_leave_minutes',
        'work_duration_minutes',
        'device_info',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'is_weekend' => 'boolean',
            'is_holiday' => 'boolean',
            'holiday_work' => 'boolean',
            'is_mock_location' => 'boolean',
            'late_minutes' => 'integer',
            'early_leave_minutes' => 'integer',
            'work_duration_minutes' => 'integer',
            'distance_in_meters' => 'integer',
            'distance_out_meters' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftKerja::class, 'shift_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOnDate(Builder $query, Carbon|string $date): Builder
    {
        return $query->whereDate('date', $date instanceof Carbon ? $date->toDateString() : $date);
    }

    public function scopeBetweenDates(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopeRemote(Builder $query): Builder
    {
        return $query->whereIn('work_mode', [self::MODE_WFH, self::MODE_WFA]);
    }

    public function isRemote(): bool
    {
        return in_array($this->work_mode, [self::MODE_WFH, self::MODE_WFA], true);
    }

    public function isCheckedOut(): bool
    {
        return filled($this->time_out);
    }

    public function photoInUrl(): ?string
    {
        return $this->fileUrl($this->photo_in);
    }

    public function photoOutUrl(): ?string
    {
        return $this->fileUrl($this->photo_out);
    }

    /**
     * Worked minutes between check-in and check-out, tolerant of cross-day shifts.
     */
    public function workedMinutes(): ?int
    {
        if ($this->work_duration_minutes !== null) {
            return $this->work_duration_minutes;
        }

        if (blank($this->time_in) || blank($this->time_out)) {
            return null;
        }

        $date = $this->date instanceof Carbon ? $this->date->toDateString() : (string) $this->date;
        $in = Carbon::parse($date.' '.$this->time_in);
        $out = Carbon::parse($date.' '.$this->time_out);

        if ($out->lessThan($in)) {
            $out->addDay();
        }

        return (int) $in->diffInMinutes($out);
    }

    private function fileUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
