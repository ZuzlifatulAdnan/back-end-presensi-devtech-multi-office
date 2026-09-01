<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    /**
     * Seed the single row that drives the mobile app's branding and rules.
     */
    public function run(): void
    {
        $setting = AppSetting::query()->orderBy('id')->first() ?? new AppSetting;

        $setting->fill([
            'app_name' => $setting->app_name ?: config('app.name', 'Absensi'),
            'app_short_name' => $setting->app_short_name ?: 'Absensi',
            'tagline' => $setting->tagline ?: 'Presensi cepat, akurat, dan transparan',
            'primary_color' => $setting->primary_color ?: '#2563eb',
            'wfh_enabled' => true,
            'wfh_photo_required' => true,
            'wfh_notes_required' => true,
            'block_mock_location' => true,
            'location_accuracy_tolerance_meters' => 50,
            'map_default_zoom' => 17,
            'map_tile_url' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            'map_attribution' => 'OpenStreetMap contributors',
        ])->save();

        AppSetting::flushCache();
    }
}
