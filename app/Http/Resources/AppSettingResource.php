<?php

namespace App\Http\Resources;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * White-label configuration consumed by the mobile app on boot.
 *
 * @mixin AppSetting
 */
class AppSettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'app_name' => $this->app_name,
            'app_short_name' => $this->app_short_name ?: $this->app_name,
            'tagline' => $this->tagline,
            'logo_url' => $this->logoUrl(),
            'logo_dark_url' => $this->logoDarkUrl(),
            'favicon_url' => $this->faviconUrl(),
            'login_banner_url' => $this->loginBannerUrl(),
            'theme' => [
                'primary_color' => $this->primary_color,
                'secondary_color' => $this->secondary_color,
            ],
            'company' => [
                'name' => $this->company_name,
                'address' => $this->address,
                'support_email' => $this->support_email,
                'support_phone' => $this->support_phone,
                'website' => $this->website,
            ],
            'version' => [
                'android_latest' => $this->android_latest_version,
                'android_minimum' => $this->android_min_version,
                'ios_latest' => $this->ios_latest_version,
                'ios_minimum' => $this->ios_min_version,
                'force_update' => (bool) $this->force_update,
            ],
            'maintenance' => [
                'enabled' => (bool) $this->maintenance_mode,
                'message' => $this->maintenance_message,
            ],
            'attendance' => [
                'photo_required' => (bool) $this->attendance_photo_required,
                'wfh_enabled' => (bool) $this->wfh_enabled,
                'wfh_photo_required' => (bool) $this->wfh_photo_required,
                'wfh_notes_required' => (bool) $this->wfh_notes_required,
                'block_mock_location' => (bool) $this->block_mock_location,
                'accuracy_tolerance_meters' => (int) $this->location_accuracy_tolerance_meters,
            ],
            'map' => [
                'default_zoom' => (int) $this->map_default_zoom,
                'tile_url' => $this->map_tile_url ?: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                'attribution' => $this->map_attribution ?: 'OpenStreetMap contributors',
            ],
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
