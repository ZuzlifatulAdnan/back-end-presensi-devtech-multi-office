<?php

namespace App\Observers;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;

class LeaveTypeObserver
{
    /**
     * Handle the LeaveType "created" event.
     * Create leave balance for all existing users when new leave type is created.
     */
    public function created(LeaveType $leaveType): void
    {
        $currentYear = now()->year;

        // Get all users
        $users = User::all();

        // Create leave balance for each user
        foreach ($users as $user) {
            LeaveBalance::create([
                'employee_id' => $user->id,
                'leave_type_id' => $leaveType->id,
                'year' => $currentYear,
                'quota_days' => $leaveType->quota_days,
                'used_days' => 0,
                'remaining_days' => $leaveType->quota_days,
                'carry_over_days' => 0,
                'last_updated' => now(),
            ]);
        }
    }

    /**
     * Handle the LeaveType "updated" event.
     * Update quota_days and remaining_days for all leave balances when leave type quota is changed.
     */
    public function updated(LeaveType $leaveType): void
    {
        // Only update if quota_days has changed
        if ($leaveType->wasChanged('quota_days')) {
            $oldQuota = $leaveType->getOriginal('quota_days');
            $newQuota = $leaveType->quota_days;
            $quotaDifference = $newQuota - $oldQuota;

            // Update all leave balances for this leave type
            $leaveBalances = LeaveBalance::where('leave_type_id', $leaveType->id)->get();

            foreach ($leaveBalances as $balance) {
                $balance->update([
                    'quota_days' => $newQuota,
                    'remaining_days' => $balance->remaining_days + $quotaDifference,
                    'last_updated' => now(),
                ]);
            }
        }
    }

    /**
     * Handle the LeaveType "deleting" event.
     * Delete all related leave balances when leave type is deleted.
     */
    public function deleting(LeaveType $leaveType): void
    {
        // Delete all leave balances for this leave type
        LeaveBalance::where('leave_type_id', $leaveType->id)->delete();

        // Also delete all leave requests for this leave type
        // to maintain referential integrity
        $leaveType->leaves()->delete();
    }

    /**
     * Handle the LeaveType "restored" event.
     */
    public function restored(LeaveType $leaveType): void
    {
        //
    }

    /**
     * Handle the LeaveType "force deleted" event.
     */
    public function forceDeleted(LeaveType $leaveType): void
    {
        //
    }
}
