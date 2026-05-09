<?php

namespace App\Filament\Widgets;

use App\Models\Overtime;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingOvertimeWidget extends BaseWidget
{
    protected static ?string $heading = 'Overtime Menunggu Persetujuan';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Overtime::with(['user:id,name'])
                    ->where('status', 'pending')
                    ->latest('created_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable(),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y'),

                TextColumn::make('start_time')
                    ->label('Jam Mulai')
                    ->time('H:i'),

                TextColumn::make('end_time')
                    ->label('Jam Selesai')
                    ->time('H:i'),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50)
                    ->placeholder('Tidak ada catatan'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                    }),

                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->since(),
            ])
            ->paginated(false);
    }
}
