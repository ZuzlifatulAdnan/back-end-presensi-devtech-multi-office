<?php

namespace Database\Seeders;

use App\Models\ShiftKerja;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
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
                'is_cross_day' => false,
                'grace_period_minutes' => 10,
                'is_active' => true,
                'description' => 'Shift pagi regular dari jam 8 pagi sampai jam 5 sore',
            ],
            [
                'name' => 'Shift Siang',
                'start_time' => '14:00:00',
                'end_time' => '23:00:00',
                'is_cross_day' => false,
                'grace_period_minutes' => 10,
                'is_active' => true,
                'description' => 'Shift siang dari jam 2 siang sampai jam 11 malam',
            ],
            [
                'name' => 'Shift Malam',
                'start_time' => '23:00:00',
                'end_time' => '07:00:00',
                'is_cross_day' => true,
                'grace_period_minutes' => 10,
                'is_active' => true,
                'description' => 'Shift malam dari jam 11 malam sampai jam 7 pagi (melewati tengah malam)',
            ],
            [
                'name' => 'Shift Flexible',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'is_cross_day' => false,
                'grace_period_minutes' => 10,
                'is_active' => true,
                'description' => 'Shift flexible dari jam 9 pagi sampai jam 6 sore',
            ],
        ];

        foreach ($shifts as $shift) {
            ShiftKerja::create($shift);
        }

        $this->command->info('4 shifts created successfully:');
        $this->command->info('- Shift Pagi (08:00-17:00)');
        $this->command->info('- Shift Siang (14:00-23:00)');
        $this->command->info('- Shift Malam (23:00-07:00)');
        $this->command->info('- Shift Flexible (09:00-18:00)');
    }
}
