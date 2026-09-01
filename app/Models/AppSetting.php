<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Singleton row holding the white-label configuration consumed by the mobile app.
 */
class AppSetting extends Model
{
    public const CACHE_KEY = 'app_settings.singleton';

    public const CACHE_TTL = 3600;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'force_update' => 'boolean',
            'maintenance_mode' => 'boolean',
            'attendance_photo_required' => 'boolean',
            'wfh_enabled' => 'boolean',
            'wfh_photo_required' => 'boolean',
            'wfh_notes_required' => 'boolean',
            'block_mock_location' => 'boolean',
            'location_accuracy_tolerance_meters' => 'integer',
            'map_default_zoom' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    /**
     * Resolve (and lazily create) the single settings row, cached between requests.
     */
    public static function current(): self
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn () => static::query()->orderBy('id')->first() ?? static::createDefault()
        );
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Create the row and read it back so the column defaults declared in the
     * migration are present on the instance handed to callers.
     */
    protected static function createDefault(): self
    {
        $setting = static::query()->create([
            'app_name' => config('app.name', 'Absensi'),
            'primary_color' => '#2563eb',
        ]);

        return $setting->refresh();
    }

    public function logoUrl(): ?string
    {
        return $this->publicUrl($this->logo_path);
    }

    public function logoDarkUrl(): ?string
    {
        return $this->publicUrl($this->logo_dark_path) ?? $this->logoUrl();
    }

    public function faviconUrl(): ?string
    {
        return $this->publicUrl($this->favicon_path);
    }

    public function loginBannerUrl(): ?string
    {
        return $this->publicUrl($this->login_banner_path);
    }

    protected function publicUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
