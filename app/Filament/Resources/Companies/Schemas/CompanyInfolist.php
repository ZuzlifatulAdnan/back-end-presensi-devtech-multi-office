<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Models\ShiftKerja;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Company Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Company Name')
                            ->weight('bold')
                            ->size('lg'),

                        TextEntry::make('email')
                            ->label('Email Address')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),

                        TextEntry::make('address')
                            ->label('Address')
                            ->icon('heroicon-o-map-pin')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Location Settings')
                    ->schema([
                        TextEntry::make('latitude')
                            ->label('Latitude')
                            ->icon('heroicon-o-map-pin')
                            ->copyable(),

                        TextEntry::make('longitude')
                            ->label('Longitude')
                            ->icon('heroicon-o-map-pin')
                            ->copyable(),

                        TextEntry::make('radius_km')
                            ->label('Check-in Radius')
                            ->suffix(' km')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('attendance_type')
                            ->label('Attendance Method')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'location_based_only' => 'Location Based (GPS)',
                                'face_recognition_only' => 'Face Recognition',
                                'hybrid' => 'Hybrid (GPS + Face)',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'location_based_only' => 'primary',
                                'face_recognition_only' => 'success',
                                'hybrid' => 'warning',
                                default => 'gray',
                            }),
                    ])
                    ->columns(4),

                Section::make('Available Work Shifts')
                    ->description('Configured shifts for this company')
                    ->schema([
                        TextEntry::make('shifts')
                            ->label('')
                            ->state(function () {
                                return ShiftKerja::where('is_active', true)
                                    ->orderBy('start_time')
                                    ->get()
                                    ->map(function ($shift) {
                                        $crossDay = $shift->is_cross_day ? ' 🌙' : '';
                                        $grace = $shift->grace_period_minutes.' min grace';
                                        $employees = $shift->users()->count();

                                        return sprintf(
                                            '%s: %s - %s%s (%s, %d employees)',
                                            $shift->name,
                                            $shift->start_time->format('H:i'),
                                            $shift->end_time->format('H:i'),
                                            $crossDay,
                                            $grace,
                                            $employees
                                        );
                                    })
                                    ->toArray();
                            })
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('No shifts configured'),
                    ])
                    ->collapsible(),
            ]);
    }
}
