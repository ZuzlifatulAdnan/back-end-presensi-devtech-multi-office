<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OvertimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstUser = \App\Models\User::first();

        if (! $firstUser) {
            return;
        }

        $overtimes = [
            [
                'user_id' => $firstUser->id,
                'date' => '2024-01-15',
                'start_time' => '18:00:00',
                'end_time' => '21:00:00',
                'reason' => 'Menyelesaikan proyek urgent untuk klien',
                'status' => 'approved',
                'approved_by' => $firstUser->id,
                'approved_at' => '2024-01-16 08:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $firstUser->id,
                'date' => '2024-01-16',
                'start_time' => '19:00:00',
                'end_time' => '22:30:00',
                'reason' => 'Maintenance server dan update sistem',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $firstUser->id,
                'date' => '2024-01-17',
                'start_time' => '17:30:00',
                'end_time' => '20:00:00',
                'reason' => 'Training karyawan baru dan dokumentasi',
                'status' => 'approved',
                'approved_by' => $firstUser->id,
                'approved_at' => '2024-01-18 09:15:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $firstUser->id,
                'date' => '2024-01-18',
                'start_time' => '18:15:00',
                'end_time' => '21:45:00',
                'reason' => 'Presentasi proposal kepada manajemen',
                'status' => 'rejected',
                'approved_by' => $firstUser->id,
                'approved_at' => '2024-01-19 10:30:00',
                'notes' => 'Tidak memenuhi kriteria overtime yang telah ditetapkan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $firstUser->id,
                'date' => '2024-01-19',
                'start_time' => '20:00:00',
                'end_time' => '23:30:00',
                'reason' => 'Review dan approval proses bisnis baru',
                'status' => 'approved',
                'approved_by' => $firstUser->id,
                'approved_at' => '2024-01-20 07:45:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($overtimes as $overtime) {
            \App\Models\Overtime::create($overtime);
        }
    }
}
