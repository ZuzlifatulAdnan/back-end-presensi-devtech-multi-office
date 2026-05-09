<?php

namespace Database\Seeders;

use App\Models\ShiftKerja;
use Illuminate\Database\Seeder;

class ShiftKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Shift Pagi',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'description' => 'Shift pagi regular dari jam 8 pagi sampai jam 5 sore',
                'is_cross_day' => false,
                'grace_period_minutes' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Shift Siang',
                'start_time' => '14:00:00',
                'end_time' => '23:00:00',
                'description' => 'Shift siang dari jam 2 siang sampai jam 11 malam',
                'is_cross_day' => false,
                'grace_period_minutes' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Shift Malam',
                'start_time' => '23:00:00',
                'end_time' => '07:00:00',
                'description' => 'Shift malam melewati tengah malam',
                'is_cross_day' => true,
                'grace_period_minutes' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Shift Flexible',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'description' => 'Shift flexible dari jam 9 pagi sampai jam 6 sore',
                'is_cross_day' => false,
                'grace_period_minutes' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            ShiftKerja::updateOrCreate(
                ['name' => $shift['name']],
                $shift
            );
        }
    }
}
