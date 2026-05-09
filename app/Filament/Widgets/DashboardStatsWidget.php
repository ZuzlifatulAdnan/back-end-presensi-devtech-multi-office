<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\ShiftKerja;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Pegawai', User::count())
                ->description('Jumlah seluruh pegawai')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Total Jabatan', Jabatan::count())
                ->description('Jumlah jabatan tersedia')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('success'),

            Stat::make('Total Departemen', Departemen::count())
                ->description('Jumlah departemen tersedia')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make('Total Shift Kerja', ShiftKerja::count())
                ->description('Jumlah shift kerja')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Overtime Approved', $this->getApprovedOvertimeThisMonth())
                ->description('Bulan ini yang disetujui')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('success'),

            Stat::make('Leave Approved', $this->getApprovedLeaveThisMonth())
                ->description('Bulan ini yang disetujui')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Complete Attendance', $this->getCompleteAttendanceThisMonth())
                ->description('Check-in & check-out bulan ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
        ];
    }

    private function getApprovedOvertimeThisMonth(): int
    {
        return Overtime::where('status', 'approved')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();
    }

    private function getApprovedLeaveThisMonth(): int
    {
        return Leave::where('status', 'approved')
            ->whereNotNull('approved_at')
            ->whereMonth('approved_at', now()->month)
            ->whereYear('approved_at', now()->year)
            ->count();
    }

    private function getCompleteAttendanceThisMonth(): int
    {
        return Attendance::whereNotNull('time_in')
            ->whereNotNull('time_out')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();
    }
}
