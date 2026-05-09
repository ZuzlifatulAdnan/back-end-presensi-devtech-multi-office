<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'work_mode',
        'position',
        'department',
        'jabatan_id',
        'departemen_id',
        'shift_kerja_id',
        'company_id',
        'face_embedding',
        'image_url',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class);
    }

    public function notes()
    {
        return $this->hasMany(\App\Models\Note::class);
    }

    // New one-to-one relationships
    public function jabatan()
    {
        return $this->belongsTo(\App\Models\Jabatan::class, 'jabatan_id');
    }

    public function departemen()
    {
        return $this->belongsTo(\App\Models\Departemen::class, 'departemen_id');
    }

    public function shiftKerja()
    {
        return $this->belongsTo(\App\Models\ShiftKerja::class, 'shift_kerja_id');
    }

    // Legacy many-to-many relationships (deprecated, for backward compatibility)
    public function jabatans()
    {
        return $this->belongsToMany(\App\Models\Jabatan::class, 'jabatan_user');
    }

    public function departemens()
    {
        return $this->belongsToMany(\App\Models\Departemen::class, 'departemen_user');
    }

    public function shiftKerjas()
    {
        return $this->belongsToMany(\App\Models\ShiftKerja::class, 'shift_kerja_user');
    }

    public function overtimes()
    {
        return $this->hasMany(\App\Models\Overtime::class);
    }

    public function approvedOvertimes()
    {
        return $this->hasMany(\App\Models\Overtime::class, 'approved_by');
    }

    public function shiftAssignments()
    {
        return $this->hasMany(\App\Models\ShiftAssignment::class);
    }

    public function leaves()
    {
        return $this->hasMany(\App\Models\Leave::class, 'employee_id');
    }

    public function leaveBalances()
    {
        return $this->hasMany(\App\Models\LeaveBalance::class, 'employee_id');
    }

    public function approvedLeaves()
    {
        return $this->hasMany(\App\Models\Leave::class, 'approved_by');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'manager'], true);
    }
}
