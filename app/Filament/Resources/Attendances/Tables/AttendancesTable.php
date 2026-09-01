<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('time_in')
                    ->label('Jam Masuk')
                    ->time('H:i')
                    ->sortable()
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('success'),
                TextColumn::make('time_out')
                    ->label('Jam Keluar')
                    ->time('H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->icon('heroicon-o-arrow-left-on-rectangle')
                    ->color('danger'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'on_time' => 'Tepat Waktu',
                        'late' => 'Terlambat',
                        'absent' => 'Alpa',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'on_time' => 'success',
                        'late' => 'warning',
                        'absent' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('work_mode')
                    ->label('Mode Kerja')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'wfo' => 'success',
                        'wfh' => 'warning',
                        'wfa' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Lokasi / Kantor')
                    ->placeholder('Pusat / Any')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_hours')
                    ->label('Total Jam')
                    ->getStateUsing(function ($record) {
                        if (! $record->time_out) {
                            return '-';
                        }
                        $checkIn = \Carbon\Carbon::parse($record->time_in);
                        $checkOut = \Carbon\Carbon::parse($record->time_out);
                        $duration = $checkIn->diff($checkOut);

                        return sprintf('%d:%02d jam', $duration->h, $duration->i);
                    })
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-clock'),
                TextColumn::make('shift.name')
                    ->label('Shift')
                    ->badge()
                    ->color('primary')
                    ->placeholder('No Shift')
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('photo_in')
                    ->label('Bukti Masuk')
                    ->disk('public')
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes_in')
                    ->label('Catatan WFH')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->notes_in)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('distance_in_meters')
                    ->label('Jarak Masuk')
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '-' : number_format($state).' m')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_mock_location')
                    ->label('Fake GPS')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('latlon_in')
                    ->label('Lokasi Masuk')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('latlon_out')
                    ->label('Lokasi Keluar')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal')
                            ->default(now()->subMonth()),
                        \Filament\Forms\Components\DatePicker::make('date_to')
                            ->label('Sampai Tanggal')
                            ->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['date_from'] ?? null) {
                            $indicators[] = 'From: '.\Carbon\Carbon::parse($data['date_from'])->format('d M Y');
                        }
                        if ($data['date_to'] ?? null) {
                            $indicators[] = 'To: '.\Carbon\Carbon::parse($data['date_to'])->format('d M Y');
                        }

                        return $indicators;
                    }),
                SelectFilter::make('user_id')
                    ->label('Pegawai')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options([
                        'on_time' => 'Tepat Waktu',
                        'late' => 'Terlambat',
                        'absent' => 'Alpa',
                    ])
                    ->multiple(),
                SelectFilter::make('shift_id')
                    ->label('Shift')
                    ->relationship('shift', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('work_mode')
                    ->label('Mode Kerja')
                    ->options([
                        'wfo' => 'WFO',
                        'wfh' => 'WFH',
                        'wfa' => 'WFA',
                    ]),
                SelectFilter::make('company_id')
                    ->label('Kantor / Lokasi')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('is_mock_location')
                    ->label('Terindikasi Fake GPS')
                    ->query(fn (Builder $query): Builder => $query->where('is_mock_location', true))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                Action::make('export_csv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredSortedTableQuery();

                        if (! $query) {
                            return null;
                        }

                        $attendances = (clone $query)
                            ->reorder()
                            ->orderByDesc('date')
                            ->orderByDesc('time_in')
                            ->with(['user', 'shift', 'company'])
                            ->get();

                        $csv = "User,Date,Check In,Check Out,Status,Total Hours,Shift,Mode Kerja,Lokasi\n";
                        foreach ($attendances as $attendance) {
                            $totalHours = '-';
                            if ($attendance->time_out) {
                                $checkIn = \Carbon\Carbon::parse($attendance->time_in);
                                $checkOut = \Carbon\Carbon::parse($attendance->time_out);
                                $duration = $checkIn->diff($checkOut);
                                $totalHours = sprintf('%d:%02d', $duration->h, $duration->i);
                            }

                            $csv .= sprintf(
                                '"%s","%s","%s","%s","%s","%s","%s","%s","%s"'."\n",
                                $attendance->user->name,
                                $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('d M Y') : '-',
                                $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '-',
                                $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '-',
                                ucfirst(str_replace('_', ' ', $attendance->status)),
                                $totalHours,
                                $attendance->shift->name ?? 'No Shift',
                                strtoupper($attendance->work_mode ?? 'WFO'),
                                $attendance->company->name ?? 'Pusat'
                            );
                        }

                        return response()->streamDownload(function () use ($csv) {
                            echo $csv;
                        }, 'attendances-'.now()->format('Y-m-d').'.csv');
                    }),
                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredSortedTableQuery();

                        if (! $query) {
                            return null;
                        }

                        $attendances = (clone $query)
                            ->reorder()
                            ->orderByDesc('date')
                            ->orderByDesc('time_in')
                            ->with(['user', 'shift', 'company'])
                            ->get();

                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('filament.pages.laporan-absensi-pdf', [
                            'attendances' => $attendances,
                            'exported_at' => now()->format('d/m/Y H:i'),
                            'total_records' => $attendances->count(),
                        ])
                            ->setPaper('A4', 'landscape');

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'attendances-'.now()->format('Y-m-d').'.pdf');
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['user', 'shift', 'company']))
            ->defaultSort('date', 'desc');
    }
}
