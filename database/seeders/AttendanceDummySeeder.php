<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ShiftKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users with shifts
        $users = User::whereNotNull('shift_kerja_id')->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users with assigned shifts found. Please assign shifts to users first.');

            return;
        }

        $statuses = ['on_time', 'late', 'on_time', 'on_time', 'late']; // 60% on time, 40% late

        // Generate 20 attendance records for the past 2 weeks
        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $shift = ShiftKerja::find($user->shift_kerja_id);

            // Random date in the past 14 days
            $date = Carbon::today()->subDays(rand(0, 14));

            // Check if attendance already exists
            if (Attendance::where('user_id', $user->id)->where('date', $date)->exists()) {
                continue;
            }

            $status = $statuses[array_rand($statuses)];

            // Calculate check in time based on status
            $shiftStart = Carbon::parse($shift->start_time);
            if ($status === 'late') {
                // Late: 10-60 minutes after shift start
                $checkInTime = $shiftStart->copy()->addMinutes(rand(10, 60));
            } else {
                // On time: 0-10 minutes before/after shift start
                $checkInTime = $shiftStart->copy()->addMinutes(rand(-10, 10));
            }

            // Calculate check out time (8-9 hours after check in)
            $workHours = rand(8, 9);
            $checkOutTime = $checkInTime->copy()->addHours($workHours)->addMinutes(rand(0, 59));

            // 90% have check out, 10% still in progress
            $hasCheckOut = rand(1, 100) <= 90;

            Attendance::create([
                'user_id' => $user->id,
                'shift_id' => $shift->id,
                'date' => $date->format('Y-m-d'),
                'time_in' => $checkInTime->format('H:i:s'),
                'time_out' => $hasCheckOut ? $checkOutTime->format('H:i:s') : null,
                'latlon_in' => '-6.'.rand(200000, 250000).',106.'.rand(800000, 850000),
                'latlon_out' => $hasCheckOut ? '-6.'.rand(200000, 250000).',106.'.rand(800000, 850000) : null,
                'status' => $status,
            ]);
        }

        $this->command->info('20 dummy attendance records created successfully!');
    }
}
