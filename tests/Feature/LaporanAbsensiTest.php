<?php

namespace Tests\Feature;

use App\Filament\Pages\LaporanAbsensi;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LaporanAbsensiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user and authenticate
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
        ]));
    }

    public function test_can_render_page(): void
    {
        $this->get('/admin/laporan-absensi')
            ->assertSuccessful();
    }

    public function test_can_export_csv_with_data(): void
    {
        // Create test attendance data
        $users = User::factory()->count(3)->create([
            'position' => 'Developer',
            'department' => 'IT',
        ]);

        foreach ($users as $user) {
            Attendance::factory()->count(2)->create([
                'user_id' => $user->id,
            ]);
        }

        // Test export CSV action
        Livewire::test(LaporanAbsensi::class)
            ->callAction('export_csv')
            ->assertSuccessful();
    }

    public function test_can_export_pdf_with_data(): void
    {
        // Create test attendance data
        $users = User::factory()->count(3)->create([
            'position' => 'Developer',
            'department' => 'IT',
        ]);

        foreach ($users as $user) {
            Attendance::factory()->count(2)->create([
                'user_id' => $user->id,
            ]);
        }

        // Test export PDF action
        Livewire::test(LaporanAbsensi::class)
            ->callAction('export_pdf')
            ->assertSuccessful();
    }

    public function test_can_filter_by_date_range(): void
    {
        $user = User::factory()->create();

        // Create attendance for different dates
        $oldAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->subDays(60),
        ]);

        $recentAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->subDays(5),
        ]);

        Livewire::test(LaporanAbsensi::class)
            ->filterTable('date_range', [
                'start_date' => now()->subDays(30)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ])
            ->assertCanSeeTableRecords([$recentAttendance])
            ->assertCanNotSeeTableRecords([$oldAttendance]);
    }

    public function test_can_filter_by_user(): void
    {
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);

        $attendance1 = Attendance::factory()->create(['user_id' => $user1->id]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user2->id]);

        Livewire::test(LaporanAbsensi::class)
            ->filterTable('user_id', $user1->id)
            ->assertCanSeeTableRecords([$attendance1])
            ->assertCanNotSeeTableRecords([$attendance2]);
    }
}
