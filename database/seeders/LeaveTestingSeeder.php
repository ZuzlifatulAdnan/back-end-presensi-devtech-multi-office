<?php

namespace Database\Seeders;

use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaveTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $leaveTypes = LeaveType::all();
        $currentYear = now()->year;

        if ($users->isEmpty() || $leaveTypes->isEmpty()) {
            $this->command->warn('No users or leave types found. Please run UserSeeder and LeaveTypeSeeder first.');

            return;
        }

        // Create leave balances for all users
        foreach ($users as $user) {
            foreach ($leaveTypes as $leaveType) {
                LeaveBalance::updateOrCreate(
                    [
                        'employee_id' => $user->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $currentYear,
                    ],
                    [
                        'quota_days' => $leaveType->quota_days,
                        'used_days' => 0,
                        'remaining_days' => $leaveType->quota_days,
                        'carry_over_days' => 0,
                        'last_updated' => now(),
                    ]
                );
            }
        }

        // Create some sample leave requests
        $sampleUsers = $users->take(5);
        foreach ($sampleUsers as $user) {
            $leaveType = $leaveTypes->random();

            // Pending leave
            Leave::create([
                'employee_id' => $user->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => now()->addDays(rand(1, 30)),
                'end_date' => now()->addDays(rand(31, 40)),
                'total_days' => rand(1, 5),
                'reason' => 'Sample leave request for testing',
                'status' => 'pending',
            ]);

            // Approved leave
            Leave::create([
                'employee_id' => $user->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => now()->subDays(rand(10, 20)),
                'end_date' => now()->subDays(rand(5, 9)),
                'total_days' => rand(1, 3),
                'reason' => 'Sample approved leave',
                'status' => 'approved',
                'approved_by' => $users->first()->id,
                'approved_at' => now()->subDays(rand(11, 21)),
            ]);
        }

        $this->command->info('Leave testing data created successfully.');
    }
}
