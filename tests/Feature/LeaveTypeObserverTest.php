<?php

namespace Tests\Feature;

use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\ShiftKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveTypeObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function createUsersWithRequiredRelations(int $count = 3): void
    {
        $jabatan = Jabatan::create(['name' => 'Staff', 'description' => 'Staff']);
        $departemen = Departemen::create(['name' => 'IT', 'description' => 'IT']);
        $shift = ShiftKerja::create([
            'name' => 'Regular',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        for ($i = 0; $i < $count; $i++) {
            User::factory()->create([
                'jabatan_id' => $jabatan->id,
                'departemen_id' => $departemen->id,
                'shift_kerja_id' => $shift->id,
            ]);
        }
    }

    public function test_creating_leave_type_automatically_creates_leave_balances_for_all_users(): void
    {
        // Arrange: Create 3 users
        $this->createUsersWithRequiredRelations(3);

        // Act: Create a new leave type
        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'quota_days' => 12,
            'is_paid' => true,
        ]);

        // Assert: Check that leave balances were created for all 3 users
        $leaveBalances = LeaveBalance::where('leave_type_id', $leaveType->id)->get();
        $this->assertCount(3, $leaveBalances);

        foreach ($leaveBalances as $balance) {
            $this->assertEquals($leaveType->id, $balance->leave_type_id);
            $this->assertEquals(12, $balance->quota_days);
            $this->assertEquals(0, $balance->used_days);
            $this->assertEquals(12, $balance->remaining_days);
            $this->assertEquals(0, $balance->carry_over_days);
            $this->assertEquals(now()->year, $balance->year);
        }
    }

    public function test_updating_leave_type_quota_updates_all_leave_balances(): void
    {
        // Arrange: Create users and leave type
        $this->createUsersWithRequiredRelations(2);

        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'quota_days' => 12,
            'is_paid' => true,
        ]);

        // Simulate some leaves have been used
        $balance = LeaveBalance::where('leave_type_id', $leaveType->id)->first();
        $balance->update([
            'used_days' => 3,
            'remaining_days' => 9,
        ]);

        // Act: Update leave type quota from 12 to 15 (increase by 3)
        $leaveType->update(['quota_days' => 15]);

        // Assert: All balances should have updated quota and remaining days
        $balances = LeaveBalance::where('leave_type_id', $leaveType->id)->get();

        foreach ($balances as $updatedBalance) {
            $this->assertEquals(15, $updatedBalance->quota_days);

            // For the balance that had used_days=3, remaining should be 9+3=12
            // For unused balances, remaining should be 12+3=15
            if ($updatedBalance->id === $balance->id) {
                $this->assertEquals(3, $updatedBalance->used_days);
                $this->assertEquals(12, $updatedBalance->remaining_days); // 9 + 3
            } else {
                $this->assertEquals(0, $updatedBalance->used_days);
                $this->assertEquals(15, $updatedBalance->remaining_days); // 12 + 3
            }
        }
    }

    public function test_decreasing_leave_type_quota_updates_all_leave_balances(): void
    {
        // Arrange: Create users and leave type
        $this->createUsersWithRequiredRelations(2);

        $leaveType = LeaveType::create([
            'name' => 'Sick Leave',
            'quota_days' => 10,
            'is_paid' => true,
        ]);

        // Act: Decrease quota from 10 to 7 (decrease by 3)
        $leaveType->update(['quota_days' => 7]);

        // Assert: All balances should have updated quota and remaining days
        $balances = LeaveBalance::where('leave_type_id', $leaveType->id)->get();

        foreach ($balances as $balance) {
            $this->assertEquals(7, $balance->quota_days);
            $this->assertEquals(7, $balance->remaining_days); // 10 - 3
        }
    }

    public function test_updating_leave_type_name_does_not_affect_leave_balances(): void
    {
        // Arrange: Create users and leave type
        $this->createUsersWithRequiredRelations(2);

        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'quota_days' => 12,
            'is_paid' => true,
        ]);

        $originalBalances = LeaveBalance::where('leave_type_id', $leaveType->id)->get();

        // Act: Update only the name
        $leaveType->update(['name' => 'Yearly Leave']);

        // Assert: Balances should remain unchanged
        $updatedBalances = LeaveBalance::where('leave_type_id', $leaveType->id)->get();

        $this->assertCount($originalBalances->count(), $updatedBalances);

        foreach ($updatedBalances as $balance) {
            $this->assertEquals(12, $balance->quota_days);
            $this->assertEquals(12, $balance->remaining_days);
        }
    }

    public function test_deleting_leave_type_deletes_all_related_leave_balances(): void
    {
        // Arrange: Create users and leave type
        $this->createUsersWithRequiredRelations(3);

        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'quota_days' => 12,
            'is_paid' => true,
        ]);

        // Verify balances exist
        $this->assertCount(3, LeaveBalance::where('leave_type_id', $leaveType->id)->get());

        // Act: Delete the leave type
        $leaveType->delete();

        // Assert: All related leave balances should be deleted
        $this->assertCount(0, LeaveBalance::where('leave_type_id', $leaveType->id)->get());
    }

    public function test_leave_type_created_for_existing_multiple_users(): void
    {
        // Arrange: Create 5 users
        $this->createUsersWithRequiredRelations(5);

        // Act: Create leave type
        $leaveType = LeaveType::create([
            'name' => 'Emergency Leave',
            'quota_days' => 5,
            'is_paid' => false,
        ]);

        // Assert: Should have 5 leave balances
        $balances = LeaveBalance::where('leave_type_id', $leaveType->id)->get();
        $this->assertCount(5, $balances);

        // All users should have the same quota
        $users = User::all();
        foreach ($users as $user) {
            $userBalance = $balances->firstWhere('employee_id', $user->id);
            $this->assertNotNull($userBalance);
            $this->assertEquals(5, $userBalance->quota_days);
            $this->assertEquals(5, $userBalance->remaining_days);
        }
    }
}
