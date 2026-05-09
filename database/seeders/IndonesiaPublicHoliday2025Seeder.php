<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class IndonesiaPublicHoliday2025Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            // Januari
            ['date' => '2025-01-01', 'name' => 'Tahun Baru Masehi', 'is_official' => true],

            // Februari
            ['date' => '2025-02-12', 'name' => 'Tahun Baru Imlek 2576 Kongzili', 'is_official' => true],

            // Maret
            ['date' => '2025-03-29', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW', 'is_official' => true],
            ['date' => '2025-03-31', 'name' => 'Hari Suci Nyepi Tahun Baru Saka 1947', 'is_official' => true],

            // April
            ['date' => '2025-04-18', 'name' => 'Wafat Isa Al Masih', 'is_official' => true],

            // Mei
            ['date' => '2025-05-01', 'name' => 'Hari Buruh Internasional', 'is_official' => true],
            ['date' => '2025-05-12', 'name' => 'Kenaikan Isa Al Masih', 'is_official' => true],

            // Juni
            ['date' => '2025-06-01', 'name' => 'Hari Lahir Pancasila', 'is_official' => true],

            // Agustus
            ['date' => '2025-08-17', 'name' => 'Hari Kemerdekaan Republik Indonesia', 'is_official' => true],

            // September - Oktober (Cuti Bersama biasanya)
            ['date' => '2025-09-27', 'name' => 'Isra Miraj Nabi Muhammad SAW', 'is_official' => true],

            // Desember
            ['date' => '2025-12-25', 'name' => 'Hari Raya Natal', 'is_official' => true],

            // Hari Raya Idul Fitri (perkiraan, bisa berubah sesuai kalender Hijriyah)
            ['date' => '2025-03-30', 'name' => 'Hari Raya Idul Fitri 1446 H', 'is_official' => true],
            ['date' => '2025-03-31', 'name' => 'Hari Raya Idul Fitri 1446 H', 'is_official' => true],

            // Hari Raya Idul Adha (perkiraan)
            ['date' => '2025-06-07', 'name' => 'Hari Raya Idul Adha 1446 H', 'is_official' => true],

            // Tahun Baru Islam
            ['date' => '2025-06-27', 'name' => 'Tahun Baru Islam 1447 H', 'is_official' => true],

            // Maulid Nabi Muhammad SAW
            ['date' => '2025-09-05', 'name' => 'Maulid Nabi Muhammad SAW', 'is_official' => true],

            // Waisak
            ['date' => '2025-05-12', 'name' => 'Hari Raya Waisak 2569', 'is_official' => true],

            // Cuti Bersama (common collective leave days - adjust as needed)
            ['date' => '2025-03-28', 'name' => 'Cuti Bersama Idul Fitri', 'is_official' => false],
            ['date' => '2025-04-01', 'name' => 'Cuti Bersama Idul Fitri', 'is_official' => false],
            ['date' => '2025-04-02', 'name' => 'Cuti Bersama Idul Fitri', 'is_official' => false],
            ['date' => '2025-04-03', 'name' => 'Cuti Bersama Idul Fitri', 'is_official' => false],
            ['date' => '2025-06-06', 'name' => 'Cuti Bersama Idul Adha', 'is_official' => false],
            ['date' => '2025-12-26', 'name' => 'Cuti Bersama Natal', 'is_official' => false],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date']],
                [
                    'name' => $holiday['name'],
                    'type' => Holiday::TYPE_NATIONAL,
                    'is_official' => $holiday['is_official'],
                ]
            );
        }

        $this->command->info('Indonesia Public Holidays 2025 seeded successfully.');
        $this->command->info('Total holidays: '.count($holidays));
    }
}
