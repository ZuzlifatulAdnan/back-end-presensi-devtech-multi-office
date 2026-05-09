<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestAttendanceWidget extends BaseWidget
{
    protected static ?string $heading = 'Absensi Terbaru';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::with(['user:id,name,position'])
                    ->latest('created_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Karyawan')
                    ->searchable(),

                TextColumn::make('user.position')
                    ->label('Jabatan'),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y'),

                TextColumn::make('time_in')
                    ->label('Jam Masuk')
                    ->time('H:i')
                    ->placeholder('-'),

                TextColumn::make('time_out')
                    ->label('Jam Keluar')
                    ->time('H:i')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Status')
                    ->state(function (Attendance $record): string {
                        if (! $record->time_in) {
                            return 'Belum Masuk';
                        }
                        if (! $record->time_out) {
                            return 'Belum Pulang';
                        }

                        return 'Selesai';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Selesai' => 'success',
                        'Belum Pulang' => 'warning',
                        'Belum Masuk' => 'danger',
                    }),
            ])
            ->paginated(false);
    }
}
