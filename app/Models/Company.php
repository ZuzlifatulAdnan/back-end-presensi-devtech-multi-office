<?php

namespace App\Models;

use App\Services\GeofenceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'address',
        'latitude',
        'longitude',
        'radius_km',
        'attendance_type',
        'is_active',
        'logo_path',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => GeofenceService::flushCache());
        static::deleted(fn () => GeofenceService::flushCache());
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function hasCoordinates(): bool
    {
        return is_numeric($this->latitude) && is_numeric($this->longitude);
    }

    /**
     * Geofence radius in meters. Falls back to 100m when misconfigured so a
     * broken row can never turn into an unlimited check-in area.
     */
    public function radiusInMeters(): float
    {
        $radiusKm = (float) $this->radius_km;

        if ($radiusKm <= 0) {
            return 100.0;
        }

        return $radiusKm * 1000;
    }

    public function logoUrl(): ?string
    {
        if (blank($this->logo_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }
}
