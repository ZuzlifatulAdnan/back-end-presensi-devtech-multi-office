<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_id',
        'company_id',
        'date',
        'time_in',
        'time_out',
        'latlon_in',
        'latlon_out',
        'status',
        'work_mode',
        'is_weekend',
        'is_holiday',
        'holiday_work',
        'late_minutes',
        'early_leave_minutes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time_in' => 'datetime:H:i:s',
            'time_out' => 'datetime:H:i:s',
            'is_weekend' => 'boolean',
            'is_holiday' => 'boolean',
            'holiday_work' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(ShiftKerja::class, 'shift_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
