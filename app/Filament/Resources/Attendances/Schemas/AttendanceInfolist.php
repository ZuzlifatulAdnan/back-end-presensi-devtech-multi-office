<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Models\Attendance;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttendanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user.name')->label('Pegawai')->weight('bold'),
                        TextEntry::make('date')->label('Tanggal')->date('d M Y'),
                        TextEntry::make('shift.name')->label('Shift')->placeholder('Tanpa shift'),
                        TextEntry::make('work_mode')
                            ->label('Mode Kerja')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => strtoupper((string) $state))
                            ->color(fn (?string $state): string => match ($state) {
                                Attendance::MODE_WFO => 'success',
                                Attendance::MODE_WFH => 'warning',
                                Attendance::MODE_WFA => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                Attendance::STATUS_ON_TIME => 'Tepat Waktu',
                                Attendance::STATUS_LATE => 'Terlambat',
                                Attendance::STATUS_ABSENT => 'Alpa',
                                default => ucfirst((string) $state),
                            })
                            ->color(fn (?string $state): string => match ($state) {
                                Attendance::STATUS_ON_TIME => 'success',
                                Attendance::STATUS_LATE => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('company.name')->label('Kantor')->placeholder('-'),
                        TextEntry::make('late_minutes')
                            ->label('Keterlambatan')
                            ->suffix(' menit'),
                        TextEntry::make('early_leave_minutes')
                            ->label('Pulang Cepat')
                            ->suffix(' menit'),
                        TextEntry::make('work_duration_minutes')
                            ->label('Durasi Kerja')
                            ->state(fn (Attendance $record): string => self::duration($record->workedMinutes())),
                    ]),

                Section::make('Absen Masuk')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('time_in')
                            ->label('Jam Masuk')
                            ->formatStateUsing(fn (?string $state): string => $state ? substr($state, 0, 5) : '-'),
                        TextEntry::make('latlon_in')->label('Koordinat')->copyable()->placeholder('-'),
                        TextEntry::make('distance_in_meters')
                            ->label('Jarak dari kantor')
                            ->placeholder('-')
                            ->formatStateUsing(fn (?int $state): string => $state === null ? '-' : number_format($state).' m'),
                        TextEntry::make('address_in')->label('Alamat')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('notes_in')
                            ->label('Catatan Aktivitas')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        ImageEntry::make('photo_in')
                            ->label('Foto Bukti')
                            ->disk('public')
                            ->height(220)
                            ->visible(fn (Attendance $record): bool => filled($record->photo_in))
                            ->columnSpanFull(),
                    ]),

                Section::make('Absen Pulang')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('time_out')
                            ->label('Jam Pulang')
                            ->formatStateUsing(fn (?string $state): string => $state ? substr($state, 0, 5) : '-'),
                        TextEntry::make('latlon_out')->label('Koordinat')->copyable()->placeholder('-'),
                        TextEntry::make('distance_out_meters')
                            ->label('Jarak dari kantor')
                            ->placeholder('-')
                            ->formatStateUsing(fn (?int $state): string => $state === null ? '-' : number_format($state).' m'),
                        TextEntry::make('address_out')->label('Alamat')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('notes_out')->label('Catatan')->placeholder('-')->columnSpanFull(),
                        ImageEntry::make('photo_out')
                            ->label('Foto Bukti')
                            ->disk('public')
                            ->height(220)
                            ->visible(fn (Attendance $record): bool => filled($record->photo_out))
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Attendance $record): bool => $record->isCheckedOut()),

                Section::make('Perangkat')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('is_mock_location')
                            ->label('Lokasi Palsu')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Terdeteksi' : 'Tidak')
                            ->color(fn (bool $state): string => $state ? 'danger' : 'success'),
                        TextEntry::make('device_info')->label('Perangkat')->placeholder('-'),
                    ])
                    ->collapsed(),
            ]);
    }

    private static function duration(?int $minutes): string
    {
        if ($minutes === null) {
            return '-';
        }

        return sprintf('%d jam %d menit', intdiv($minutes, 60), $minutes % 60);
    }
}
