<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartemenSeeder::class,
            JabatanSeeder::class,
            ShiftKerjaSeeder::class,
            UserSeeder::class,
            CompanySeeder::class,

            // Pivot table seeders - must run after the main tables are seeded
            DepartemenUserSeeder::class,
            JabatanUserSeeder::class,
            ShiftKerjaUserSeeder::class,

            // Leave management seeders
            LeaveTypeSeeder::class,
            LeaveTestingSeeder::class,

            // Other data seeders
            NoteSeeder::class,
            // QrAbsenSeeder::class,
            // PermissionSeeder::class,
            OvertimeSeeder::class,
            IndonesiaPublicHoliday2025Seeder::class,
            AttendanceSeeder::class,
        ]);
    }
}
